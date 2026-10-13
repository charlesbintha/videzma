<?php

namespace Tests\Feature;

use App\Models\ClientSubscription;
use App\Models\PaymentReceipt;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VidezmaMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_upgrade_preserves_existing_rows_and_marks_estimated_receipts(): void
    {
        $migration = require database_path('migrations/2026_10_09_000001_align_videzma_payments_and_roles.php');
        $migration->down();
        // Raw inserts reproduce a database made by the previous version.
        $client = User::factory()->create();
        $plan = SubscriptionPlan::create(['name' => 'Test', 'slug' => 'test', 'price' => 25000, 'periodicity' => 'monthly', 'interventions_per_period' => 2]);
        $id = DB::table('client_subscriptions')->insertGetId([
            'client_id' => $client->id, 'plan_id' => $plan->id,
            'current_period_start' => today(), 'current_period_end' => today()->addMonth(),
            'payment_status' => 'paid', 'paid_at' => now(),
        ]);
        $migration->up();
        $this->assertDatabaseHas('users', ['id' => $client->id]);
        $this->assertDatabaseHas('client_subscriptions', ['id' => $id, 'price_amount' => 25000]);
        $this->assertDatabaseHas('payment_receipts', ['payable_id' => $id, 'source' => 'legacy_estimate', 'amount' => 25000]);
        ClientSubscription::findOrFail($id)->save();
        $this->assertSame(1, PaymentReceipt::count());
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }
}
