<?php

namespace App\Events;

use App\Models\ConsultationRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaWarning
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ConsultationRequest $consultation,
        public int $percentElapsed = 75
    ) {}
}
