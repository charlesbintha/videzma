<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'type',
        'file_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
