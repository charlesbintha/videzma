<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceipt extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime', 'amount' => 'integer'];
}
