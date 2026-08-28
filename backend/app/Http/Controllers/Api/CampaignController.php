<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ChannelConnection;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['campaigns' => Campaign::query()->with('template')->latest()->paginate(25)]);
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
        $campaign->delete(); return response()->json(['message'=>'Campaign deleted.']);
    }
}
