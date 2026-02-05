<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['patient', 'doctor'])->get();
        $districts = [
            'Plateau',
            'Medina',
            'Almadies',
            'Point E',
            'Parcelles Assainies',
            'Yoff',
            'Fann',
            'Ngor',
            'Grand Dakar',
            'Mermoz',
            'HLM',
            'Ouakam',
            'Sacre-Coeur',
        ];
        $positionsPerUser = 5;

        foreach ($users as $user) {
            for ($i = 0; $i < $positionsPerUser; $i++) {
                $district = fake()->randomElement($districts);

                Location::create([
                    'user_id' => $user->id,
                    'latitude' => fake()->randomFloat(6, 14.62, 14.80),
                    'longitude' => fake()->randomFloat(6, -17.55, -17.30),
                    'address' => $district . ', Dakar, Senegal',
                    'captured_at' => now()->subMinutes(fake()->numberBetween(5, 1440)),
                ]);
            }
        }
    }
}
