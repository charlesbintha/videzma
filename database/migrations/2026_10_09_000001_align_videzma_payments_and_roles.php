<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // SQLite rebuilds the users table to change a default. Disable FK actions
        // outside a transaction so that rebuilding cannot cascade-delete child rows.
        Schema::withoutForeignKeyConstraints(function () {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('client')->change();
            });
        });
        // Existing medical accounts are not automatically granted Videzma access.
        Schema::table('client_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('price_amount')->nullable();
            $table->uuid('payment_cycle')->nullable();
        });
        DB::table('client_subscriptions')->orderBy('id')->chunkById(200, function ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                DB::table('client_subscriptions')->where('id', $subscription->id)->update([
                    'payment_cycle' => (string) \Illuminate\Support\Str::uuid(),
                    'price_amount' => DB::table('subscription_plans')->where('id', $subscription->plan_id)->value('price') ?? 0,
                ]);
            }
        });
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_key')->unique();
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->unsignedInteger('amount');
            $table->timestamp('paid_at')->index();
            $table->string('source');
            $table->timestamps();
            $table->index(['payable_type', 'payable_id']);
        });
        foreach (['service_requests', 'client_subscriptions'] as $table) {
            DB::table($table)->where('payment_status', 'paid')->whereNotNull('paid_at')->orderBy('id')->chunkById(200, function ($payments) use ($table) {
                foreach ($payments as $payment) {
                    DB::table('payment_receipts')->insertOrIgnore([
                        'receipt_key' => $table.':'.$payment->id.':'.($payment->payment_cycle ?? 'once'),
                        'payable_type' => $table,
                        'payable_id' => $payment->id,
                        'amount' => $payment->price_amount ?? 0,
                        'paid_at' => $payment->paid_at,
                        // Old subscription amounts cannot be recovered from this database.
                        'source' => $table === 'client_subscriptions' ? 'legacy_estimate' : 'legacy',
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
        Schema::table('client_subscriptions', fn (Blueprint $table) => $table->dropColumn(['price_amount', 'payment_cycle']));
        Schema::withoutForeignKeyConstraints(function () {
            Schema::table('users', fn (Blueprint $table) => $table->string('role', 20)->default('patient')->change());
        });
    }
};
