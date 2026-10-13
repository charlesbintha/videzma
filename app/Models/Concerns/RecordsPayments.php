<?php

namespace App\Models\Concerns;

use App\Models\PaymentReceipt;

trait RecordsPayments
{
    public function save(array $options = [])
    {
        return $this->getConnection()->transaction(fn () => parent::save($options));
    }

    protected static function bootRecordsPayments(): void
    {
        static::saved(function ($model) {
            if ($model->payment_status !== 'paid' || ! $model->paid_at) {
                return;
            }

            // One receipt per payment confirmation, including manual payments.
            $key = $model->getTable().':'.$model->id.':'.($model->payment_cycle ?? 'once');
            PaymentReceipt::firstOrCreate(['receipt_key' => $key], [
                'payable_type' => $model->getTable(),
                'payable_id' => $model->id,
                'amount' => $model->price_amount ?? 0,
                'paid_at' => $model->paid_at,
                'source' => 'confirmed',
            ]);
        });
    }
}
