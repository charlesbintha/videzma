<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Location;
use App\Services\Maps\GoogleMapsDirections;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NavigationController extends Controller
{
    public function __construct(
        protected GoogleMapsDirections $mapsService
    ) {}

    /**
     * Start navigation to a client
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'service_request_id' => 'required|integer|exists:service_requests,id',
            'origin_latitude' => 'required|numeric|between:-90,90',
            'origin_longitude' => 'required|numeric|between:-180,180',
        ]);

        $driver = $request->user();
        $serviceRequest = ServiceRequest::where('id', $request->service_request_id)
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['accepted', 'in_progress'])
            ->firstOrFail();

        // Get client's location from the service request
        $clientLocation = $serviceRequest->location;

        if (!$clientLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Localisation du client non disponible',
            ], 404);
        }

        // Calculate route using Google Maps
        $route = $this->mapsService->getFastestRoute(
            $request->origin_latitude,
            $request->origin_longitude,
            (float) $clientLocation->latitude,
            (float) $clientLocation->longitude
        );

        if (!$route['ok']) {
            return response()->json([
                'success' => false,
                'message' => $route['message'] ?? 'Erreur calcul itineraire',
            ], 500);
        }

        // Store driver's current position
        Location::create([
            'user_id' => $driver->id,
            'latitude' => $request->origin_latitude,
            'longitude' => $request->origin_longitude,
            'captured_at' => now(),
        ]);

        // Mark service request as in_navigation
        $serviceRequest->update([
            'navigation_started_at' => now(),
        ]);

        $distanceMeters = ($route['distance_km'] ?? 0) * 1000;
        $etaMinutes = $route['eta_minutes'] ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $serviceRequest->id,
                'service_request_id' => $serviceRequest->id,
                'client' => [
                    'id' => $serviceRequest->client_id,
                    'name' => $serviceRequest->client->name ?? 'Client',
                    'phone' => $serviceRequest->client->phone ?? null,
                    'location' => [
                        'latitude' => (float) $clientLocation->latitude,
                        'longitude' => (float) $clientLocation->longitude,
                        'address' => $serviceRequest->address ?? $clientLocation->address,
                    ],
                ],
                'distance_meters' => (int) $distanceMeters,
                'eta_minutes' => $etaMinutes,
                'formatted_distance' => $this->formatDistance($distanceMeters),
                'formatted_eta' => $this->formatDuration($etaMinutes),
                'polyline' => $route['route_data']['routes'][0]['overview_polyline']['points'] ?? null,
                'route_data' => $route['route_data'] ?? null,
            ],
        ]);
    }

    /**
     * Update driver's position during navigation
     */
    public function updatePosition(Request $request): JsonResponse
    {
        $request->validate([
            'service_request_id' => 'required|integer|exists:service_requests,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|min:0|max:360',
        ]);

        $driver = $request->user();
        $serviceRequest = ServiceRequest::where('id', $request->service_request_id)
            ->where('driver_id', $driver->id)
            ->whereNotNull('navigation_started_at')
            ->firstOrFail();

        // Store new position
        Location::create([
            'user_id' => $driver->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'captured_at' => now(),
        ]);

        // Get client's location
        $clientLocation = $serviceRequest->location;

        if (!$clientLocation) {
            return response()->json([
                'success' => true,
                'data' => [
                    'position_updated' => true,
                ],
            ]);
        }

        // Calculate remaining distance
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $clientLocation->latitude,
            $clientLocation->longitude
        );

        // Estimate remaining time (assuming average speed of 30 km/h in city)
        $avgSpeed = $request->speed && $request->speed > 0 ? $request->speed : 30;
        $etaMinutes = ($distance / 1000) / $avgSpeed * 60;

        return response()->json([
            'success' => true,
            'data' => [
                'position_updated' => true,
                'distance_to_client' => round($distance),
                'eta_minutes' => round($etaMinutes),
                'formatted_distance' => $this->formatDistance($distance),
                'formatted_eta' => $this->formatDuration($etaMinutes),
            ],
        ]);
    }

    /**
     * Get updated route (recalculate from current position)
     */
    public function refreshRoute(Request $request): JsonResponse
    {
        $request->validate([
            'service_request_id' => 'required|integer|exists:service_requests,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $driver = $request->user();
        $serviceRequest = ServiceRequest::where('id', $request->service_request_id)
            ->where('driver_id', $driver->id)
            ->whereNotNull('navigation_started_at')
            ->firstOrFail();

        // Get client's location
        $clientLocation = $serviceRequest->location;

        if (!$clientLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Localisation du client non disponible',
            ], 404);
        }

        // Calculate new route
        $route = $this->mapsService->getFastestRoute(
            $request->latitude,
            $request->longitude,
            (float) $clientLocation->latitude,
            (float) $clientLocation->longitude
        );

        if (!$route['ok']) {
            return response()->json([
                'success' => false,
                'message' => $route['message'] ?? 'Erreur calcul itineraire',
            ], 500);
        }

        // Update driver's position
        Location::create([
            'user_id' => $driver->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'captured_at' => now(),
        ]);

        $distanceMeters = ($route['distance_km'] ?? 0) * 1000;
        $etaMinutes = $route['eta_minutes'] ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $serviceRequest->id,
                'distance_meters' => (int) $distanceMeters,
                'eta_minutes' => $etaMinutes,
                'formatted_distance' => $this->formatDistance($distanceMeters),
                'formatted_eta' => $this->formatDuration($etaMinutes),
                'polyline' => $route['route_data']['routes'][0]['overview_polyline']['points'] ?? null,
                'route_data' => $route['route_data'] ?? null,
            ],
        ]);
    }

    /**
     * Stop navigation
     */
    public function stop(Request $request): JsonResponse
    {
        $request->validate([
            'service_request_id' => 'required|integer|exists:service_requests,id',
        ]);

        $driver = $request->user();
        $serviceRequest = ServiceRequest::where('id', $request->service_request_id)
            ->where('driver_id', $driver->id)
            ->whereNotNull('navigation_started_at')
            ->firstOrFail();

        $serviceRequest->update([
            'navigation_ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Navigation terminee',
        ]);
    }

    /**
     * Get driver's current position (for client to track)
     */
    public function getDriverPosition(int $serviceRequestId): JsonResponse
    {
        $user = request()->user();
        $serviceRequest = ServiceRequest::where('id', $serviceRequestId)
            ->where('client_id', $user->id)
            ->whereNotNull('navigation_started_at')
            ->whereNull('navigation_ended_at')
            ->firstOrFail();

        $driverLocation = Location::where('user_id', $serviceRequest->driver_id)
            ->latest('captured_at')
            ->first();

        if (!$driverLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Position du vidangeur non disponible',
            ], 404);
        }

        // Get client's location
        $clientLocation = $serviceRequest->location;

        $distance = null;
        $eta = null;

        if ($clientLocation) {
            $distance = $this->calculateDistance(
                $driverLocation->latitude,
                $driverLocation->longitude,
                $clientLocation->latitude,
                $clientLocation->longitude
            );
            $eta = round(($distance / 1000) / 30 * 60); // Assuming 30 km/h
        }

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => [
                    'name' => $serviceRequest->driver->name ?? 'Vidangeur',
                    'phone' => $serviceRequest->driver->phone ?? null,
                ],
                'position' => [
                    'latitude' => $driverLocation->latitude,
                    'longitude' => $driverLocation->longitude,
                    'captured_at' => $driverLocation->captured_at->toIso8601String(),
                ],
                'distance_meters' => $distance ? round($distance) : null,
                'eta_minutes' => $eta,
                'formatted_distance' => $distance ? $this->formatDistance($distance) : null,
                'formatted_eta' => $eta ? $this->formatDuration($eta) : null,
            ],
        ]);
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) * sin($deltaLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return round($meters) . ' m';
        }
        return number_format($meters / 1000, 1) . ' km';
    }

    private function formatDuration(float $minutes): string
    {
        if ($minutes < 60) {
            return round($minutes) . ' min';
        }
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        return $hours . ' h ' . $mins . ' min';
    }
}
