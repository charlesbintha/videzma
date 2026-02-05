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
        $consultation = $event->consultation;
        $doctor = $consultation->doctor;

        // Notify the assigned doctor
        if ($doctor) {
            $this->notificationService->send(
                user: $doctor,
                title: 'Alerte SLA - Demande urgente',
                body: "La demande #{$consultation->id} approche de l'echeance SLA ({$event->percentElapsed}% ecoule).",
                data: [
                    'type' => 'sla_warning',
                    'consultation_id' => $consultation->id,
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
                body: "La demande #{$consultation->id} approche de l'echeance ({$event->percentElapsed}%).",
                data: [
                    'type' => 'sla_warning',
                    'consultation_id' => $consultation->id,
                    'percent_elapsed' => $event->percentElapsed,
                ]
            );
        }
    }
}
