<?php

namespace Database\Seeders;

use App\Models\ConsultationRequest;
use App\Models\Itinerary;
use App\Models\NavigationSession;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        if (NavigationSession::count() > 0) {
            return;
        }

        $consultations = ConsultationRequest::whereNotNull('doctor_id')->get();

        foreach ($consultations->take(10) as $consultation) {
            $session = NavigationSession::create([
                'doctor_id' => $consultation->doctor_id,
                'consultation_request_id' => $consultation->id,
                'status' => fake()->randomElement(['active', 'completed']),
                'started_at' => now()->subMinutes(fake()->numberBetween(10, 120)),
                'ended_at' => fake()->boolean(40) ? now()->subMinutes(fake()->numberBetween(1, 9)) : null,
            ]);

            Itinerary::create([
                'navigation_session_id' => $session->id,
                'distance_km' => fake()->randomFloat(2, 1, 25),
                'eta_minutes' => fake()->numberBetween(5, 60),
                'route_data' => [
                    'provider' => 'sample',
                    'points' => [],
                ],
            ]);
        }
    }
}
