<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'scheduled_at',
        'status',
        'started_at',
        'ended_at',
        'canceled_at',
        'completed_at',
        'duration_minutes',
        'photo_before',
        'photo_after',
        'actual_volume',
        'driver_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'canceled_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_minutes' => 'integer',
        'actual_volume' => 'decimal:2',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
