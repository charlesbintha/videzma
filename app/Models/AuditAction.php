<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'action',
        'target_type',
        'target_id',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function target()
    {
        return $this->morphTo();
    }
}
