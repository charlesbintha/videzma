<?php

namespace Tests\Feature;

use App\Events\DriverPositionUpdated;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LocationTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function mission(): ServiceRequest
    {
        $client = User::factory()->create();
        $site = Location::create(['user_id' => $client->id, 'latitude' => 14.6928, 'longitude' => -17.4467, 'captured_at' => now()->subDay()]);

        return ServiceRequest::create([
            'client_id' => $client->id, 'driver_id' => User::factory()->create(['role' => 'driver'])->id,
            'location_id' => $site->id, 'address' => 'Lieu fixe', 'price_amount' => 15000,
            'requested_at' => now(), 'payment_method' => 'wave', 'status' => 'accepted',
            'navigation_started_at' => now()->subMinute(),
        ]);
    }

    public function test_terminal_statuses_and_reassignment_close_tracking(): void
    {
        foreach (['completed', 'canceled', 'cancelled', 'rejected'] as $status) {
            $mission = $this->mission();
            $mission->update(['status' => $status]);
            $this->assertNotNull($mission->fresh()->navigation_ended_at);
            Sanctum::actingAs($mission->client);
            $this->getJson("/api/tracking/{$mission->id}")->assertOk()->assertJsonPath('tracking', false);
            $this->getJson("/api/tracking/{$mission->id}/status")->assertJsonPath('available', false);
        }
        $mission = $this->mission();
        $mission->update(['driver_id' => User::factory()->create(['role' => 'driver'])->id]);
        $this->assertFalse($mission->fresh()->isTrackingActive());
    }

    public function test_restart_resets_end_time_and_uses_fixed_destination(): void
    {
        Event::fake([DriverPositionUpdated::class]);
        $mission = $this->mission();
        $mission->update(['navigation_ended_at' => now()]);
        Location::create(['user_id' => $mission->client_id, 'latitude' => 0, 'longitude' => 0, 'captured_at' => now()]);
        $this->mock(\App\Services\Maps\GoogleMapsDirections::class)->shouldReceive('getFastestRoute')
            ->once()->with(14.69, -17.44, 14.6928, -17.4467)
            ->andReturn(['ok' => true, 'distance_km' => 2, 'eta_minutes' => 7, 'route_data' => []]);
        Sanctum::actingAs($mission->driver);
        $this->postJson('/api/driver/navigation/start', [
            'service_request_id' => $mission->id, 'origin_latitude' => 14.69, 'origin_longitude' => -17.44,
        ])->assertOk()->assertJsonPath('data.eta_source', 'road_route');
        $this->assertTrue($mission->fresh()->isTrackingActive());
        Event::assertDispatchedTimes(DriverPositionUpdated::class, 1);
    }

    public function test_position_updates_stop_and_both_endpoints_broadcast_consistently(): void
    {
        Event::fake([DriverPositionUpdated::class]);
        $mission = $this->mission();
        Sanctum::actingAs($mission->driver);
        $payload = ['service_request_id' => $mission->id, 'latitude' => 14.69, 'longitude' => -17.44];
        $this->postJson('/api/driver/navigation/position', $payload)->assertOk();
        $this->postJson('/api/locations', $payload)->assertCreated();
        Event::assertDispatchedTimes(DriverPositionUpdated::class, 2);
        $this->postJson('/api/driver/navigation/stop', ['service_request_id' => $mission->id])->assertOk();
        $count = Location::count();
        $this->postJson('/api/driver/navigation/position', $payload)->assertNotFound();
        $this->postJson('/api/driver/navigation/refresh', $payload)->assertNotFound();
        $this->assertSame($count, Location::count());
        $this->postJson('/api/locations', $payload)->assertCreated();
        Event::assertDispatchedTimes(DriverPositionUpdated::class, 2);
    }

    public function test_future_and_out_of_order_packets_are_rejected(): void
    {
        Event::fake([DriverPositionUpdated::class]);
        $mission = $this->mission();
        Sanctum::actingAs($mission->driver);
        $payload = ['service_request_id' => $mission->id, 'latitude' => 0, 'longitude' => 0];
        $this->postJson('/api/locations', $payload)->assertCreated();
        foreach (['/api/locations', '/api/driver/navigation/position'] as $endpoint) {
            foreach ([now()->addHour(), now()->subMinute()] as $time) {
                $this->postJson($endpoint, $payload + ['captured_at' => $time->toIso8601String()])
                    ->assertUnprocessable()->assertJsonValidationErrors('captured_at');
            }
        }
        Event::assertDispatchedTimes(DriverPositionUpdated::class, 1);
    }

    public function test_polling_marks_legacy_positions_stale_and_etag_changes_when_freshness_expires(): void
    {
        $mission = $this->mission();
        Location::create(['user_id' => $mission->driver_id, 'latitude' => 14.6928, 'longitude' => -17.4467, 'captured_at' => null]);
        Sanctum::actingAs($mission->client);
        $response = $this->getJson("/api/tracking/{$mission->id}")->assertOk()
            ->assertJsonPath('is_fresh', true)->assertJsonPath('d', 0)->assertJsonPath('eta', null);
        $etag = $response->headers->get('ETag');
        $this->withHeader('If-None-Match', $etag)->getJson("/api/tracking/{$mission->id}")->assertStatus(304);
        $this->travel(3)->minutes();
        $this->withHeader('If-None-Match', $etag)->getJson("/api/tracking/{$mission->id}")->assertOk()->assertJsonPath('is_fresh', false);
    }

    public function test_queued_position_is_not_broadcast_after_stop_or_reassignment(): void
    {
        $mission = $this->mission();
        $position = Location::create(['user_id' => $mission->driver_id, 'latitude' => 14.69, 'longitude' => -17.44, 'captured_at' => now()]);
        $event = new DriverPositionUpdated($mission, $position);
        $this->assertCount(1, $event->broadcastOn());
        $mission->update(['navigation_ended_at' => now()]);
        $this->assertSame([], $event->broadcastOn());
    }

    public function test_admin_map_uses_fixed_site_and_only_active_requests(): void
    {
        $mission = $this->mission();
        Location::create(['user_id' => $mission->client_id, 'latitude' => 0, 'longitude' => 0, 'captured_at' => now()]);
        $completed = $this->mission();
        $completed->update(['status' => 'completed']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->getJson('/admin/locations/data')->assertOk()->assertJsonCount(1, 'missions')
            ->assertJsonPath('missions.0.id', $mission->id)->assertJsonPath('missions.0.destination.lat', 14.6928);
        $this->get('/admin/locations')->assertOk()->assertSee('videzma-locations.js');
        $this->getJson('/admin/locations/data?search=absent')->assertJsonCount(0, 'missions');
        $this->getJson('/admin/locations/data?driver_id='.$completed->driver_id)->assertJsonCount(0, 'missions');
        $this->actingAs($mission->client)->getJson('/admin/locations/data')->assertForbidden();
    }
}
