<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('locations');

            // Adresse et détails du service
            $table->text('address');
            $table->string('fosse_type')->nullable(); // traditionnelle, septique, etc.
            $table->decimal('estimated_volume', 8, 2)->nullable(); // volume estimé en m³
            $table->decimal('actual_volume', 8, 2)->nullable(); // volume réel en m³
            $table->string('urgency_level')->default('normal'); // normal, urgent, emergency

            // Distance et tarification
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('price_amount')->nullable(); // prix en FCFA
            $table->string('payment_method')->nullable(); // cash, mobile_money, card
            $table->string('payment_status')->default('pending'); // pending, paid, failed
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Statut et cycle de vie
            $table->string('status')->default('pending'); // pending, assigned, accepted, rejected, in_progress, completed, canceled
            $table->text('notes')->nullable();
            $table->text('client_notes')->nullable();
            $table->text('driver_notes')->nullable();

            // Timestamps du cycle de vie
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // SLA
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('sla_warning_sent_at')->nullable();
            $table->timestamp('sla_breach_sent_at')->nullable();

            // Navigation
            $table->timestamp('navigation_started_at')->nullable();
            $table->timestamp('navigation_ended_at')->nullable();

            // Photos et évaluation
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->integer('rating')->nullable(); // 1-5
            $table->text('rating_comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
