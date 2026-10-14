<?php

namespace App\Services\Maps;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenRouteServiceDirections
{
    public function getFastestRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        $key = config('services.openrouteservice.key');
        if (! $key) {
            return ['ok' => false, 'message' => 'Le calcul des trajets OpenRouteService doit être activé par l’administrateur.'];
        }

        // Short-lived caching prevents repeated retries from spending the free quota.
        // Coordinates are hashed, never put into cache key names or logs.
        $cacheKey = 'ors-route:'.hash('sha256', json_encode([$originLat, $originLng, $destinationLat, $destinationLng]));
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }
        try {
            $response = Http::connectTimeout(5)->timeout(15)->withHeaders(['Authorization' => $key])->post(
                'https://api.heigit.org/openrouteservice/v2/directions/driving-car/geojson',
                ['coordinates' => [[$originLng, $originLat], [$destinationLng, $destinationLat]], 'language' => 'fr', 'instructions' => true],
            );
        } catch (ConnectionException) {
            return ['ok' => false, 'message' => 'Le calcul du trajet ne répond pas. Réessayez dans un instant.'];
        }
        if (! $response->successful()) {
            return ['ok' => false, 'message' => match ($response->status()) {
                401, 403 => 'Le service de calcul des trajets n’est pas autorisé. Contactez l’administrateur.',
                429 => 'Le quota gratuit de calcul des trajets est atteint. Réessayez plus tard.',
                404 => 'Aucun itinéraire routier trouvé. Vérifiez les positions de départ et d’arrivée.',
                default => 'Le calcul du trajet est indisponible. Réessayez dans un instant.',
            }];
        }
        $feature = $response->json('features.0');
        $coordinates = $feature['geometry']['coordinates'] ?? [];
        $summary = $feature['properties']['summary'] ?? null;
        if (count($coordinates) < 2 || ! isset($summary['distance'], $summary['duration'])) {
            return ['ok' => false, 'message' => 'Aucun itinéraire routier trouvé entre ces positions.'];
        }

        // Preserve the existing API contract so already installed APKs remain readable.
        $steps = [];
        foreach ($feature['properties']['segments'] ?? [] as $segment) {
            foreach ($segment['steps'] ?? [] as $step) {
                [$start, $end] = $step['way_points'] ?? [0, 0];
                if (! isset($coordinates[$start], $coordinates[$end])) {
                    continue;
                }
                $steps[] = [
                    'html_instructions' => htmlspecialchars($step['instruction'] ?? '', ENT_QUOTES, 'UTF-8'),
                    'distance' => ['value' => (int) round($step['distance'] ?? 0)],
                    'duration' => ['value' => (int) round($step['duration'] ?? 0)],
                    'start_location' => ['lat' => $coordinates[$start][1], 'lng' => $coordinates[$start][0]],
                    'end_location' => ['lat' => $coordinates[$end][1], 'lng' => $coordinates[$end][0]],
                    'polyline' => ['points' => $this->encodePolyline(array_slice($coordinates, $start, $end - $start + 1))],
                    'maneuver' => match ($step['type'] ?? -1) {
                        0 => 'turn-left', 1 => 'turn-right', 2 => 'turn-sharp-left', 3 => 'turn-sharp-right',
                        4 => 'turn-slight-left', 5 => 'turn-slight-right', 7 => 'roundabout-right', 9 => 'uturn-left', default => 'straight',
                    },
                ];
            }
        }
        $result = [
            'ok' => true,
            'distance_km' => round($summary['distance'] / 1000, 2),
            'eta_minutes' => (int) ceil($summary['duration'] / 60),
            'route_data' => ['provider' => 'openrouteservice', 'attribution' => '© openrouteservice.org by HeiGIT | Map data © OpenStreetMap contributors', 'routes' => [[
                'overview_polyline' => ['points' => $this->encodePolyline($coordinates)],
                'legs' => [[
                    'distance' => ['value' => (int) round($summary['distance'])],
                    'duration' => ['value' => (int) round($summary['duration'])],
                    'steps' => $steps,
                ]],
            ]]],
        ];
        Cache::put($cacheKey, $result, now()->addSeconds(20));

        return $result;
    }

    private function encodePolyline(array $coordinates): string
    {
        $encoded = '';
        $previous = [0, 0];
        foreach ($coordinates as $point) {
            foreach ([$point[1], $point[0]] as $axis => $value) {
                $integer = (int) round($value * 1e5);
                $delta = $integer - $previous[$axis];
                $previous[$axis] = $integer;
                $delta = $delta < 0 ? ~($delta << 1) : ($delta << 1);
                while ($delta >= 0x20) {
                    $encoded .= chr((0x20 | ($delta & 0x1F)) + 63);
                    $delta >>= 5;
                }
                $encoded .= chr($delta + 63);
            }
        }

        return $encoded;
    }
}
