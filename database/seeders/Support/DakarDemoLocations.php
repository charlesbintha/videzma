<?php

namespace Database\Seeders\Support;

final class DakarDemoLocations
{
    // Inland reference points; small offsets represent fictional demo addresses.
    // https://fr.wikipedia.org/wiki/Stade_Demba-Diop
    // https://fr.wikipedia.org/wiki/Stade_Iba-Mar-Diop
    // https://fr.wikipedia.org/wiki/Mosquée_Massalikul_Jinaan
    // https://commons.wikimedia.org/wiki/File:Grande_mosquée_de_Dakar.jpg
    private const AREAS = [
        ['latitude' => 14.710556, 'longitude' => -17.458056, 'name' => 'Sicap-Liberté, secteur Demba Diop'],
        ['latitude' => 14.679513, 'longitude' => -17.446913, 'name' => 'Médina, secteur Iba Mar Diop'],
        ['latitude' => 14.701167, 'longitude' => -17.448000, 'name' => 'Colobane, secteur Massalikoul Djinane'],
        ['latitude' => 14.678061, 'longitude' => -17.443450, 'name' => 'Médina, secteur Grande Mosquée'],
    ];

    public static function point(int $index): array
    {
        $area = self::AREAS[$index % count(self::AREAS)];
        $offset = intdiv($index, count(self::AREAS));

        return [
            'latitude' => round($area['latitude'] + (($offset % 5) - 2) * 0.00045, 7),
            'longitude' => round($area['longitude'] + ((intdiv($offset, 5) % 7) - 3) * 0.00045, 7),
            'address' => 'Démo — '.$area['name'].', Dakar',
        ];
    }
}
