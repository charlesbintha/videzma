<?php

namespace Database\Seeders;

use App\Models\DriverDocument;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DriverDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = User::where('role', 'driver')->get();

        if ($drivers->isEmpty()) {
            return;
        }

        $adminId = User::where('role', 'admin')->value('id');
        $types = ['license', 'vehicle_registration', 'insurance', 'certificate'];

        foreach ($drivers as $driver) {
            if (DriverDocument::where('driver_id', $driver->id)->exists()) {
                continue;
            }

            $profile = DriverProfile::where('user_id', $driver->id)->first();
            $isApproved = $profile && $profile->verification_status === 'approved';

            foreach ($types as $type) {
                $path = "driver-documents/{$driver->id}/{$type}.pdf";
                Storage::disk('public')->put($path, 'Seeded document');

                DriverDocument::create([
                    'driver_id' => $driver->id,
                    'type' => $type,
                    'file_path' => $path,
                    'status' => $isApproved ? 'approved' : 'pending',
                    'reviewed_by' => $isApproved ? $adminId : null,
                    'reviewed_at' => $isApproved ? now()->subDays(fake()->numberBetween(1, 30)) : null,
                    'notes' => $isApproved ? 'Document approuve automatiquement.' : null,
                ]);
            }
        }
    }
}
