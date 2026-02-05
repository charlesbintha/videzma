<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'sla_config_id',
        'name',
        'condition',
        'action',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function config()
    {
        return $this->belongsTo(SlaConfig::class, 'sla_config_id');
    }
}
