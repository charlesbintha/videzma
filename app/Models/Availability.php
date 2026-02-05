<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'starts_at',
        'ends_at',
        'is_booked',
        'note',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_booked' => 'boolean',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
