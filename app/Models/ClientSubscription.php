<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    use \App\Models\Concerns\RecordsPayments, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'client_id',
        'plan_id',
        'price_amount',
        'payment_cycle',
        'status',
        'current_period_start',
        'current_period_end',
        'interventions_used',
        'volume_used',
        'payment_method',
        'payment_status',
        'payment_token',
        'payment_reference',
        'paid_at',
        'auto_renew',
        'cancelled_at',
        'paused_at',
    ];

    protected $casts = [
        'price_amount' => 'integer',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'interventions_used' => 'integer',
        'volume_used' => 'decimal:2',
        'paid_at' => 'datetime',
        'auto_renew' => 'boolean',
        'cancelled_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription) {
            $subscription->payment_cycle ??= (string) \Illuminate\Support\Str::uuid();
            $subscription->price_amount ??= SubscriptionPlan::findOrFail($subscription->plan_id)->price;
        });
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_amount ?? 0, 0, ',', ' ').' FCFA';
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->current_period_end >= now()->toDateString();
    }

    public function isExpired(): bool
    {
        return $this->current_period_end < now()->toDateString();
    }

    public function getRemainingInterventionsAttribute(): int
    {
        $max = $this->plan?->interventions_per_period ?? 0;

        return max(0, $max - $this->interventions_used);
    }

    public function getRemainingDaysAttribute(): int
    {
        if ($this->current_period_end < now()) {
            return 0;
        }

        return now()->diffInDays($this->current_period_end);
    }

    public function canUseIntervention(): bool
    {
        return $this->isActive() && $this->remaining_interventions > 0;
    }

    public function useIntervention(float $volume): void
    {
        $this->increment('interventions_used');
        $this->increment('volume_used', $volume);
    }

    public function renew(): void
    {
        $days = $this->plan->period_days;
        $this->update([
            'current_period_start' => now()->toDateString(),
            'current_period_end' => now()->addDays($days)->toDateString(),
            'interventions_used' => 0,
            'volume_used' => 0,
            'status' => self::STATUS_ACTIVE,
            'payment_status' => 'pending',
            'price_amount' => $this->plan->price,
            'payment_cycle' => (string) \Illuminate\Support\Str::uuid(),
            'paid_at' => null,
            'payment_reference' => null,
            'payment_token' => null,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('current_period_end', '>=', now()->toDateString());
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
