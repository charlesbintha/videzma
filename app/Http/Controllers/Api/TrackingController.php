<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Get driver's current position for real-time tracking (optimized for polling)
     *
     * Features:
     * - ETag support for 304 Not Modified responses (reduces bandwidth)
     * - Minimal JSON payload (short keys)
     * - Cache headers for efficient polling
     */
    public function getDriverPosition(Request $request, int $serviceRequestId): JsonResponse
    {
        $user = $request->user();

        // Find active navigation for this client
        $serviceRequest = ServiceRequest::where('id', $serviceRequestId)
            ->where('client_id', $user->id)
            ->trackingActive()
            ->first();

        // No active navigation
        if (! $serviceRequest) {
            return response()->json([
                'tracking' => false,
                'message' => 'Aucune navigation active',
            ]);
        }

        // Get driver's latest position
        $driverLocation = Location::where('user_id', $serviceRequest->driver_id)
            ->sinceNavigation($serviceRequest)->newest()
            ->first();

        if (! $driverLocation) {
            return response()->json([
                'tracking' => true,
                'position' => null,
                'message' => 'Position du vidangeur non disponible',
            ]);
        }

        // Calculate ETag based on position and timestamp
        $etag = '"'.md5(
            $driverLocation->latitude.
            $driverLocation->longitude.(int) $driverLocation->isFresh().
            $driverLocation->recordedAt()?->timestamp
        ).'"';

        // Check If-None-Match header for conditional request
        $clientEtag = $request->header('If-None-Match');
        if ($clientEtag && $clientEtag === $etag) {
            return response()->json(null, 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'private, max-age=2');
        }

        // Calculate distance and ETA
        $clientLocation = $serviceRequest->location;
        $distance = null;

        if ($clientLocation) {
            $distance = $this->calculateDistance(
                $driverLocation->latitude,
                $driverLocation->longitude,
                $clientLocation->latitude,
                $clientLocation->longitude
            );
        }

        // Minimal response with short keys for bandwidth efficiency
        return response()->json([
            'tracking' => true,
            'p' => [
                'lat' => round((float) $driverLocation->latitude, 6),
                'lng' => round((float) $driverLocation->longitude, 6),
                't' => $driverLocation->recordedAt()?->timestamp,
            ],
            'd' => $distance !== null ? (int) round($distance) : null,
            'eta' => null,
            'eta_source' => null,
            'distance_source' => 'straight_line',
            'is_fresh' => $driverLocation->isFresh(),
            'stale_after_seconds' => 120,
            'driver' => [
                'name' => $serviceRequest->driver->name ?? 'Vidangeur',
                'phone' => $serviceRequest->driver->phone ?? null,
            ],
        ])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'private, max-age=2');
    }

    /**
     * Check if tracking is available for a service request
     */
    public function checkTrackingStatus(Request $request, int $serviceRequestId): JsonResponse
    {
        $user = $request->user();

        $serviceRequest = ServiceRequest::where('id', $serviceRequestId)
            ->where('client_id', $user->id)
            ->first();

        if (! $serviceRequest) {
            return response()->json([
                'available' => false,
                'reason' => 'not_found',
            ], 404);
        }

        $isActive = $serviceRequest->isTrackingActive();

        return response()->json([
            'available' => $isActive,
            'status' => $serviceRequest->status,
            'navigation_started_at' => $serviceRequest->navigation_started_at?->toIso8601String(),
        ]);
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
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
}
