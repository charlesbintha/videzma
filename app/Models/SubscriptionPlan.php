<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    // Périodicités disponibles
    public const PERIODICITY_WEEKLY = 'weekly';
    public const PERIODICITY_BIWEEKLY = 'biweekly';
    public const PERIODICITY_MONTHLY = 'monthly';
    public const PERIODICITY_QUARTERLY = 'quarterly';
    public const PERIODICITY_YEARLY = 'yearly';

    public const PERIODICITIES = [
        self::PERIODICITY_WEEKLY => 'Hebdomadaire',
        self::PERIODICITY_BIWEEKLY => 'Bi-mensuel',
        self::PERIODICITY_MONTHLY => 'Mensuel',
        self::PERIODICITY_QUARTERLY => 'Trimestriel',
        self::PERIODICITY_YEARLY => 'Annuel',
    ];

    // Durées en jours pour chaque périodicité
    public const PERIODICITY_DAYS = [
        self::PERIODICITY_WEEKLY => 7,
        self::PERIODICITY_BIWEEKLY => 14,
        self::PERIODICITY_MONTHLY => 30,
        self::PERIODICITY_QUARTERLY => 90,
        self::PERIODICITY_YEARLY => 365,
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'periodicity',
        'interventions_per_period',
        'max_volume_per_intervention',
        'price',
        'extra_volume_price',
        'discount_percent',
        'display_order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'interventions_per_period' => 'integer',
        'max_volume_per_intervention' => 'decimal:2',
        'price' => 'integer',
        'extra_volume_price' => 'integer',
        'discount_percent' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(ClientSubscription::class, 'plan_id');
    }

    public function getPeriodicityLabelAttribute(): string
    {
        return self::PERIODICITIES[$this->periodicity] ?? $this->periodicity;
    }

    public function getPeriodDaysAttribute(): int
    {
        return self::PERIODICITY_DAYS[$this->periodicity] ?? 30;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('price');
    }
}
