<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Acces refuse.');
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'captured_at' => ['nullable', 'date'],
        ]);

        $location = app(\App\Services\LocationRecorder::class)->record($user, $validated);

        return response()->json([
            'data' => $this->formatLocation($location),
        ], 201);
    }

    public function index(Request $request, User $client)
    {
        $this->authorizeViewer($request->user(), $client);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $query = Location::where('user_id', $client->id);

        if (! empty($validated['from'])) {
            $query->where('captured_at', '>=', Carbon::parse($validated['from']));
        }

        if (! empty($validated['to'])) {
            $query->where('captured_at', '<=', Carbon::parse($validated['to']));
        }

        $limit = $validated['limit'] ?? 200;

        $locations = $query
            ->newest()
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $locations->map(fn (Location $location) => $this->formatLocation($location)),
        ]);
    }

    public function latest(Request $request, User $client)
    {
        $this->authorizeViewer($request->user(), $client);

        $location = Location::where('user_id', $client->id)
            ->newest()
            ->first();

        if (! $location) {
            return response()->json([
                'message' => 'Aucune position disponible.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatLocation($location),
        ]);
    }

    private function authorizeViewer(?User $viewer, User $client): void
    {
        if (! $viewer) {
            abort(401, 'Acces refuse.');
        }

        if ($viewer->role === 'admin') {
            return;
        }

        if ($viewer->role === 'client' && $viewer->id === $client->id) {
            return;
        }

        if ($viewer->role === 'driver') {
            $follows = ServiceRequest::where('driver_id', $viewer->id)
                ->where('client_id', $client->id)
                ->whereIn('status', ['assigned', 'accepted', 'in_progress'])
                ->exists();

            if ($follows) {
                return;
            }
        }

        abort(403, 'Acces refuse.');
    }

    private function formatLocation(Location $location): array
    {
        $timestamp = $location->captured_at ?? $location->created_at;

        return [
            'id' => $location->id,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'address' => $location->address,
            'captured_at' => $timestamp?->toIso8601String(),
            'is_fresh' => $location->isFresh(),
        ];
    }
}
