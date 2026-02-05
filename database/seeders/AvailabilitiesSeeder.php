<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvailabilitiesSeeder extends Seeder
{
    public function run(): void
    {
        if (Availability::count() > 0) {
            return;
        }

        $doctors = User::where('role', 'doctor')->get();

        foreach ($doctors as $doctor) {
            for ($day = 1; $day <= 5; $day++) {
                $base = now()->addDays($day)->setTime(9, 0);

                for ($slot = 0; $slot < 3; $slot++) {
                    $slotStart = (clone $base)->addHours($slot * 3);
                    $slotEnd = (clone $slotStart)->addHours(2);

                    Availability::create([
                        'doctor_id' => $doctor->id,
                        'starts_at' => $slotStart,
                        'ends_at' => $slotEnd,
                        'is_booked' => false,
                        'note' => 'Seeded slot',
                    ]);
                }
            }
        }
    }
}
