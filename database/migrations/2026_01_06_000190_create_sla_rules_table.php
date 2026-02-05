<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_config_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('condition')->nullable();
            $table->text('action')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_rules');
    }
};
