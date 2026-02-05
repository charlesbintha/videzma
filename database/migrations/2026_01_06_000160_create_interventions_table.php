<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, canceled
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->decimal('actual_volume', 8, 2)->nullable();
            $table->text('driver_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
