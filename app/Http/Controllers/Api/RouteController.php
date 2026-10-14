<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\NavigationSession;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Maps\DirectionsService;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function fastest(Request $request, User $client, DirectionsService $maps)
    {
        $driver = $request->user();

        if (! $driver) {
            abort(401, 'Acces refuse.');
        }

        if (! in_array($driver->role, ['driver', 'admin'], true)) {
            abort(403, 'Acces refuse.');
        }

        if ($driver->role === 'driver') {
            $follows = ServiceRequest::where('driver_id', $driver->id)
                ->where('client_id', $client->id)
                ->whereIn('status', ['assigned', 'accepted', 'in_progress'])
                ->exists();

            if (! $follows) {
                abort(403, 'Acces refuse.');
            }
        }

        $validated = $request->validate([
            'origin_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'origin_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
        ]);

        $originLat = $validated['origin_latitude'] ?? null;
        $originLng = $validated['origin_longitude'] ?? null;

        if ($originLat === null || $originLng === null) {
            $origin = Location::where('user_id', $driver->id)
                ->newest()
                ->first();

            if (! $origin || ! $origin->isFresh()) {
                return response()->json([
                    'message' => 'Position du vidangeur manquante.',
                ], 422);
            }

            $originLat = (float) $origin->latitude;
            $originLng = (float) $origin->longitude;
        }

        $serviceRequest = null;

        if (! empty($validated['service_request_id'])) {
            $serviceRequest = ServiceRequest::whereKey($validated['service_request_id'])
                ->where('client_id', $client->id)
                ->whereIn('status', ['assigned', 'accepted', 'in_progress'])
                ->when($driver->role === 'driver', function ($query) use ($driver) {
                    $query->where('driver_id', $driver->id);
                })
                ->first();

            if (! $serviceRequest) {
                return response()->json([
                    'message' => 'Demande invalide.',
                ], 422);
            }
        }

        if (! $serviceRequest) {
            $services = ServiceRequest::where('client_id', $client->id)
                ->whereIn('status', ['assigned', 'accepted', 'in_progress'])
                ->when($driver->role === 'driver', fn ($query) => $query->where('driver_id', $driver->id))
                ->limit(2)->get();
            if ($services->count() !== 1) {
                return response()->json(['message' => 'Précisez la demande à rejoindre.'], 422);
            }
            $serviceRequest = $services->first();
        }
        $destination = $serviceRequest->location;
        if (! $destination) {
            return response()->json(['message' => 'Lieu de l’intervention manquant.'], 422);
        }

        $route = $maps->getFastestRoute(
            $originLat,
            $originLng,
            (float) $destination->latitude,
            (float) $destination->longitude
        );

        if (! $route['ok']) {
            return response()->json([
                'message' => $route['message'],
            ], 502);
        }

        $session = NavigationSession::create([
            'driver_id' => $driver->id,
            'service_request_id' => $serviceRequest?->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $itinerary = $session->itineraries()->create([
            'distance_km' => $route['distance_km'],
            'eta_minutes' => $route['eta_minutes'],
            'route_data' => $route['route_data'],
        ]);

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'itinerary_id' => $itinerary->id,
                'distance_km' => $itinerary->distance_km,
                'eta_minutes' => $itinerary->eta_minutes,
                'origin' => [
                    'latitude' => $originLat,
                    'longitude' => $originLng,
                ],
                'destination' => [
                    'latitude' => (float) $destination->latitude,
                    'longitude' => (float) $destination->longitude,
                ],
            ],
        ]);
    }
}
