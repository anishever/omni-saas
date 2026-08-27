<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): mixed
    {
        if ($request->query('hub_verify_token') !== config('services.whatsapp.verify_token')) {
            return response('Forbidden', 403);
        }

        return response($request->query('hub_challenge'), 200);
    }

    public function receive(Request $request, WhatsAppWebhookService $service): JsonResponse
    {
        $stored = $service->handle($request->all());

        return response()->json(['received' => true, 'stored' => $stored]);
    }
}
