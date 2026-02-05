<?php

namespace App\Services\Maps;

use Illuminate\Support\Facades\Http;

class GoogleMapsDirections
{
    public function getFastestRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        $key = config('services.google_maps.key');

        if (!$key) {
            return [
                'ok' => false,
                'message' => 'Cle Google Maps manquante.',
            ];
        }

        $response = Http::withoutVerifying()->get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $originLat . ',' . $originLng,
            'destination' => $destinationLat . ',' . $destinationLng,
            'key' => $key,
            'mode' => 'driving',
            'departure_time' => 'now',
            'traffic_model' => 'best_guess',
            'avoid' => 'highways|tolls',
            'region' => 'sn',
            'language' => 'fr',
        ]);

        if (!$response->ok()) {
            return [
                'ok' => false,
                'message' => 'Service carte indisponible.',
            ];
        }

        $payload = $response->json();
        $leg = $payload['routes'][0]['legs'][0] ?? null;

        if (($payload['status'] ?? '') !== 'OK' || !$leg) {
            return [
                'ok' => false,
                'message' => 'Aucun itineraire trouve.',
            ];
        }

        $distanceMeters = $leg['distance']['value'] ?? null;
        $durationSeconds = $leg['duration_in_traffic']['value'] ?? ($leg['duration']['value'] ?? null);

        return [
            'ok' => true,
            'distance_km' => $distanceMeters !== null ? round($distanceMeters / 1000, 2) : null,
            'eta_minutes' => $durationSeconds !== null ? (int) ceil($durationSeconds / 60) : null,
            'route_data' => $payload,
        ];
    }
}
