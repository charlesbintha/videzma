<?php

namespace App\Providers;

use App\Events\SlaBreach;
use App\Events\SlaWarning;
use App\Listeners\SendSlaBreachNotification;
use App\Listeners\SendSlaWarningNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
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
