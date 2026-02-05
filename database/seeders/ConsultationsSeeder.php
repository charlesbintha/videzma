<?php

namespace Database\Seeders;

use App\Models\ConsultationRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ConsultationsSeeder extends Seeder
{
    public function run(): void
    {
        if (ConsultationRequest::count() > 0) {
            return;
        }

        $patients = User::where('role', 'patient')->get();

        if ($patients->isEmpty()) {
            return;
        }

        $doctors = User::where('role', 'doctor')->get();
        $reasons = ['Consultation generale', 'Suivi', 'Urgence', 'Prescription', 'Visite a domicile'];

        for ($i = 0; $i < 30; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->isNotEmpty() && fake()->boolean(70) ? $doctors->random() : null;
            $requestedAt = fake()->dateTimeBetween('-10 days', 'now');
            $status = $doctor ? fake()->randomElement(['assigned', 'accepted', 'rejected', 'closed']) : 'pending';

            $data = [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor?->id,
                'location_id' => Location::where('user_id', $patient->id)->inRandomOrder()->value('id'),
                'status' => $status,
                'reason' => fake()->randomElement($reasons),
                'notes' => fake()->sentence(8),
                'requested_at' => $requestedAt,
                'sla_due_at' => Carbon::instance($requestedAt)->addMinutes(60),
            ];

            if ($status === 'accepted') {
                $data['accepted_at'] = Carbon::instance($requestedAt)->addMinutes(15);
            }

            if ($status === 'rejected') {
                $data['rejected_at'] = Carbon::instance($requestedAt)->addMinutes(10);
            }

            if ($status === 'closed') {
                $data['accepted_at'] = Carbon::instance($requestedAt)->addMinutes(12);
                $data['closed_at'] = Carbon::instance($requestedAt)->addHours(2);
            }

            ConsultationRequest::create($data);
        }
    }
}
