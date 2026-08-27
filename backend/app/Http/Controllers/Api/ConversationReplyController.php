<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Services\WhatsApp\WhatsAppOutboundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationReplyController
{
    public function __invoke(Request $request, Conversation $conversation, WhatsAppOutboundService $whatsapp): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:4096']]);

        if ($conversation->channel !== 'whatsapp') {
            return response()->json(['message' => 'This reply endpoint currently supports WhatsApp conversations only.'], 422);
        }

        $message = $whatsapp->sendText($conversation, $data['body'], $request->user()->id);

        return response()->json(['message' => $message], 201);
    }
}
