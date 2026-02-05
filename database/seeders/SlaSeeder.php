<?php

namespace Database\Seeders;

use App\Models\SlaConfig;
use App\Models\SlaRule;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        $config = SlaConfig::firstOrCreate(
            ['name' => 'Par defaut'],
            [
                'description' => 'Regles SLA par defaut',
                'validation_minutes' => 30,
                'expiration_minutes' => 60,
                'cancellation_minutes' => 60,
                'is_active' => true,
            ]
        );

        if (!SlaRule::where('sla_config_id', $config->id)->exists()) {
            SlaRule::create([
                'sla_config_id' => $config->id,
                'name' => 'Expiration automatique',
                'condition' => 'statut=en attente et delai>expiration',
                'action' => 'marquer_expire',
                'is_active' => true,
            ]);

            SlaRule::create([
                'sla_config_id' => $config->id,
                'name' => 'Alerte avant echeance',
                'condition' => 'delai>validation-5',
                'action' => 'notifier_admin',
                'is_active' => true,
            ]);
        }
    }
}
