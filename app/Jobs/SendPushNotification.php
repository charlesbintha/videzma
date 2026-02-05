<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Notification $notification
    ) {}

    public function handle(): void
    {
        $user = $this->notification->user;

        if (!$user) {
            $this->notification->update(['status' => 'failed']);
            return;
        }

        $deviceTokens = $user->deviceTokens()->where('is_active', true)->get();

        if ($deviceTokens->isEmpty()) {
            $this->notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            return;
        }

        // TODO: Integrate with Firebase Cloud Messaging or other push service
        // For now, we mark the notification as sent
        // In production, you would send to FCM/APNs here

        foreach ($deviceTokens as $token) {
            Log::info('Push notification queued', [
                'user_id' => $user->id,
                'notification_id' => $this->notification->id,
                'device_token' => substr($token->token, 0, 20) . '...',
                'title' => $this->notification->title,
            ]);
        }

        $this->notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->notification->update(['status' => 'failed']);

        Log::error('Push notification failed', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
