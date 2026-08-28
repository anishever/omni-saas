<?php

namespace App\Services\WhatsApp;

use App\Models\ChannelConnection;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class WhatsAppWebhookService
{
    public function handle(array $payload): array
    {
        $result = ['messages_stored' => 0, 'statuses_updated' => 0];

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];
                if (($change['field'] ?? null) !== 'messages') continue;

                $phoneNumberId = data_get($value, 'metadata.phone_number_id');
                $connection = ChannelConnection::withoutGlobalScopes()
                    ->where('channel', 'whatsapp')
                    ->where('external_id', $phoneNumberId)
                    ->where('status', 'active')
                    ->first();

                if (! $connection) continue;

                foreach ($value['statuses'] ?? [] as $status) {
                    if ($this->updateStatus($connection, $status)) {
                        $result['statuses_updated']++;
                    }
                }

                foreach ($value['messages'] ?? [] as $message) {
                    if ($this->storeInboundMessage($connection, $message)) {
                        $result['messages_stored']++;
                    }
                }
            }
        }

        return $result;
    }

    private function updateStatus(ChannelConnection $connection, array $status): bool
    {
        $externalId = $status['id'] ?? null;
        if (! $externalId) return false;

        $message = Message::withoutGlobalScopes()
            ->where('tenant_id', $connection->tenant_id)
            ->where('external_id', $externalId)
            ->first();

        if (! $message) return false;

        $mapped = match ($status['status'] ?? null) {
            'sent' => 'sent',
            'delivered' => 'delivered',
            'read' => 'read',
            'failed' => 'failed',
            default => null,
        };

        if (! $mapped) return false;

        $payload = is_array($message->payload) ? $message->payload : [];
        $payload['status_update'] = $status;

        $message->forceFill([
            'status' => $mapped,
            'payload' => $payload,
            'sent_at' => $message->sent_at ?? (isset($status['timestamp']) ? now()->setTimestamp((int) $status['timestamp']) : now()),
        ])->save();

        if ($mapped === 'failed') {
            $message->conversation?->update(['last_message_at' => now()]);
        }

        return true;
    }

    private function storeInboundMessage(ChannelConnection $connection, array $message): bool
    {
        $externalId = $message['id'] ?? null;
        $from = $message['from'] ?? null;
        if (! $externalId || ! $from) return false;

        if (Message::withoutGlobalScopes()->where('external_id', $externalId)->exists()) return false;

        return DB::transaction(function () use ($connection, $message, $from, $externalId) {
            $contact = Contact::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $connection->tenant_id, 'phone' => $from],
                ['workspace_id' => $connection->workspace_id, 'status' => 'active', 'source' => 'WhatsApp']
            );

            $conversation = Conversation::withoutGlobalScopes()->firstOrCreate(
                [
                    'tenant_id' => $connection->tenant_id,
                    'contact_id' => $contact->id,
                    'channel' => 'whatsapp',
                    'status' => 'open',
                ],
                ['workspace_id' => $connection->workspace_id, 'priority' => 'normal']
            );

            $body = data_get($message, 'text.body')
                ?? data_get($message, 'image.caption')
                ?? data_get($message, 'video.caption')
                ?? null;

            Message::withoutGlobalScopes()->create([
                'tenant_id' => $connection->tenant_id,
                'conversation_id' => $conversation->id,
                'external_id' => $externalId,
                'direction' => 'inbound',
                'sender_type' => 'contact',
                'type' => $message['type'] ?? 'unknown',
                'body' => $body,
                'payload' => $message,
                'status' => 'received',
                'sent_at' => isset($message['timestamp']) ? now()->setTimestamp((int) $message['timestamp']) : now(),
            ]);

            $conversation->update(['last_message_at' => now()]);
            return true;
        });
    }
}
