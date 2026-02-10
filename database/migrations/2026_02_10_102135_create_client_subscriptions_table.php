<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();

            // Statut: active, paused, cancelled, expired
            $table->string('status', 20)->default('active');

            // Dates de la période courante
            $table->date('current_period_start');
            $table->date('current_period_end');

            // Compteur d'interventions utilisées dans la période courante
            $table->unsignedInteger('interventions_used')->default(0);

            // Volume total utilisé dans la période courante
            $table->decimal('volume_used', 8, 2)->default(0);

            // Paiement
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();

            // Renouvellement automatique
            $table->boolean('auto_renew')->default(true);

            // Dates de gestion
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paused_at')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_subscriptions');
    }
};
