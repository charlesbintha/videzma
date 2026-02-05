<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20);
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
