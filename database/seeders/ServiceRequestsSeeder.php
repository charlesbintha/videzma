<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ServiceRequestsSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceRequest::count() > 0) {
            return;
        }

        $clients = User::where('role', 'client')->get();

        if ($clients->isEmpty()) {
            return;
        }

        $drivers = User::where('role', 'driver')->get();
        $fosseTypes = ['traditionnelle', 'septique', 'toutes_eaux'];
        $urgencyLevels = ['normal', 'urgent', 'emergency'];
        $paymentMethods = ['cash', 'orange_money', 'wave', 'card'];

        for ($i = 0; $i < 30; $i++) {
            $client = $clients->random();
            $driver = $drivers->isNotEmpty() && fake()->boolean(70) ? $drivers->random() : null;
            $requestedAt = fake()->dateTimeBetween('-10 days', 'now');
            $estimatedVolume = fake()->randomFloat(1, 2, 15);

            // Calcul du prix: BASE_PRICE + volume * PRICE_PER_M3
            $priceAmount = ServiceRequest::BASE_PRICE + ($estimatedVolume * ServiceRequest::PRICE_PER_M3);

            // Déterminer le statut
            if (!$driver) {
                $status = 'pending';
            } else {
                $status = fake()->randomElement(['assigned', 'accepted', 'rejected', 'in_progress', 'completed']);
            }

            $data = [
                'client_id' => $client->id,
                'driver_id' => $driver?->id,
                'location_id' => Location::where('user_id', $client->id)->inRandomOrder()->value('id'),
                'status' => $status,
                'address' => fake()->address(),
                'fosse_type' => fake()->randomElement($fosseTypes),
                'estimated_volume' => $estimatedVolume,
                'urgency_level' => fake()->randomElement($urgencyLevels),
                'price_amount' => (int) $priceAmount,
                'payment_method' => fake()->randomElement($paymentMethods),
                'payment_status' => $status === 'completed' ? 'paid' : 'pending',
                'client_notes' => fake()->boolean(50) ? fake()->sentence(8) : null,
                'requested_at' => $requestedAt,
            ];

            if (in_array($status, ['assigned', 'accepted', 'in_progress', 'completed'])) {
                $data['assigned_at'] = Carbon::instance($requestedAt)->addMinutes(fake()->numberBetween(5, 30));
            }

            if (in_array($status, ['accepted', 'in_progress', 'completed'])) {
                $data['accepted_at'] = Carbon::instance($requestedAt)->addMinutes(fake()->numberBetween(10, 45));
            }

            if ($status === 'rejected') {
                $data['rejected_at'] = Carbon::instance($requestedAt)->addMinutes(fake()->numberBetween(5, 20));
            }

            if (in_array($status, ['in_progress', 'completed'])) {
                $data['started_at'] = Carbon::instance($requestedAt)->addMinutes(fake()->numberBetween(30, 90));
            }

            if ($status === 'completed') {
                $actualVolume = $estimatedVolume + fake()->randomFloat(1, -2, 3);
                $actualVolume = max(1, $actualVolume);
                $finalPrice = ServiceRequest::BASE_PRICE + ($actualVolume * ServiceRequest::PRICE_PER_M3);

                $data['actual_volume'] = $actualVolume;
                $data['price_amount'] = (int) $finalPrice;
                $data['completed_at'] = Carbon::instance($requestedAt)->addHours(fake()->numberBetween(1, 4));
                $data['paid_at'] = $data['completed_at'];
                $data['rating'] = fake()->boolean(70) ? fake()->numberBetween(3, 5) : null;
                $data['rating_comment'] = $data['rating'] ? fake()->sentence(6) : null;
            }

            ServiceRequest::create($data);
        }
    }
}
