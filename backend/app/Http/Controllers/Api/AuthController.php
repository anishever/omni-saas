<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required', 'string', 'max:160'],
        ]);

        $tenant = Tenant::create([
            'name' => $data['company_name'],
            'slug' => Str::slug($data['company_name']) . '-' . Str::lower(Str::random(6)),
            'email' => $data['email'],
            'status' => 'active',
        ]);

        $workspace = $tenant->workspaces()->create([
            'name' => 'Main Workspace',
            'slug' => 'main',
            'status' => 'active',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $token = $user->createToken('omni-web')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'token' => $token,
            'user' => $user->load('tenant'),
            'workspace' => $workspace,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password) || $user->status !== 'active') {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('omni-web')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user->load('tenant')]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()->load('tenant', 'roles.permissions')]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
