<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppCampaignRecipient;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\ChannelConnection;
use App\Models\Contact;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $campaigns = Campaign::query()->where('tenant_id', $request->user()->tenant_id)
            ->with('template')->latest()->paginate(25);

        return response()->json(['campaigns' => $campaigns]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'workspace_id' => ['required','integer'], 'channel_connection_id' => ['required','integer'],
            'whatsapp_template_id' => ['required','integer'], 'name' => ['required','string','max:160'],
            'audience_filter' => ['nullable','array'], 'settings' => ['nullable','array'], 'scheduled_at' => ['nullable','date'],
        ]);
        $connection = ChannelConnection::query()->findOrFail($data['channel_connection_id']);
        $template = WhatsAppTemplate::query()->findOrFail($data['whatsapp_template_id']);
        abort_unless($connection->tenant_id === $request->user()->tenant_id && $connection->channel === 'whatsapp', 403);
        abort_unless($template->tenant_id === $request->user()->tenant_id && $template->channel_connection_id === $connection->id, 403);
        $campaign = Campaign::create([...$data, 'tenant_id'=>$request->user()->tenant_id, 'channel'=>'whatsapp', 'status'=>'draft']);
        return response()->json(['campaign'=>$campaign->load('template')], 201);
    }

    public function launch(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->tenant_id === $request->user()->tenant_id, 403);
        abort_if($campaign->status !== 'draft', 422, 'Only draft campaigns can be launched.');
        $data = $request->validate(['contact_ids' => ['required','array','min:1','max:5000'], 'contact_ids.*' => ['required','integer','distinct']]);
        $connection = ChannelConnection::query()->whereKey($campaign->channel_connection_id)->where('tenant_id', $campaign->tenant_id)->where('status', 'active')->firstOrFail();
        $template = WhatsAppTemplate::query()->whereKey($campaign->whatsapp_template_id)->where('tenant_id', $campaign->tenant_id)->firstOrFail();
        abort_unless(strtolower($template->status) === 'approved', 422, 'The WhatsApp template must be approved before launch.');

        $contacts = Contact::query()->where('tenant_id', $campaign->tenant_id)
            ->where('workspace_id', $campaign->workspace_id)->whereIn('id', $data['contact_ids'])
            ->whereNotNull('phone')->where('whatsapp_opt_in', true)->get(['id']);
        abort_if($contacts->count() !== count($data['contact_ids']), 422,
            'Every selected contact must belong to this workspace, have a phone number, and have recorded WhatsApp opt-in.');

        DB::transaction(function () use ($campaign, $contacts) {
            foreach ($contacts as $contact) CampaignRecipient::firstOrCreate(['campaign_id' => $campaign->id, 'contact_id' => $contact->id], ['status' => 'queued']);
            $campaign->update(['status' => 'running', 'audience_count' => $contacts->count(), 'started_at' => now()]);
        });

        $campaign->recipients()->where('status', 'queued')->pluck('id')->each(fn ($id) => SendWhatsAppCampaignRecipient::dispatch((int) $id));
        return response()->json(['campaign' => $campaign->fresh(), 'queued' => $contacts->count()]);
    }

    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->tenant_id === $request->user()->tenant_id, 403);
        abort_if(in_array($campaign->status, ['running','completed']), 422, 'Campaign can no longer be edited.');
        $data=$request->validate(['name'=>['sometimes','string','max:160'],'audience_filter'=>['nullable','array'],'settings'=>['nullable','array'],'scheduled_at'=>['nullable','date'],'status'=>['sometimes','in:draft,scheduled']]);
        $campaign->update($data);
        return response()->json(['campaign'=>$campaign->fresh('template')]);
    }

    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->tenant_id === $request->user()->tenant_id, 403);
        abort_if($campaign->status === 'running', 422, 'A running campaign cannot be deleted.');
        $campaign->delete(); return response()->json(['message'=>'Campaign deleted.']);
    }
}
