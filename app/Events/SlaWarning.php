<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaWarning
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest,
        public int $percentElapsed = 75
    ) {}
}
