<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('license_number')->nullable();
            $table->string('vehicle_type')->nullable(); // camion, semi-remorque
            $table->string('vehicle_plate')->nullable();
            $table->integer('tank_capacity')->nullable(); // capacité en m³
            $table->string('zone_coverage')->nullable(); // zones desservies
            $table->string('verification_status')->default('pending'); // pending, verified, rejected
            $table->timestamp('verified_at')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
