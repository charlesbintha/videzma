<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ConsultationRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AppointmentsSeeder extends Seeder
{
    public function run(): void
    {
        if (Appointment::count() > 0) {
            return;
        }

        $consultations = ConsultationRequest::whereIn('status', ['assigned', 'accepted', 'closed'])->get();

        foreach ($consultations as $consultation) {
            if (!fake()->boolean(70)) {
                continue;
            }

            $scheduledAt = Carbon::parse($consultation->requested_at)->addHours(fake()->numberBetween(2, 48));
            $status = $consultation->status === 'closed'
                ? 'completed'
                : fake()->randomElement(['scheduled', 'in_progress', 'completed']);

            $data = [
                'consultation_request_id' => $consultation->id,
                'scheduled_at' => $scheduledAt,
                'status' => $status,
            ];

            if ($status === 'in_progress') {
                $data['started_at'] = $scheduledAt;
            }

            if ($status === 'completed') {
                $data['started_at'] = $scheduledAt;
                $data['completed_at'] = Carbon::instance($scheduledAt)->addMinutes(fake()->numberBetween(20, 60));
                $data['ended_at'] = $data['completed_at'];
            }

            Appointment::create($data);
        }
    }
}
