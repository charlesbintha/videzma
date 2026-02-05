<?php

namespace Database\Seeders;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationPreferencesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $channels = ['email', 'push', 'sms'];

        foreach ($users as $user) {
            foreach ($channels as $channel) {
                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'channel' => $channel],
                    [
                        'is_enabled' => true,
                        'locale' => $user->locale,
                    ]
                );
            }
        }
    }
}
