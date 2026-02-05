<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        if (User::whereIn('role', ['patient', 'doctor'])->exists()) {
            return;
        }

        User::factory()
            ->count(20)
            ->state(fn () => [
                'role' => 'patient',
                'status' => 'active',
                'phone' => fake()->phoneNumber(),
                'locale' => 'fr',
            ])
            ->create();

        User::factory()
            ->count(8)
            ->state(fn () => [
                'role' => 'doctor',
                'status' => 'active',
                'phone' => fake()->phoneNumber(),
                'locale' => 'fr',
            ])
            ->create();
    }
}
