<?php

namespace App\Services\WhatsApp;

use App\Models\ChannelConnection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppMediaService
{
    public function download(ChannelConnection $connection, string $mediaId): array
    {
        $version = config('services.whatsapp.graph_version', 'v23.0');
        $base = "https://graph.facebook.com/{$version}";
        $headers = ['Authorization' => 'Bearer '.$connection->access_token];

        $meta = Http::withHeaders($headers)->get("{$base}/{$mediaId}")->throw()->json();
        if (empty($meta['url'])) throw new RuntimeException('WhatsApp media URL was not returned.');

        $response = Http::withHeaders($headers)->get($meta['url'])->throw();

        return [
            'media_id' => $mediaId,
            'mime_type' => $meta['mime_type'] ?? $response->header('Content-Type'),
            'sha256' => $meta['sha256'] ?? null,
            'data' => $response->body(),
        ];
    }
}
