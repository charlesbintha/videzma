<?php

namespace Database\Seeders;

use App\Models\Intervention;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InterventionsSeeder extends Seeder
{
    public function run(): void
    {
        if (Intervention::count() > 0) {
            return;
        }

        // Créer des interventions pour les demandes en cours ou terminées
        $requests = ServiceRequest::whereIn('status', ['in_progress', 'completed'])->get();

        foreach ($requests as $request) {
            $scheduledAt = $request->accepted_at ?? $request->assigned_at ?? $request->requested_at;
            $status = $request->status === 'completed' ? 'completed' : 'in_progress';

            $data = [
                'service_request_id' => $request->id,
                'scheduled_at' => Carbon::instance($scheduledAt)->addHours(fake()->numberBetween(1, 4)),
                'status' => $status,
            ];

            if ($status === 'in_progress' || $status === 'completed') {
                $data['started_at'] = $request->started_at;
                $data['driver_notes'] = fake()->boolean(50) ? fake()->sentence(6) : null;
            }

            if ($status === 'completed') {
                $data['completed_at'] = $request->completed_at;
                $data['ended_at'] = $request->completed_at;
                $data['actual_volume'] = $request->actual_volume;
                $data['duration_minutes'] = fake()->numberBetween(30, 120);
                $data['photo_before'] = fake()->boolean(60) ? "interventions/{$request->id}/before.jpg" : null;
                $data['photo_after'] = fake()->boolean(60) ? "interventions/{$request->id}/after.jpg" : null;
            }

            Intervention::create($data);
        }
    }
}
