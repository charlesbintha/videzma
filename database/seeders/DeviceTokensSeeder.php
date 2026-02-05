<?php

namespace Database\Seeders;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeviceTokensSeeder extends Seeder
{
    public function run(): void
    {
        if (DeviceToken::count() > 0) {
            return;
        }

        $users = User::whereIn('role', ['patient', 'doctor'])->get();
        $platforms = ['ios', 'android'];

        foreach ($users->take(10) as $user) {
            DeviceToken::create([
                'user_id' => $user->id,
                'token' => (string) Str::uuid(),
                'platform' => fake()->randomElement($platforms),
                'last_used_at' => now()->subMinutes(fake()->numberBetween(5, 500)),
            ]);
        }
    }
}
