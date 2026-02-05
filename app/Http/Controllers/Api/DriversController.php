<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    /**
     * Liste les vidangeurs disponibles avec leur distance par rapport au client
     */
    public function available(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $clientLat = $request->latitude;
        $clientLng = $request->longitude;

        // Récupérer tous les vidangeurs actifs
        $drivers = User::where('role', 'driver')
            ->where('status', 'active')
            ->with(['driverProfile'])
            ->get();

        $driversWithDistance = [];

        foreach ($drivers as $driver) {
            // Récupérer la dernière localisation du vidangeur
            $driverLocation = Location::where('user_id', $driver->id)
                ->latest('captured_at')
                ->first();

            if (!$driverLocation) {
                continue; // Ignorer les vidangeurs sans localisation
            }

            // Calculer la distance
            $distance = $this->calculateDistance(
                $clientLat,
                $clientLng,
                (float) $driverLocation->latitude,
                (float) $driverLocation->longitude
            );

            $distanceKm = round($distance / 1000, 2);

            $driversWithDistance[] = [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'email' => $driver->email,
                'vehicle_type' => $driver->driverProfile?->vehicle_type ?? 'Camion citerne',
                'tank_capacity' => $driver->driverProfile?->tank_capacity ?? null,
                'zone_coverage' => $driver->driverProfile?->zone_coverage ?? null,
                'location' => [
                    'latitude' => (float) $driverLocation->latitude,
                    'longitude' => (float) $driverLocation->longitude,
                ],
                'distance_km' => $distanceKm,
                'distance_formatted' => $this->formatDistance($distance),
            ];
        }

        // Trier par distance croissante
        usort($driversWithDistance, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

        return response()->json([
            'data' => $driversWithDistance,
            'meta' => [
                'base_price' => ServiceRequest::BASE_PRICE,
                'price_per_m3' => ServiceRequest::PRICE_PER_M3,
                'currency' => 'FCFA',
            ],
        ]);
    }

    /**
     * Détails d'un vidangeur spécifique
     */
    public function show(Request $request, User $driver): JsonResponse
    {
        if ($driver->role !== 'driver') {
            return response()->json([
                'message' => 'Vidangeur non trouvé',
            ], 404);
        }

        $driver->load('driverProfile');

        $driverLocation = Location::where('user_id', $driver->id)
            ->latest('captured_at')
            ->first();

        return response()->json([
            'data' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'email' => $driver->email,
                'vehicle_type' => $driver->driverProfile?->vehicle_type,
                'vehicle_plate' => $driver->driverProfile?->vehicle_plate,
                'tank_capacity' => $driver->driverProfile?->tank_capacity,
                'zone_coverage' => $driver->driverProfile?->zone_coverage,
                'verification_status' => $driver->driverProfile?->verification_status,
                'bio' => $driver->driverProfile?->bio,
                'location' => $driverLocation ? [
                    'latitude' => (float) $driverLocation->latitude,
                    'longitude' => (float) $driverLocation->longitude,
                    'captured_at' => $driverLocation->captured_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    /**
     * Calcule le prix estimé pour un service
     */
    public function estimatePrice(Request $request): JsonResponse
    {
        $request->validate([
            'estimated_volume' => 'required|numeric|min:1|max:50',
        ]);

        $volume = $request->estimated_volume;
        $price = ServiceRequest::BASE_PRICE + (($volume - 1) * ServiceRequest::PRICE_PER_M3);

        return response()->json([
            'data' => [
                'estimated_volume' => $volume,
                'price_amount' => $price,
                'price_formatted' => number_format($price, 0, ',', ' ') . ' FCFA',
                'base_price' => ServiceRequest::BASE_PRICE,
                'price_per_m3' => ServiceRequest::PRICE_PER_M3,
            ],
        ]);
    }

    /**
     * Calcule la distance entre deux points (formule de Haversine)
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // en mètres

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

    /**
     * Formate la distance pour l'affichage
     */
    private function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return round($meters) . ' m';
        }
        return number_format($meters / 1000, 1, ',', ' ') . ' km';
    }
}
