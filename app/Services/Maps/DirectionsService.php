<?php

namespace App\Services\Maps;

class DirectionsService
{
    public function getFastestRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        $provider = config('services.routing.provider', 'google');

        return match ($provider) {
            'openrouteservice' => app(OpenRouteServiceDirections::class)->getFastestRoute($originLat, $originLng, $destinationLat, $destinationLng),
            'google' => app(GoogleMapsDirections::class)->getFastestRoute($originLat, $originLng, $destinationLat, $destinationLng),
            default => ['ok' => false, 'message' => 'Service de calcul des trajets non configuré. Contactez l’administrateur.'],
        };
    }
}
