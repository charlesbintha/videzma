<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'vehicle_type',
        'vehicle_plate',
        'tank_capacity',
        'zone_coverage',
        'verification_status',
        'verified_at',
        'bio',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'tank_capacity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id', 'user_id');
    }
}
