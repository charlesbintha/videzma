<?php

namespace Tests\Feature;

use App\Events\SlaBreach;
use App\Models\ClientSubscription;
use App\Models\DriverProfile;
use App\Models\Location;
use App\Models\PaymentReceipt;
use App\Models\ServiceRequest;
use App\Models\SlaConfig;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Maps\GoogleMapsDirections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VidezmaRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function service(array $attributes = []): ServiceRequest
    {
        return ServiceRequest::create(array_merge([
            'client_id' => User::factory()->create()->id,
            'address' => 'Adresse de test', 'price_amount' => 15000,
            'requested_at' => now(), 'payment_method' => 'wave',
        ], $attributes));
    }

    private function subscription(): ClientSubscription
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Forfait test', 'slug' => 'test', 'price' => 25000,
            'periodicity' => 'monthly', 'interventions_per_period' => 2,
        ]);

        return ClientSubscription::create([
            'client_id' => User::factory()->create()->id, 'plan_id' => $plan->id,
            'current_period_start' => today(), 'current_period_end' => today()->addMonth(),
            'payment_method' => 'wave',
        ]);
    }

    private function ipn(ServiceRequest $service, string $event, ?string $reference = null)
    {
        config(['services.paytech.api_key' => 'test-key', 'services.paytech.api_secret' => 'test-secret']);

        return $this->postJson('/api/payments/ipn', [
            'type_event' => $event, 'ref_command' => $reference ?? $service->payment_reference,
            'api_key_sha256' => hash('sha256', 'test-key'), 'api_secret_sha256' => hash('sha256', 'test-secret'),
            'custom_field' => json_encode(['type' => 'service_request', 'service_request_id' => $service->id]),
        ]);
    }

    public function test_clients_can_only_read_their_own_locations(): void
    {
        $client = User::factory()->create();
        $other = User::factory()->create();
        $location = Location::create(['user_id' => $client->id, 'latitude' => 14.7, 'longitude' => -17.4, 'captured_at' => now()]);
        Sanctum::actingAs($client);
        $this->getJson("/api/clients/{$client->id}/locations/latest")->assertOk()->assertJsonPath('data.id', $location->id);
        $this->getJson("/api/clients/{$client->id}/locations")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/clients/{$other->id}/locations/latest")->assertForbidden();
    }

    public function test_driver_location_access_ends_when_service_is_completed(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $service = $this->service(['driver_id' => $driver->id, 'status' => 'assigned']);
        Sanctum::actingAs($driver);
        $this->getJson("/api/clients/{$service->client_id}/locations")->assertOk();
        $service->update(['status' => 'completed']);
        $this->getJson("/api/clients/{$service->client_id}/locations")->assertForbidden();
    }

    public function test_driver_route_uses_service_models_and_navigation_fields(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $service = $this->service(['driver_id' => $driver->id, 'status' => 'assigned']);
        $site = Location::create(['user_id' => $service->client_id, 'latitude' => 14.7, 'longitude' => -17.4, 'captured_at' => now()]);
        $service->update(['location_id' => $site->id]);
        $this->mock(GoogleMapsDirections::class)->shouldReceive('getFastestRoute')->once()->andReturn(['ok' => true, 'distance_km' => 2, 'eta_minutes' => 7, 'route_data' => []]);
        Sanctum::actingAs($driver);
        $this->getJson("/api/clients/{$service->client_id}/route?origin_latitude=14.6&origin_longitude=-17.3&service_request_id={$service->id}")->assertOk();
        $this->assertDatabaseHas('navigation_sessions', ['driver_id' => $driver->id, 'service_request_id' => $service->id]);
    }

    public function test_assignment_rejects_client_unapproved_and_inactive_drivers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = $this->service();
        $client = User::factory()->create();
        $driver = User::factory()->create(['role' => 'driver']);
        $this->actingAs($admin);
        foreach ([$client, $driver] as $invalid) {
            $this->post(route('admin.service-requests.assign', $service), ['driver_id' => $invalid->id])->assertSessionHasErrors('driver_id');
            $this->patch(route('admin.service-requests.update', $service), ['driver_id' => $invalid->id])->assertSessionHasErrors('driver_id');
        }
        DriverProfile::create(['user_id' => $driver->id, 'verification_status' => 'approved']);
        $driver->update(['status' => 'inactive']);
        $this->post(route('admin.service-requests.assign', $service), ['driver_id' => $driver->id])->assertSessionHasErrors('driver_id');
        $driver->update(['status' => 'active']);
        $this->post(route('admin.service-requests.assign', $service), ['driver_id' => $driver->id])->assertSessionHasNoErrors();
        $this->assertSame($driver->id, $service->fresh()->driver_id);
        $service->update(['status' => 'completed']);
        $this->post(route('admin.service-requests.assign', $service), ['driver_id' => $driver->id])->assertSessionHasErrors('driver_id');
    }

    public function test_payment_notifications_are_idempotent_and_cannot_undo_payment(): void
    {
        $service = $this->service(['payment_reference' => 'SR-test']);
        $this->ipn($service, 'sale_complete')->assertOk();
        $paidAt = $service->fresh()->paid_at;
        $this->travel(1)->day();
        $this->ipn($service, 'sale_complete')->assertOk();
        $this->ipn($service, 'sale_canceled')->assertOk();
        $this->assertSame('paid', $service->fresh()->payment_status);
        $this->assertTrue($paidAt->equalTo($service->fresh()->paid_at));
        $this->assertSame(1, PaymentReceipt::count());
    }

    public function test_obsolete_payment_reference_and_invalid_signature_are_rejected(): void
    {
        $service = $this->service(['payment_reference' => 'current']);
        $this->ipn($service, 'sale_complete', 'old')->assertConflict();
        $this->postJson('/api/payments/ipn', ['type_event' => 'sale_complete'])->assertForbidden();
        $this->assertSame('pending', $service->fresh()->payment_status);
        $this->assertSame(0, PaymentReceipt::count());
    }

    public function test_price_changes_and_renewals_preserve_revenue(): void
    {
        $subscription = $this->subscription();
        $subscription->update(['payment_status' => 'paid', 'paid_at' => now()]);
        $subscription->plan->update(['price' => 40000]);
        $this->assertSame(25000, $subscription->fresh()->price_amount);
        $this->assertSame(25000, (int) PaymentReceipt::sum('amount'));
        $subscription->unsetRelation('plan');
        $subscription->renew();
        $this->assertNull($subscription->paid_at);
        $this->assertNull($subscription->payment_reference);
        $this->assertSame(40000, $subscription->price_amount);
        $subscription->update(['payment_status' => 'paid', 'paid_at' => now()]);
        $subscription->save();
        $this->assertSame(65000, (int) PaymentReceipt::sum('amount'));
        $this->assertSame(2, PaymentReceipt::count());
    }

    public function test_sla_command_uses_service_requests_and_preserves_pending_work(): void
    {
        Event::fake([SlaBreach::class]);
        SlaConfig::create(['name' => 'Test', 'is_active' => true, 'expiration_minutes' => 60]);
        $service = $this->service(['requested_at' => now()->subHours(2)]);
        $this->artisan('sla:run')->assertSuccessful();
        $this->artisan('sla:run')->assertSuccessful();
        Event::assertDispatchedTimes(SlaBreach::class, 1);
        $this->assertSame('pending', $service->fresh()->status);
        $this->assertNotNull($service->fresh()->sla_breach_sent_at);
        $this->assertDatabaseCount('audit_actions', 1);
    }

    public function test_subscription_checkout_uses_the_agreed_price(): void
    {
        $subscription = $this->subscription();
        $subscription->plan->update(['price' => 99000]);
        $this->mock(\App\Services\PaytechService::class)->shouldReceive('requestPayment')
            ->once()->withArgs(fn ($payload) => $payload['item_price'] === 25000)
            ->andReturn(['success' => 1, 'token' => 'test-token', 'redirect_url' => 'https://example.test/checkout']);
        Sanctum::actingAs($subscription->client);
        $this->postJson("/api/payments/subscription/{$subscription->id}/initiate")->assertOk();
    }

    public function test_subscription_webhook_does_not_reactivate_a_cancelled_subscription(): void
    {
        $subscription = $this->subscription();
        $subscription->update(['status' => 'cancelled', 'payment_reference' => 'SUB-test']);
        config(['services.paytech.api_key' => 'test-key', 'services.paytech.api_secret' => 'test-secret']);
        $payload = [
            'type_event' => 'sale_complete', 'ref_command' => 'SUB-test',
            'api_key_sha256' => hash('sha256', 'test-key'), 'api_secret_sha256' => hash('sha256', 'test-secret'),
            'custom_field' => json_encode(['type' => 'subscription', 'subscription_id' => $subscription->id]),
        ];
        $this->postJson('/api/payments/ipn', $payload)->assertOk();
        $this->postJson('/api/payments/ipn', array_merge($payload, ['type_event' => 'sale_canceled']))->assertOk();
        $this->assertSame('cancelled', $subscription->fresh()->status);
        $this->assertSame('paid', $subscription->fresh()->payment_status);
        $this->assertSame(1, PaymentReceipt::count());
    }

    public function test_admin_pages_render_with_empty_and_populated_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin')->assertOk()->assertSee('Tout commence par une demande');
        $service = $this->service();
        foreach (['/admin', '/admin?days=30', '/admin/service-requests', '/admin/service-requests/'.$service->id, '/admin/payments', '/admin/users', '/admin/drivers', '/admin/locations', '/admin/client-subscriptions', '/admin/subscription-plans', '/admin/notifications', '/admin/interventions', '/admin/driver-documents'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/layout-light')->assertNotFound();
    }
}
