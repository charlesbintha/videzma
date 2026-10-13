<?php

namespace App\Listeners;

use App\Events\SlaBreach;
use App\Models\User;
use App\Services\NotificationService;

class SendSlaBreachNotification
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(SlaBreach $event): void
    {
        $serviceRequest = $event->serviceRequest;
        $driver = $serviceRequest->driver;

        // Notify the assigned driver
        if ($driver) {
            $this->notificationService->send(
                user: $driver,
                title: 'SLA depasse - Action immediate requise',
                body: "La demande #{$serviceRequest->id} a depasse l'echeance SLA.",
                data: [
                    'type' => 'sla_breach',
                    'service_request_id' => $serviceRequest->id,
                ]
            );
        }

        // Notify admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            $this->notificationService->send(
                user: $admin,
                title: 'SLA depasse - Demande en retard',
                body: "La demande #{$serviceRequest->id} a depasse l'echeance SLA et nécessite une prise en charge.",
                data: [
                    'type' => 'sla_breach',
                    'service_request_id' => $serviceRequest->id,
                ]
            );
        }
    }
}
