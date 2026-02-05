<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = User::where('role', 'doctor')->get();

        if ($doctors->isEmpty()) {
            return;
        }

        $specialties = ['Generaliste', 'Cardiologie', 'Pediatrie', 'Dermatologie', 'Neurologie', 'Orthopedie'];

        foreach ($doctors as $doctor) {
            $status = ($doctor->id % 2 === 0) ? 'approved' : 'pending';
            $verifiedAt = $status === 'approved' ? now()->subDays(fake()->numberBetween(1, 60)) : null;

            DoctorProfile::updateOrCreate(
                ['user_id' => $doctor->id],
                [
                    'specialty' => fake()->randomElement($specialties),
                    'license_number' => sprintf('LIC-%05d', $doctor->id),
                    'verification_status' => $status,
                    'verified_at' => $verifiedAt,
                    'bio' => fake()->sentence(12),
                ]
            );
        }
    }
}
