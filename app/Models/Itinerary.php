<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'navigation_session_id',
        'distance_km',
        'eta_minutes',
        'route_data',
    ];

    protected $casts = [
        'route_data' => 'array',
    ];

    public function navigationSession()
    {
        return $this->belongsTo(NavigationSession::class);
    }
}
