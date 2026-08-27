<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppWebhookController
{
    public function verify(Request $request): mixed
    {
        if ($request->query('hub_verify_token') !== config('services.whatsapp.verify_token')) {
            return response('Forbidden', 403);
        }

        return response($request->query('hub_challenge'), 200);
    }

    public function receive(Request $request): JsonResponse
    {
        // Webhook signature verification and event normalization belong here.
        // Persisting the normalized event will be handled by the channel/message service.
        logger()->info('WhatsApp webhook received', ['payload' => $request->all()]);

        return response()->json(['received' => true]);
    }
}
