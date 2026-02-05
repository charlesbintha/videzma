<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavigationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'service_request_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }
}
