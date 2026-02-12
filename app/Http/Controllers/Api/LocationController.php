<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\DriverPositionUpdated;
use App\Models\ConsultationRequest;
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

        if (!$user) {
            abort(401, 'Acces refuse.');
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'captured_at' => ['nullable', 'date'],
        ]);

        $location = $user->locations()->create([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address' => $validated['address'] ?? null,
            'captured_at' => isset($validated['captured_at'])
                ? Carbon::parse($validated['captured_at'])
                : now(),
        ]);

        // Si c'est un vidangeur, broadcaster la position aux clients qui le suivent
        if ($user->role === 'driver') {
            $this->broadcastDriverPosition($user, $location);
        }

        return response()->json([
            'data' => $this->formatLocation($location),
        ], 201);
    }

    /**
     * Broadcast driver position to all active service requests
     */
    private function broadcastDriverPosition(User $driver, Location $location): void
    {
        // Trouver les demandes de service actives pour ce vidangeur
        $activeRequests = ServiceRequest::where('driver_id', $driver->id)
            ->whereIn('status', ['assigned', 'accepted', 'in_progress', 'en_route'])
            ->with('client')
            ->get();

        foreach ($activeRequests as $serviceRequest) {
            // Calculer la distance et l'ETA
            $distance = null;
            $eta = null;

            // Utiliser la location de la demande de service pour calculer la distance
            $clientLocation = $serviceRequest->location;
            if ($clientLocation && $clientLocation->latitude && $clientLocation->longitude) {
                $distance = $this->calculateDistance(
                    $location->latitude,
                    $location->longitude,
                    $clientLocation->latitude,
                    $clientLocation->longitude
                );
                // Estimation: 30 km/h en ville
                $eta = $distance > 0 ? (int) ceil(($distance / 1000) / 30 * 60) : 0;
            }

            // Broadcaster l'événement
            broadcast(new DriverPositionUpdated(
                $serviceRequest,
                $location,
                $distance,
                $eta
            ))->toOthers();
        }
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    public function index(Request $request, User $patient)
    {
        $this->authorizeViewer($request->user(), $patient);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $query = Location::where('user_id', $patient->id);

        if (!empty($validated['from'])) {
            $query->where('captured_at', '>=', Carbon::parse($validated['from']));
        }

        if (!empty($validated['to'])) {
            $query->where('captured_at', '<=', Carbon::parse($validated['to']));
        }

        $limit = $validated['limit'] ?? 200;

        $locations = $query
            ->orderByDesc('captured_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $locations->map(fn (Location $location) => $this->formatLocation($location)),
        ]);
    }

    public function latest(Request $request, User $patient)
    {
        $this->authorizeViewer($request->user(), $patient);

        $location = Location::where('user_id', $patient->id)
            ->orderByDesc('captured_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$location) {
            return response()->json([
                'message' => 'Aucune position disponible.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatLocation($location),
        ]);
    }

    private function authorizeViewer(?User $viewer, User $patient): void
    {
        if (!$viewer) {
            abort(401, 'Acces refuse.');
        }

        if ($viewer->role === 'admin') {
            return;
        }

        if ($viewer->role === 'patient' && $viewer->id === $patient->id) {
            return;
        }

        if ($viewer->role === 'doctor') {
            $follows = ConsultationRequest::where('doctor_id', $viewer->id)
                ->where('patient_id', $patient->id)
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
        ];
    }
}
