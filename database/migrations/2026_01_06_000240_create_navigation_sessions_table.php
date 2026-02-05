<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_sessions');
    }
};
