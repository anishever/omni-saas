<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\ProvisionTenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request, ProvisionTenantService $provisioner): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required', 'string', 'max:160'],
        ]);

        $result = $provisioner->create(
            $data['company_name'],
            $data['name'],
            $data['email'],
            $data['password'],
        );

        $token = $result['user']->createToken('omni-web')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'token' => $token,
            'user' => $result['user']->load('tenant', 'roles.permissions'),
            'workspace' => $result['workspace'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password) || $user->status !== 'active') {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('omni-web')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user->load('tenant', 'roles.permissions')]);
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
