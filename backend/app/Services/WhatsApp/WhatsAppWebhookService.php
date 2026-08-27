<?php

namespace App\Services\WhatsApp;

use App\Models\ChannelConnection;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class WhatsAppWebhookService
{
    public function handle(array $payload): int
    {
        $stored = 0;

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

                foreach ($value['messages'] ?? [] as $message) {
                    $externalId = $message['id'] ?? null;
                    if (! $externalId) continue;
                    if (Message::withoutGlobalScopes()->where('external_id', $externalId)->exists()) continue;

                    $from = $message['from'] ?? null;
                    if (! $from) continue;

                    DB::transaction(function () use ($connection, $message, $from, $externalId, &$stored) {
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
                        $stored++;
                    });
                }
            }
        }

        return $stored;
    }
}
