<?php

namespace App\Listeners;

use App\Events\ConsultationAccepted;
use App\Services\NotificationService;

class SendConsultationAcceptedNotification
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(ConsultationAccepted $event): void
    {
        $consultation = $event->consultation;
        $patient = $consultation->patient;

        if (!$patient) {
            return;
        }

        $doctor = $consultation->doctor;
        $doctorName = $doctor?->name ?? 'Un medecin';

        $this->notificationService->send(
            user: $patient,
            title: 'Demande acceptee',
            body: "Dr. {$doctorName} a accepte votre demande de consultation.",
            data: [
                'type' => 'consultation_accepted',
                'consultation_id' => $consultation->id,
            ]
        );
    }
}
