<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@wadnou.test');
        $name = env('ADMIN_NAME', 'Admin');
        $password = env('ADMIN_PASSWORD', 'admin1234');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'locale' => 'fr',
                'email_verified_at' => now(),
            ]
        );
    }
}
