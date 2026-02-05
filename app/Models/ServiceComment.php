<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'author_id',
        'content',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
