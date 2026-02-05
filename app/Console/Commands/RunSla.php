<?php

namespace App\Console\Commands;

use App\Events\SlaBreach;
use App\Events\SlaWarning;
use App\Models\AuditAction;
use App\Models\ConsultationRequest;
use App\Models\SlaConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RunSla extends Command
{
    protected $signature = 'sla:run';

    protected $description = 'Apply SLA rules to consultation requests';

    public function handle(): int
    {
        $config = SlaConfig::where('is_active', true)->orderByDesc('created_at')->first();

        if (!$config) {
            $this->info('No active SLA config found.');
            return self::SUCCESS;
        }

        $now = now();
        $expirationMinutes = $config->expiration_minutes;
        $warningThreshold = 75; // Send warning at 75%

        $expiredCount = 0;
        $warningCount = 0;

        $candidates = ConsultationRequest::whereIn('status', ['pending', 'assigned'])->get();

        foreach ($candidates as $consultation) {
            $dueAt = $consultation->sla_due_at;
            $requestedAt = Carbon::parse($consultation->requested_at);

            if (!$dueAt) {
                $dueAt = $requestedAt->copy()->addMinutes($expirationMinutes);
            }

            $totalMinutes = $requestedAt->diffInMinutes($dueAt);
            $elapsedMinutes = $requestedAt->diffInMinutes($now);
            $percentElapsed = $totalMinutes > 0 ? ($elapsedMinutes / $totalMinutes) * 100 : 100;

            // Check for SLA breach (100% elapsed)
            if ($dueAt->lte($now)) {
                // Only dispatch breach event if not already sent
                if (!$consultation->sla_breach_sent_at) {
                    event(new SlaBreach($consultation));
                    $consultation->sla_breach_sent_at = $now;
                }

                $consultation->status = 'expired';
                $consultation->closed_at = $consultation->closed_at ?? $now;
                $consultation->save();

                AuditAction::create([
                    'actor_id' => null,
                    'action' => 'sla_expired',
                    'target_type' => ConsultationRequest::class,
                    'target_id' => $consultation->id,
                    'metadata' => ['due_at' => $dueAt->toDateTimeString()],
                    'ip_address' => null,
                ]);

                $expiredCount++;
            }
            // Check for SLA warning (75% elapsed)
            elseif ($percentElapsed >= $warningThreshold && !$consultation->sla_warning_sent_at) {
                event(new SlaWarning($consultation, (int) $percentElapsed));

                $consultation->update([
                    'sla_warning_sent_at' => $now,
                ]);

                $warningCount++;
            }
        }

        $this->info("Expired {$expiredCount} request(s).");
        $this->info("Sent {$warningCount} warning(s).");

        return self::SUCCESS;
    }
}
