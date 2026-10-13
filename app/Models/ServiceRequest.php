<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use \App\Models\Concerns\RecordsPayments, HasFactory;

    public const STATUS_LABELS = ['pending' => 'En attente', 'assigned' => 'Assignée', 'accepted' => 'Acceptée', 'in_progress' => 'En cours', 'completed' => 'Terminée', 'rejected' => 'Refusée', 'canceled' => 'Annulée', 'cancelled' => 'Annulée'];

    public const ACTIVE_STATUSES = ['pending', 'assigned', 'accepted', 'in_progress'];

    public const TRACKING_STATUSES = ['accepted', 'in_progress'];

    protected static function booted(): void
    {
        static::saving(function (self $service) {
            if ($service->navigation_started_at &&
                (! in_array($service->status, self::TRACKING_STATUSES, true) ||
                 ($service->exists && $service->isDirty('driver_id')))) {
                $service->navigation_ended_at = $service->navigation_ended_at ?? now();
            }
        });
    }

    public function scopeTrackingActive($query)
    {
        return $query->whereIn('status', self::TRACKING_STATUSES)
            ->whereNotNull('driver_id')->whereNotNull('navigation_started_at')->whereNull('navigation_ended_at');
    }

    public function isTrackingActive(): bool
    {
        return $this->driver_id !== null && in_array($this->status, self::TRACKING_STATUSES, true)
            && $this->navigation_started_at !== null && $this->navigation_ended_at === null;
    }

    // Prix de base en FCFA
    public const BASE_PRICE = 15000;

    // Prix par m³ supplémentaire
    public const PRICE_PER_M3 = 5000;

    protected $fillable = [
        'client_id',
        'driver_id',
        'location_id',
        'address',
        'fosse_type',
        'estimated_volume',
        'actual_volume',
        'urgency_level',
        'distance_km',
        'price_amount',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_token',
        'paid_at',
        'status',
        'notes',
        'client_notes',
        'driver_notes',
        'requested_at',
        'assigned_at',
        'accepted_at',
        'rejected_at',
        'canceled_at',
        'started_at',
        'completed_at',
        'sla_due_at',
        'sla_warning_sent_at',
        'sla_breach_sent_at',
        'navigation_started_at',
        'navigation_ended_at',
        'photo_before',
        'photo_after',
        'rating',
        'rating_comment',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'estimated_volume' => 'decimal:2',
        'actual_volume' => 'decimal:2',
        'price_amount' => 'integer',
        'rating' => 'integer',
        'paid_at' => 'datetime',
        'requested_at' => 'datetime',
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'canceled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'sla_warning_sent_at' => 'datetime',
        'sla_breach_sent_at' => 'datetime',
        'navigation_started_at' => 'datetime',
        'navigation_ended_at' => 'datetime',
    ];

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_amount ?? 0, 0, ',', ' ').' FCFA';
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function intervention()
    {
        return $this->hasOne(Intervention::class);
    }

    public function auditActions()
    {
        return $this->morphMany(AuditAction::class, 'target');
    }

    public function comments()
    {
        return $this->hasMany(ServiceComment::class);
    }
}
