<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'validation_minutes',
        'expiration_minutes',
        'cancellation_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rules()
    {
        return $this->hasMany(SlaRule::class);
    }
}
