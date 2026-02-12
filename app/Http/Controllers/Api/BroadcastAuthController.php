<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class BroadcastAuthController extends Controller
{
    /**
     * Authenticate the request for channel access.
     * This endpoint is used by mobile apps with Sanctum tokens.
     */
    public function authenticate(Request $request)
    {
        // Get the user from Sanctum token
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Laravel's Broadcast::auth handles the channel authorization
        return Broadcast::auth($request);
    }
}
