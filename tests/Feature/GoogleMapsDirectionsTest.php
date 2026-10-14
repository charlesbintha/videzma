<?php

namespace Tests\Feature;

use App\Services\Maps\GoogleMapsDirections;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleMapsDirectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google_maps.key' => 'test-key']);
        Http::preventStrayRequests();
    }

    public function test_billing_denial_is_not_reported_as_an_absent_route(): void
    {
        Http::fake(['*' => Http::response(['status' => 'REQUEST_DENIED', 'error_message' => 'You must enable Billing on the Google Cloud Project', 'routes' => []])]);
        $result = app(GoogleMapsDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('facturation Google Maps', $result['message']);
        $this->assertStringNotContainsString('Aucun itinéraire', $result['message']);
    }

    public function test_an_actual_absent_route_has_a_location_message(): void
    {
        Http::fake(['*' => Http::response(['status' => 'ZERO_RESULTS', 'routes' => []])]);
        $result = app(GoogleMapsDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('localisation du client', $result['message']);
    }

    public function test_http_denials_also_keep_the_google_failure_reason(): void
    {
        Http::fake(['*' => Http::response(['status' => 'REQUEST_DENIED', 'error_message' => 'You must enable Billing'], 403)]);
        $result = app(GoogleMapsDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertStringContainsString('facturation Google Maps', $result['message']);
    }

    public function test_network_failure_returns_a_retryable_message(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $result = app(GoogleMapsDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Réessayez', $result['message']);
    }

    public function test_success_keeps_route_and_traffic_duration(): void
    {
        $route = ['legs' => [['distance' => ['value' => 4300], 'duration' => ['value' => 600], 'duration_in_traffic' => ['value' => 720]]]];
        Http::fake(['*' => Http::response(['status' => 'OK', 'routes' => [$route]])]);
        $result = app(GoogleMapsDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertTrue($result['ok']);
        $this->assertSame(4.3, $result['distance_km']);
        $this->assertSame(12, $result['eta_minutes']);
        $this->assertSame($route, $result['route_data']['routes'][0]);
    }
}
