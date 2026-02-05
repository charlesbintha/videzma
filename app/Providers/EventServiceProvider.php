<?php

namespace App\Providers;

use App\Events\ConsultationAccepted;
use App\Events\ConsultationAssigned;
use App\Events\ConsultationRejected;
use App\Events\SlaBreach;
use App\Events\SlaWarning;
use App\Listeners\SendConsultationAcceptedNotification;
use App\Listeners\SendConsultationAssignedNotification;
use App\Listeners\SendSlaBreachNotification;
use App\Listeners\SendSlaWarningNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ConsultationAssigned::class => [
            SendConsultationAssignedNotification::class,
        ],
        ConsultationAccepted::class => [
            SendConsultationAcceptedNotification::class,
        ],
        ConsultationRejected::class => [
            // Future: SendConsultationRejectedNotification
        ],
        SlaWarning::class => [
            SendSlaWarningNotification::class,
        ],
        SlaBreach::class => [
            SendSlaBreachNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
