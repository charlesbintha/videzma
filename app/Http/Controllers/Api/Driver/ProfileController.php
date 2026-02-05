<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Voir le profil du vidangeur connecte.
     */
    public function show(Request $request)
    {
        $driver = $request->user();
        $driver->load('driverProfile');

        return response()->json([
            'data' => $this->formatProfile($driver),
        ]);
    }

    /**
     * Modifier le profil du vidangeur.
     */
    public function update(Request $request)
    {
        $driver = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['sometimes', 'string', 'max:32'],
            'locale' => ['sometimes', 'string', 'in:fr,en'],
            'vehicle_type' => ['sometimes', 'string', 'max:100'],
            'vehicle_plate' => ['sometimes', 'string', 'max:20'],
            'tank_capacity' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'zone_coverage' => ['sometimes', 'string', 'max:200'],
            'bio' => ['sometimes', 'string', 'max:1000'],
        ]);

        // Update user fields
        $userFields = array_intersect_key($validated, array_flip(['name', 'phone', 'locale']));
        if (!empty($userFields)) {
            $driver->update($userFields);
        }

        // Update driver profile fields
        $profileFields = array_intersect_key($validated, array_flip([
            'vehicle_type', 'vehicle_plate', 'tank_capacity', 'zone_coverage', 'bio'
        ]));
        if (!empty($profileFields)) {
            $driver->driverProfile()->updateOrCreate(
                ['user_id' => $driver->id],
                $profileFields
            );
        }

        $driver->load('driverProfile');

        return response()->json([
            'data' => $this->formatProfile($driver),
        ]);
    }

    private function formatProfile($driver): array
    {
        $profile = $driver->driverProfile;

        return [
            'id' => $driver->id,
            'name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'role' => $driver->role,
            'status' => $driver->status,
            'locale' => $driver->locale,
            'vehicle_type' => $profile?->vehicle_type,
            'vehicle_plate' => $profile?->vehicle_plate,
            'tank_capacity' => $profile?->tank_capacity,
            'zone_coverage' => $profile?->zone_coverage,
            'license_number' => $profile?->license_number,
            'verification_status' => $profile?->verification_status,
            'verified_at' => $profile?->verified_at?->toIso8601String(),
            'bio' => $profile?->bio,
        ];
    }
}
