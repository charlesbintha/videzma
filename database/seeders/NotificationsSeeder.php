<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        if (Notification::count() > 0) {
            return;
        }

        $users = User::whereIn('role', ['client', 'driver'])->get();

        if ($users->isEmpty()) {
            return;
        }

        $channels = ['email', 'push', 'sms'];
        $statuses = ['queued', 'sent', 'failed'];
        $titles = ['Mise a jour du service', 'Rappel de intervention', 'Compte verifie', 'Alerte SLA'];
        $bodies = [
            'Votre service a ete mis a jour.',
            'Un rappel a ete envoye pour votre intervention.',
            'Votre compte a ete verifie par l administration.',
            'Une demande approche de l echeance SLA.',
        ];

        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $status = fake()->randomElement($statuses);

            Notification::create([
                'user_id' => $user->id,
                'channel' => fake()->randomElement($channels),
                'title' => fake()->randomElement($titles),
                'body' => fake()->randomElement($bodies),
                'status' => $status,
                'sent_at' => $status === 'sent' ? now()->subMinutes(fake()->numberBetween(5, 500)) : null,
            ]);
        }
    }
}
