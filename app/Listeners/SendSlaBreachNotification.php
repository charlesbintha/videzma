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
        $consultation = $event->consultation;
        $doctor = $consultation->doctor;

        // Notify the assigned doctor
        if ($doctor) {
            $this->notificationService->send(
                user: $doctor,
                title: 'SLA depasse - Action immediate requise',
                body: "La demande #{$consultation->id} a depasse l'echeance SLA.",
                data: [
                    'type' => 'sla_breach',
                    'consultation_id' => $consultation->id,
                ]
            );
        }

        // Notify admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            $this->notificationService->send(
                user: $admin,
                title: 'SLA depasse - Demande expiree',
                body: "La demande #{$consultation->id} a depasse l'echeance SLA et a ete marquee comme expiree.",
                data: [
                    'type' => 'sla_breach',
                    'consultation_id' => $consultation->id,
                ]
            );
        }
    }
}
