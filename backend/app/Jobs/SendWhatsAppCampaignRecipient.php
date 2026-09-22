<?php

namespace App\Jobs;

use App\Models\CampaignRecipient;
use App\Services\Channels\WhatsAppCloudService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendWhatsAppCampaignRecipient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public int $recipientId) {}

    public function handle(WhatsAppCloudService $whatsapp): void
    {
        $recipient = CampaignRecipient::with(['campaign.connection', 'campaign.template', 'contact'])->find($this->recipientId);
        if (! $recipient || $recipient->status !== 'queued') return;

        $campaign = $recipient->campaign;
        $connection = $campaign->connection;
        $template = $campaign->template;
        $contact = $recipient->contact;

        if (! $connection || $connection->status !== 'active' || ! $template || strtolower($template->status) !== 'approved' || ! $contact || ! $contact->phone) {
            $recipient->update(['status' => 'failed', 'error' => 'Missing active connection, approved template, or recipient phone.']);
            $this->refreshCounts($campaign->id);
            return;
        }

        try {
            $response = $whatsapp->sendTemplate(
                $connection->external_id,
                $connection->access_token,
                $contact->phone,
                $template->name,
                $template->language,
                $campaign->settings['template_components'] ?? []
            );
            $wamid = data_get($response, 'messages.0.id');
            if (! $wamid) throw new \RuntimeException('WhatsApp response did not include a message ID.');

            DB::transaction(function () use ($recipient, $campaign, $wamid, $response) {
                $recipient->update(['status' => 'sent', 'external_id' => $wamid, 'sent_at' => now(), 'error' => null]);
                $message = \App\Models\Message::create([
                    'tenant_id' => $campaign->tenant_id,
                    'conversation_id' => \App\Models\Conversation::query()->where('tenant_id', $campaign->tenant_id)->where('contact_id', $recipient->contact_id)->where('channel', 'whatsapp')->where('status', 'open')->value('id'),
                    'external_id' => $wamid,
                    'direction' => 'outbound',
                    'sender_type' => 'system',
                    'type' => 'template',
                    'body' => $campaign->template->name,
                    'payload' => $response,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $recipient->update(['message_id' => $message->id]);
            });
        } catch (Throwable $e) {
            $recipient->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 60000)]);
            throw $e;
        } finally {
            $this->refreshCounts($campaign->id);
        }
    }

    public function failed(Throwable $exception): void
    {
        $recipient = CampaignRecipient::find($this->recipientId);
        if ($recipient && $recipient->status === 'queued') {
            $recipient->update(['status' => 'failed', 'error' => mb_substr($exception->getMessage(), 0, 60000)]);
            $this->refreshCounts($recipient->campaign_id);
        }
    }

    private function refreshCounts(int $campaignId): void
    {
        $campaign = \App\Models\Campaign::find($campaignId);
        if (! $campaign) return;
        $campaign->update([
            'sent_count' => $campaign->recipients()->whereIn('status', ['sent','delivered','read'])->count(),
            'delivered_count' => $campaign->recipients()->whereIn('status', ['delivered','read'])->count(),
            'read_count' => $campaign->recipients()->where('status', 'read')->count(),
            'failed_count' => $campaign->recipients()->where('status', 'failed')->count(),
        ]);
    }
}
