<?php

namespace App\Services\WhatsApp;

use App\Models\ChannelConnection;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppOutboundService
{
    public function sendText(Conversation $conversation, string $body, ?int $senderUserId = null): Message
    {
        $connection = ChannelConnection::query()
            ->where('tenant_id', $conversation->tenant_id)
            ->where('workspace_id', $conversation->workspace_id)
            ->where('channel', 'whatsapp')
            ->where('status', 'active')
            ->first();

        if (! $connection) {
            throw new RuntimeException('No active WhatsApp connection is configured for this workspace.');
        }

        $to = $conversation->contact?->phone;
        if (! $to) {
            throw new RuntimeException('The conversation contact does not have a phone number.');
        }

        $version = config('services.whatsapp.graph_version', 'v23.0');
        $response = Http::withToken($connection->access_token)
            ->acceptJson()
            ->post("https://graph.facebook.com/{$version}/{$connection->external_id}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['preview_url' => false, 'body' => $body],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('WhatsApp API error: ' . $response->body());
        }

        $externalId = data_get($response->json(), 'messages.0.id');

        $message = Message::create([
            'tenant_id' => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'external_id' => $externalId,
            'direction' => 'outbound',
            'sender_type' => 'user',
            'sender_user_id' => $senderUserId,
            'type' => 'text',
            'body' => $body,
            'payload' => $response->json(),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);
        return $message;
    }
}
