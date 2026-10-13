<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Database\Seeders\Support\DakarDemoLocations;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['client', 'driver'])->get();
        $positionsPerUser = 5;

        foreach ($users as $user) {
            for ($i = 0; $i < $positionsPerUser; $i++) {

                Location::create([
                    ...DakarDemoLocations::point(($user->id - 1) * $positionsPerUser + $i),
                    'user_id' => $user->id,
                    'captured_at' => now()->subMinutes(fake()->numberBetween(5, 1440)),
                ]);
            }
        }
    }
}
