<?php

namespace Database\Seeders;

use App\Models\DoctorDocument;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DoctorDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = User::where('role', 'doctor')->get();

        if ($doctors->isEmpty()) {
            return;
        }

        $adminId = User::where('role', 'admin')->value('id');
        $types = ['license', 'diploma', 'certificate'];

        foreach ($doctors as $doctor) {
            if (DoctorDocument::where('doctor_id', $doctor->id)->exists()) {
                continue;
            }

            $profile = DoctorProfile::where('user_id', $doctor->id)->first();
            $isApproved = $profile && $profile->verification_status === 'approved';

            foreach ($types as $type) {
                $path = "doctor-documents/{$doctor->id}/{$type}.pdf";
                Storage::disk('public')->put($path, 'Seeded document');

                DoctorDocument::create([
                    'doctor_id' => $doctor->id,
                    'type' => $type,
                    'file_path' => $path,
                    'status' => $isApproved ? 'approved' : 'pending',
                    'reviewed_by' => $isApproved ? $adminId : null,
                    'reviewed_at' => $isApproved ? now()->subDays(fake()->numberBetween(1, 30)) : null,
                    'notes' => $isApproved ? 'Exemple approuve automatiquement.' : null,
                ]);
            }
        }
    }
}
