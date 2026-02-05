<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides.',
            ], 422);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Compte inactif.',
            ], 403);
        }

        $tokenName = $validated['device_name'] ?? ($request->userAgent() ?: 'mobile');
        $token = $user->createToken($tokenName);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $this->formatUser($user),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
            'status' => 'active',
            'locale' => 'fr',
        ]);

        $tokenName = $validated['device_name'] ?? ($request->userAgent() ?: 'mobile');
        $token = $user->createToken($tokenName);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Acces refuse.',
            ], 401);
        }

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Deconnexion OK.',
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Acces refuse.',
            ], 401);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['sometimes', 'string', 'max:32'],
            'locale' => ['sometimes', 'string', 'in:fr,en'],
        ]);

        $user->update($validated);

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status,
            'locale' => $user->locale,
        ];
    }

    public function registerDeviceToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Acces refuse.',
            ], 401);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['required', 'string', 'in:android,ios,web'],
        ]);

        // Deactivate existing tokens with the same value for other users
        DeviceToken::where('token', $validated['token'])
            ->where('user_id', '!=', $user->id)
            ->update(['is_active' => false]);

        // Create or update the token for this user
        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $validated['token'],
            ],
            [
                'platform' => $validated['platform'],
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'data' => [
                'id' => $deviceToken->id,
                'token' => $deviceToken->token,
                'platform' => $deviceToken->platform,
                'is_active' => $deviceToken->is_active,
            ],
        ], 201);
    }

    public function removeDeviceToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Acces refuse.',
            ], 401);
        }

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
        ]);

        $deleted = DeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => 'Token non trouve.',
            ], 404);
        }

        return response()->json([
            'message' => 'Token supprime.',
        ]);
    }
}
