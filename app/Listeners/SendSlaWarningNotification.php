<?php

namespace App\Listeners;

use App\Events\SlaWarning;
use App\Models\User;
use App\Services\NotificationService;

class SendSlaWarningNotification
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(SlaWarning $event): void
    {
        $serviceRequest = $event->serviceRequest;
        $driver = $serviceRequest->driver;

        // Notify the assigned driver
        if ($driver) {
            $this->notificationService->send(
                user: $driver,
                title: 'Alerte SLA - Demande urgente',
                body: "La demande #{$serviceRequest->id} approche de l'echeance SLA ({$event->percentElapsed}% ecoule).",
                data: [
                    'type' => 'sla_warning',
                    'service_request_id' => $serviceRequest->id,
                    'percent_elapsed' => $event->percentElapsed,
                ]
            );
        }

        // Notify admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            $this->notificationService->send(
                user: $admin,
                title: 'Alerte SLA - Attention requise',
                body: "La demande #{$serviceRequest->id} approche de l'echeance ({$event->percentElapsed}%).",
                data: [
                    'type' => 'sla_warning',
                    'service_request_id' => $serviceRequest->id,
                    'percent_elapsed' => $event->percentElapsed,
                ]
            );
        }
    }
}
