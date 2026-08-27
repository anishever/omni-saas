<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppCloudService
{
    public function sendText(string $phoneNumberId, string $accessToken, string $to, string $body): array
    {
        $version = config('services.whatsapp.graph_version', 'v23.0');
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => ['preview_url' => false, 'body' => $body],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('WhatsApp API request failed: '.$response->body());
        }

        return $response->json();
    }
}
