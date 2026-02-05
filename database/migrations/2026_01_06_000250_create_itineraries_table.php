<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('navigation_session_id')->constrained()->cascadeOnDelete();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedInteger('eta_minutes')->nullable();
            $table->json('route_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itineraries');
    }
};
