<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DriverProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = User::where('role', 'driver')->get();

        if ($drivers->isEmpty()) {
            return;
        }

        $vehicleTypes = ['camion_citerne', 'camion_pompe', 'remorque'];
        $zones = ['Dakar', 'Pikine', 'Guediawaye', 'Rufisque', 'Thies', 'Saint-Louis'];

        foreach ($drivers as $driver) {
            $status = ($driver->id % 2 === 0) ? 'approved' : 'pending';
            $verifiedAt = $status === 'approved' ? now()->subDays(fake()->numberBetween(1, 60)) : null;

            DriverProfile::updateOrCreate(
                ['user_id' => $driver->id],
                [
                    'license_number' => sprintf('LIC-%05d', $driver->id),
                    'vehicle_type' => fake()->randomElement($vehicleTypes),
                    'vehicle_plate' => strtoupper(fake()->bothify('DK-####-??')),
                    'tank_capacity' => fake()->randomElement([5, 8, 10, 12, 15, 20]),
                    'zone_coverage' => implode(', ', fake()->randomElements($zones, fake()->numberBetween(1, 3))),
                    'verification_status' => $status,
                    'verified_at' => $verifiedAt,
                    'bio' => fake()->sentence(8),
                ]
            );
        }
    }
}
