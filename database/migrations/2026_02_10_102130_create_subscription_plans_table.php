<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();

            // Périodicité: weekly, biweekly, monthly, quarterly, yearly
            $table->string('periodicity', 20);

            // Nombre d'interventions incluses par période
            $table->unsignedInteger('interventions_per_period')->default(1);

            // Volume max inclus par intervention (en m³)
            $table->decimal('max_volume_per_intervention', 5, 2)->default(10);

            // Prix du forfait en FCFA
            $table->unsignedInteger('price');

            // Prix par m³ supplémentaire
            $table->unsignedInteger('extra_volume_price')->default(5000);

            // Remise en pourcentage par rapport au tarif normal
            $table->unsignedTinyInteger('discount_percent')->default(0);

            // Ordre d'affichage
            $table->unsignedTinyInteger('display_order')->default(0);

            // Actif ou non
            $table->boolean('is_active')->default(true);

            // Forfait recommandé/populaire
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
