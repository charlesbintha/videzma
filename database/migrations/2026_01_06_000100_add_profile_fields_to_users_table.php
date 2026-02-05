<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('patient')->after('password');
            $table->string('status', 20)->default('pending')->after('role');
            $table->string('phone', 32)->nullable()->after('status');
            $table->string('locale', 10)->default('fr')->after('phone');
            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn(['role', 'status', 'phone', 'locale']);
        });
    }
};
