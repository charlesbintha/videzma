<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // license, insurance, vehicle_registration, certification
            $table->string('file_path');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};
