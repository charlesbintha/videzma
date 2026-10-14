<?php

namespace App\Services\Maps;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsDirections
{
    public function getFastestRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        $key = config('services.google_maps.key');

        if (! $key) {
            return [
                'ok' => false,
                'message' => 'Cle Google Maps manquante.',
            ];
        }

        try {
            $response = Http::connectTimeout(5)->timeout(15)->get('https://maps.googleapis.com/maps/api/directions/json', [
                'origin' => $originLat.','.$originLng,
                'destination' => $destinationLat.','.$destinationLng,
                'key' => $key,
                'mode' => 'driving',
                'departure_time' => 'now',
                'traffic_model' => 'best_guess',
                'avoid' => 'highways|tolls',
                'region' => 'sn',
                'language' => 'fr',
            ]);
        } catch (ConnectionException $exception) {
            return ['ok' => false, 'message' => 'Le service de calcul du trajet ne répond pas. Réessayez dans un instant.'];
        }

        if (! $response->ok() && ! isset($response->json()['status'])) {
            return [
                'ok' => false,
                'message' => 'Service carte indisponible.',
            ];
        }

        $payload = $response->json();
        $leg = $payload['routes'][0]['legs'][0] ?? null;

        $status = $payload['status'] ?? 'UNKNOWN_ERROR';
        if ($status !== 'OK' || ! $leg) {
            // Never log the request URL (API key), coordinates or Google's raw error.
            Log::warning('Google Directions unavailable', ['status' => $status]);
            $billingRequired = $status === 'REQUEST_DENIED'
                && str_contains(strtolower($payload['error_message'] ?? ''), 'billing');
            $message = $billingRequired
                ? 'Le calcul des trajets est indisponible : la facturation Google Maps doit être activée par l’administrateur.'
                : match ($status) {
                    'ZERO_RESULTS', 'NOT_FOUND' => 'Aucun itinéraire routier trouvé entre ces positions. Vérifiez la localisation du client.',
                    'REQUEST_DENIED' => 'Le service de calcul des trajets est refusé par Google Maps. Contactez l’administrateur.',
                    'OVER_QUERY_LIMIT', 'OVER_DAILY_LIMIT' => 'Le quota de calcul des trajets est atteint. Contactez l’administrateur.',
                    default => 'Le calcul du trajet est temporairement indisponible. Réessayez dans un instant.',
                };

            return ['ok' => false, 'message' => $message];
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
