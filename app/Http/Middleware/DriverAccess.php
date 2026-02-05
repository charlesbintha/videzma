<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DriverAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Non authentifie.'], 401);
        }

        if (auth()->user()->role !== 'driver' || auth()->user()->status !== 'active') {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        return $next($request);
    }
}
