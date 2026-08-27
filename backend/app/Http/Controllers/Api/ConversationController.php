<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Conversation::with(['contact:id,first_name,last_name,email,phone,avatar','assignee:id,name'])
            ->when($request->string('channel')->value(), fn ($q, $channel) => $q->where('channel', $channel))
            ->when($request->string('status')->value(), fn ($q, $status) => $q->where('status', $status))
            ->latest('last_message_at')->paginate(min($request->integer('per_page', 25), 100));

        return response()->json($items);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        return response()->json(['conversation' => $conversation->load(['contact','assignee','messages' => fn ($q) => $q->latest()->limit(100)])]);
    }

    public function reply(Request $request, Conversation $conversation): JsonResponse
    {
        $data = $request->validate(['body' => ['required','string','max:10000']]);
        $message = $conversation->messages()->create([
            'tenant_id' => $request->user()->tenant_id,
            'direction' => 'outbound', 'sender_type' => 'user', 'sender_user_id' => $request->user()->id,
            'type' => 'text', 'body' => $data['body'], 'status' => 'queued', 'sent_at' => now(),
        ]);
        $conversation->update(['last_message_at' => $message->sent_at]);
        return response()->json(['message' => $message], 201);
    }
}
