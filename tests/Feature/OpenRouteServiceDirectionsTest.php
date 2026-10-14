<?php

namespace Tests\Feature;

use App\Services\Maps\DirectionsService;
use App\Services\Maps\OpenRouteServiceDirections;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenRouteServiceDirectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.routing.provider' => 'openrouteservice', 'services.openrouteservice.key' => 'test-ors-key']);
        Http::preventStrayRequests();
    }

    public function test_free_provider_preserves_geometry_steps_and_cache_without_calling_google(): void
    {
        Http::fake(['api.heigit.org/openrouteservice/*' => Http::response(['features' => [[
            'geometry' => ['coordinates' => [[-120.2, 38.5], [-120.95, 40.7], [-126.453, 43.252]]],
            'properties' => ['summary' => ['distance' => 4300, 'duration' => 720], 'segments' => [['steps' => [
                ['instruction' => 'Tournez à gauche', 'distance' => 4300, 'duration' => 720, 'way_points' => [0, 2], 'type' => 0],
            ]]]],
        ]]])]);
        $service = app(DirectionsService::class);
        $result = $service->getFastestRoute(38.5, -120.2, 43.252, -126.453);
        $this->assertTrue($result['ok']);
        $this->assertSame(4.3, $result['distance_km']);
        $this->assertSame(12, $result['eta_minutes']);
        $route = $result['route_data']['routes'][0];
        $this->assertSame('_p~iF~ps|U_ulLnnqC_mqNvxq`@', $route['overview_polyline']['points']);
        $this->assertSame('turn-left', $route['legs'][0]['steps'][0]['maneuver']);
        $this->assertSame(38.5, $route['legs'][0]['steps'][0]['start_location']['lat']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'test-ors-key') && $request['coordinates'][0] === [-120.2, 38.5] && $request['language'] === 'fr');
        $this->assertSame($result, $service->getFastestRoute(38.5, -120.2, 43.252, -126.453));
        Http::assertSentCount(1);
    }

    public function test_missing_key_does_not_fallback_to_paid_google(): void
    {
        config(['services.openrouteservice.key' => null]);
        $result = app(DirectionsService::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('activé', $result['message']);
        Http::assertNothingSent();
    }

    public function test_quota_exceeded_is_explicit_and_not_cached_as_a_route(): void
    {
        Http::fake(['*' => Http::response([], 429)]);
        $result = app(OpenRouteServiceDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('quota gratuit', $result['message']);
    }

    public function test_network_errors_do_not_expose_the_api_key(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $result = app(OpenRouteServiceDirections::class)->getFastestRoute(14.69, -17.44, 14.71, -17.46);
        $this->assertFalse($result['ok']);
        $this->assertStringNotContainsString('test-ors-key', $result['message']);
    }
}
