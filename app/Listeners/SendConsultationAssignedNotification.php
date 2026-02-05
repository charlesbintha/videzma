<?php

namespace App\Listeners;

use App\Events\ConsultationAssigned;
use App\Services\NotificationService;

class SendConsultationAssignedNotification
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(ConsultationAssigned $event): void
    {
        $consultation = $event->consultation;
        $doctor = $consultation->doctor;

        if (!$doctor) {
            return;
        }

        $patient = $consultation->patient;
        $patientName = $patient?->name ?? 'Un patient';

        $this->notificationService->send(
            user: $doctor,
            title: 'Nouvelle demande de consultation',
            body: "{$patientName} a besoin d'une consultation. Motif: {$consultation->reason}",
            data: [
                'type' => 'consultation_assigned',
                'consultation_id' => $consultation->id,
            ]
        );
    }
}
