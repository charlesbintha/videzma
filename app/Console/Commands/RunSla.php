<?php

namespace App\Console\Commands;

use App\Events\SlaBreach;
use App\Events\SlaWarning;
use App\Models\AuditAction;
use App\Models\ServiceRequest;
use App\Models\SlaConfig;
use Illuminate\Console\Command;

class RunSla extends Command
{
    protected $signature = 'sla:run';

    protected $description = 'Apply SLA rules to service requests';

    public function handle(): int
    {
        $config = SlaConfig::where('is_active', true)->orderByDesc('created_at')->first();

        if (! $config) {
            $this->info('No active SLA config found.');

            return self::SUCCESS;
        }

        $now = now();
        $expirationMinutes = $config->expiration_minutes;
        $warningThreshold = 75; // Send warning at 75%

        $expiredCount = 0;
        $warningCount = 0;

        $candidates = ServiceRequest::whereIn('status', ['pending', 'assigned'])->get();

        foreach ($candidates as $serviceRequest) {
            $dueAt = $serviceRequest->sla_due_at;
            $requestedAt = $serviceRequest->requested_at ?? $serviceRequest->created_at;

            if (! $dueAt) {
                $dueAt = $requestedAt->copy()->addMinutes($expirationMinutes);
            }

            $totalMinutes = $requestedAt->diffInMinutes($dueAt);
            $elapsedMinutes = $requestedAt->diffInMinutes($now);
            $percentElapsed = $totalMinutes > 0 ? ($elapsedMinutes / $totalMinutes) * 100 : 100;

            // Check for SLA breach (100% elapsed)
            if ($dueAt->lte($now) && ! $serviceRequest->sla_breach_sent_at) {
                // Only dispatch breach event if not already sent
                if (! $serviceRequest->sla_breach_sent_at) {
                    event(new SlaBreach($serviceRequest));
                    $serviceRequest->sla_breach_sent_at = $now;
                }

                $serviceRequest->save();

                AuditAction::create([
                    'actor_id' => null,
                    'action' => 'sla_breach',
                    'target_type' => ServiceRequest::class,
                    'target_id' => $serviceRequest->id,
                    'metadata' => ['due_at' => $dueAt->toDateTimeString()],
                    'ip_address' => null,
                ]);

                $expiredCount++;
            }
            // Check for SLA warning (75% elapsed)
            elseif ($dueAt->gt($now) && $percentElapsed >= $warningThreshold && ! $serviceRequest->sla_warning_sent_at) {
                event(new SlaWarning($serviceRequest, (int) $percentElapsed));

                $serviceRequest->update([
                    'sla_warning_sent_at' => $now,
                ]);

                $warningCount++;
            }
        }

        $this->info("Overdue {$expiredCount} request(s).");
        $this->info("Sent {$warningCount} warning(s).");

        return self::SUCCESS;
    }
}
