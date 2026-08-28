<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelConnection;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['templates' => WhatsAppTemplate::query()->latest()->paginate(50)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel_connection_id' => ['required','integer'],
            'name' => ['required','string','max:512'],
            'language' => ['required','string','max:20'],
            'category' => ['nullable','string','max:40'],
            'components' => ['nullable','array'],
        ]);

        $connection = ChannelConnection::query()->whereKey($data['channel_connection_id'])->firstOrFail();
        abort_unless($connection->tenant_id === $request->user()->tenant_id && $connection->channel === 'whatsapp', 403);

        $template = WhatsAppTemplate::create([
            ...$data,
            'tenant_id' => $connection->tenant_id,
            'workspace_id' => $connection->workspace_id,
            'status' => 'draft',
        ]);

        return response()->json(['template' => $template], 201);
    }

    public function destroy(Request $request, WhatsAppTemplate $whatsappTemplate): JsonResponse
    {
        abort_unless($whatsappTemplate->tenant_id === $request->user()->tenant_id, 403);
        $whatsappTemplate->delete();
        return response()->json(['message' => 'Template removed.']);
    }
}
