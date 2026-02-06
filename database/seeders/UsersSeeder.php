<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        if (User::whereIn('role', ['client', 'driver'])->exists()) {
            return;
        }

        // Créer des clients
        User::factory()
            ->count(20)
            ->state(fn () => [
                'role' => 'client',
                'status' => 'active',
                'phone' => fake()->phoneNumber(),
                'locale' => 'fr',
            ])
            ->create();

        // Créer des vidangeurs
        User::factory()
            ->count(8)
            ->state(fn () => [
                'role' => 'driver',
                'status' => 'active',
                'phone' => fake()->phoneNumber(),
                'locale' => 'fr',
            ])
            ->create();
    }
}
