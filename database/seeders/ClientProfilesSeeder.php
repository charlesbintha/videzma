<?php

namespace Database\Seeders;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $clients = User::where('role', 'client')->get();

        if ($clients->isEmpty()) {
            return;
        }

        $neighborhoods = ['Medina', 'Plateau', 'Grand Dakar', 'Parcelles Assainies', 'Pikine', 'Guediawaye', 'Almadies', 'Point E', 'Mermoz'];

        foreach ($clients as $client) {
            ClientProfile::updateOrCreate(
                ['user_id' => $client->id],
                [
                    'birthdate' => fake()->dateTimeBetween('-70 years', '-18 years'),
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'address' => fake()->address(),
                    'city' => 'Dakar',
                    'neighborhood' => fake()->randomElement($neighborhoods),
                ]
            );
        }
    }
}
