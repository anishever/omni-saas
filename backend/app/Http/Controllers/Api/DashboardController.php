<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan' => $tenant->plan_id,
                'status' => $tenant->status,
            ],
            'workspace' => $tenant->workspaces()->where('status', 'active')->first(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
            ],
            'metrics' => [
                'contacts' => 0,
                'open_conversations' => 0,
                'active_campaigns' => 0,
                'automation_runs_today' => 0,
            ],
        ]);
    }
}
