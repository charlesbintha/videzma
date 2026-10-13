<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'address',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function scopeNewest($query)
    {
        return $query->orderByRaw('COALESCE(captured_at, created_at) DESC')->orderByDesc('id');
    }

    public function scopeSinceNavigation($query, ServiceRequest $service)
    {
        return $query->whereRaw('COALESCE(captured_at, created_at) >= ?', [$service->navigation_started_at]);
    }

    public function recordedAt()
    {
        return $this->captured_at ?? $this->created_at;
    }

    public function isFresh(): bool
    {
        $time = $this->recordedAt();

        return $time !== null && $time->between(now()->subMinutes(2), now()->addSeconds(30));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
