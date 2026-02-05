<?php

namespace App\Services;

use App\Jobs\SendPushNotification;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(
        User $user,
        string $title,
        string $body,
        array $data = [],
        string $channel = 'push'
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'status' => 'pending',
        ]);

        SendPushNotification::dispatch($notification);

        return $notification;
    }

    public function sendToMultiple(
        array $users,
        string $title,
        string $body,
        array $data = [],
        string $channel = 'push'
    ): array {
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = $this->send($user, $title, $body, $data, $channel);
        }

        return $notifications;
    }
}
