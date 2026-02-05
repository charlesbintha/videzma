<?php

namespace Database\Seeders;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $patients = User::where('role', 'patient')->get();

        if ($patients->isEmpty()) {
            return;
        }

        foreach ($patients as $patient) {
            PatientProfile::updateOrCreate(
                ['user_id' => $patient->id],
                [
                    'birthdate' => fake()->dateTimeBetween('-65 years', '-18 years'),
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'address' => fake()->address(),
                ]
            );
        }
    }
}
