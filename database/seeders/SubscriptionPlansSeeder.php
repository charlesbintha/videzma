<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        if (SubscriptionPlan::count() > 0) {
            return;
        }

        $plans = [
            [
                'name' => 'Essentiel Hebdo',
                'slug' => 'essentiel-hebdo',
                'description' => 'Ideal pour les petits menages. 1 vidange par semaine.',
                'periodicity' => 'weekly',
                'interventions_per_period' => 1,
                'max_volume_per_intervention' => 5,
                'price' => 35000,
                'extra_volume_price' => 5000,
                'discount_percent' => 5,
                'display_order' => 1,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Standard Mensuel',
                'slug' => 'standard-mensuel',
                'description' => 'Le plus populaire! 2 vidanges par mois avec un volume genereux.',
                'periodicity' => 'monthly',
                'interventions_per_period' => 2,
                'max_volume_per_intervention' => 10,
                'price' => 110000,
                'extra_volume_price' => 4500,
                'discount_percent' => 15,
                'display_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Premium Mensuel',
                'slug' => 'premium-mensuel',
                'description' => 'Pour les grandes familles ou les entreprises. 4 vidanges par mois.',
                'periodicity' => 'monthly',
                'interventions_per_period' => 4,
                'max_volume_per_intervention' => 15,
                'price' => 200000,
                'extra_volume_price' => 4000,
                'discount_percent' => 20,
                'display_order' => 3,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Trimestriel Eco',
                'slug' => 'trimestriel-eco',
                'description' => 'Economisez avec un engagement trimestriel. 6 vidanges sur 3 mois.',
                'periodicity' => 'quarterly',
                'interventions_per_period' => 6,
                'max_volume_per_intervention' => 10,
                'price' => 300000,
                'extra_volume_price' => 4000,
                'discount_percent' => 25,
                'display_order' => 4,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Annuel Gold',
                'slug' => 'annuel-gold',
                'description' => 'Le meilleur rapport qualite-prix. 24 vidanges par an avec priorite de service.',
                'periodicity' => 'yearly',
                'interventions_per_period' => 24,
                'max_volume_per_intervention' => 15,
                'price' => 1000000,
                'extra_volume_price' => 3500,
                'discount_percent' => 30,
                'display_order' => 5,
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
