<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelConnectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $connections = ChannelConnection::query()
            ->where('workspace_id', $request->user()->tenant->workspaces()->where('status', 'active')->value('id'))
            ->get(['id','workspace_id','channel','name','external_id','settings','status','created_at']);

        return response()->json(['connections' => $connections]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'workspace_id' => ['required','integer'],
            'channel' => ['required','in:whatsapp'],
            'name' => ['required','string','max:120'],
            'external_id' => ['required','string','max:120'],
            'access_token' => ['required','string'],
            'verify_token' => ['nullable','string'],
            'settings' => ['nullable','array'],
        ]);

        abort_unless($request->user()->tenant->workspaces()->whereKey($data['workspace_id'])->exists(), 403);

        $connection = ChannelConnection::create([
            ...$data,
            'tenant_id' => $request->user()->tenant_id,
            'status' => 'active',
        ]);

        return response()->json(['connection' => $connection->makeVisible(['access_token','verify_token'])], 201);
    }

    public function update(Request $request, ChannelConnection $channelConnection): JsonResponse
    {
        abort_unless($channelConnection->tenant_id === $request->user()->tenant_id, 403);
        $data = $request->validate([
            'name' => ['sometimes','string','max:120'],
            'external_id' => ['sometimes','string','max:120'],
            'access_token' => ['sometimes','string'],
            'verify_token' => ['nullable','string'],
            'settings' => ['nullable','array'],
            'status' => ['sometimes','in:active,inactive'],
        ]);
        $channelConnection->update($data);
        return response()->json(['connection' => $channelConnection->fresh()]);
    }

    public function destroy(Request $request, ChannelConnection $channelConnection): JsonResponse
    {
        abort_unless($channelConnection->tenant_id === $request->user()->tenant_id, 403);
        $channelConnection->update(['status' => 'inactive']);
        return response()->json(['message' => 'Channel disconnected.']);
    }
}
