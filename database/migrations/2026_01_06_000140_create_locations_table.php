<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
