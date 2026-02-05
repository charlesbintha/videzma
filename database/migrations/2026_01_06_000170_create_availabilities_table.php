<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_booked')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['driver_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
