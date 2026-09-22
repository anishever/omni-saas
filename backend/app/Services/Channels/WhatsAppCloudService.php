<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppCloudService
{
    public function sendText(string $phoneNumberId, string $accessToken, string $to, string $body): array
    {
        return $this->postMessage($phoneNumberId, $accessToken, [
            'messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $to,
            'type' => 'text', 'text' => ['preview_url' => false, 'body' => $body],
        ]);
    }

    public function sendTemplate(string $phoneNumberId, string $accessToken, string $to, string $name, string $language, array $components = []): array
    {
        $template = ['name' => $name, 'language' => ['code' => $language]];
        if ($components !== []) $template['components'] = $components;
        return $this->postMessage($phoneNumberId, $accessToken, [
            'messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $to,
            'type' => 'template', 'template' => $template,
        ]);
    }

    private function postMessage(string $phoneNumberId, string $accessToken, array $payload): array
    {
        $version = config('services.whatsapp.graph_version', 'v23.0');
        $response = Http::withToken($accessToken)->acceptJson()
            ->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", $payload);
        if ($response->failed()) throw new RuntimeException('WhatsApp API request failed: '.$response->body());
        return $response->json() ?? [];
    }
}
