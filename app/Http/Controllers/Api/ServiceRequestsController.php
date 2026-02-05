<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceRequestsController extends Controller
{
    /**
     * Liste des demandes de service du client connecté.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $services = ServiceRequest::where('client_id', $user->id)
            ->with(['driver:id,name,phone', 'location:id,latitude,longitude,address', 'intervention'])
            ->orderByDesc('requested_at')
            ->get();

        return response()->json([
            'data' => $services->map(function ($s) {
                return [
                    'id' => $s->id,
                    'status' => $s->status,
                    'address' => $s->address,
                    'fosse_type' => $s->fosse_type,
                    'estimated_volume' => $s->estimated_volume,
                    'urgency_level' => $s->urgency_level,
                    'price_amount' => $s->price_amount,
                    'price_formatted' => $s->formatted_price,
                    'notes' => $s->client_notes,
                    'requested_at' => $s->requested_at?->toIso8601String(),
                    'assigned_at' => $s->assigned_at?->toIso8601String(),
                    'accepted_at' => $s->accepted_at?->toIso8601String(),
                    'started_at' => $s->started_at?->toIso8601String(),
                    'completed_at' => $s->completed_at?->toIso8601String(),
                    'canceled_at' => $s->canceled_at?->toIso8601String(),
                    'rating' => $s->rating,
                    'driver' => $s->driver ? [
                        'id' => $s->driver->id,
                        'name' => $s->driver->name,
                        'phone' => $s->driver->phone,
                    ] : null,
                    'location' => $s->location ? [
                        'id' => $s->location->id,
                        'latitude' => $s->location->latitude,
                        'longitude' => $s->location->longitude,
                        'address' => $s->location->address,
                    ] : null,
                    'intervention' => $s->intervention ? [
                        'id' => $s->intervention->id,
                        'scheduled_at' => $s->intervention->scheduled_at?->toIso8601String(),
                        'status' => $s->intervention->status,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Détail d'une demande de service.
     */
    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        if ($serviceRequest->client_id !== $user->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $serviceRequest->load(['driver:id,name,email,phone', 'location', 'intervention']);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'address' => $serviceRequest->address,
                'fosse_type' => $serviceRequest->fosse_type,
                'estimated_volume' => $serviceRequest->estimated_volume,
                'actual_volume' => $serviceRequest->actual_volume,
                'urgency_level' => $serviceRequest->urgency_level,
                'distance_km' => $serviceRequest->distance_km,
                'price_amount' => $serviceRequest->price_amount,
                'price_formatted' => $serviceRequest->formatted_price,
                'payment_method' => $serviceRequest->payment_method,
                'payment_status' => $serviceRequest->payment_status,
                'client_notes' => $serviceRequest->client_notes,
                'driver_notes' => $serviceRequest->driver_notes,
                'requested_at' => $serviceRequest->requested_at?->toIso8601String(),
                'assigned_at' => $serviceRequest->assigned_at?->toIso8601String(),
                'accepted_at' => $serviceRequest->accepted_at?->toIso8601String(),
                'started_at' => $serviceRequest->started_at?->toIso8601String(),
                'completed_at' => $serviceRequest->completed_at?->toIso8601String(),
                'canceled_at' => $serviceRequest->canceled_at?->toIso8601String(),
                'photo_before' => $serviceRequest->photo_before,
                'photo_after' => $serviceRequest->photo_after,
                'rating' => $serviceRequest->rating,
                'rating_comment' => $serviceRequest->rating_comment,
                'driver' => $serviceRequest->driver ? [
                    'id' => $serviceRequest->driver->id,
                    'name' => $serviceRequest->driver->name,
                    'email' => $serviceRequest->driver->email,
                    'phone' => $serviceRequest->driver->phone,
                ] : null,
                'location' => $serviceRequest->location ? [
                    'id' => $serviceRequest->location->id,
                    'latitude' => $serviceRequest->location->latitude,
                    'longitude' => $serviceRequest->location->longitude,
                    'address' => $serviceRequest->location->address,
                    'captured_at' => $serviceRequest->location->captured_at?->toIso8601String(),
                ] : null,
                'intervention' => $serviceRequest->intervention ? [
                    'id' => $serviceRequest->intervention->id,
                    'scheduled_at' => $serviceRequest->intervention->scheduled_at?->toIso8601String(),
                    'status' => $serviceRequest->intervention->status,
                    'started_at' => $serviceRequest->intervention->started_at?->toIso8601String(),
                    'ended_at' => $serviceRequest->intervention->ended_at?->toIso8601String(),
                    'completed_at' => $serviceRequest->intervention->completed_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    /**
     * Créer une nouvelle demande de service.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Acces refuse.',
            ], 401);
        }

        $validated = $request->validate([
            'address' => ['required', 'string', 'max:500'],
            'fosse_type' => ['nullable', 'string', 'in:traditionnelle,septique,toutes_eaux'],
            'estimated_volume' => ['nullable', 'numeric', 'min:1', 'max:50'],
            'urgency_level' => ['nullable', 'string', 'in:normal,urgent,emergency'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
            'driver_id' => ['nullable', 'exists:users,id'],
            'payment_method' => ['required', 'in:orange_money,wave,cash'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Calculer le prix basé sur le volume estimé
        $estimatedVolume = $validated['estimated_volume'] ?? 5; // 5m³ par défaut
        $priceAmount = ServiceRequest::BASE_PRICE + (($estimatedVolume - 1) * ServiceRequest::PRICE_PER_M3);

        // Créer la location du client
        $location = Location::create([
            'user_id' => $user->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address' => $validated['address'],
            'captured_at' => now(),
        ]);

        // Si un driver spécifique est demandé, vérifier qu'il est valide
        $driverId = null;
        $distanceKm = null;

        if (!empty($validated['driver_id'])) {
            $driver = User::where('id', $validated['driver_id'])
                ->where('role', 'driver')
                ->where('status', 'active')
                ->first();

            if ($driver) {
                $driverId = $driver->id;

                // Calculer la distance si le driver a une localisation
                $driverLocation = Location::where('user_id', $driver->id)
                    ->latest('captured_at')
                    ->first();

                if ($driverLocation) {
                    $distance = $this->calculateDistance(
                        $validated['latitude'],
                        $validated['longitude'],
                        (float) $driverLocation->latitude,
                        (float) $driverLocation->longitude
                    );
                    $distanceKm = round($distance / 1000, 2);
                }
            }
        }

        $serviceRequest = ServiceRequest::create([
            'client_id' => $user->id,
            'driver_id' => $driverId,
            'location_id' => $location->id,
            'address' => $validated['address'],
            'fosse_type' => $validated['fosse_type'] ?? 'septique',
            'estimated_volume' => $estimatedVolume,
            'urgency_level' => $validated['urgency_level'] ?? 'normal',
            'distance_km' => $distanceKm,
            'price_amount' => $priceAmount,
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'pending',
            'status' => $driverId ? 'assigned' : 'pending',
            'client_notes' => $validated['client_notes'] ?? null,
            'requested_at' => now(),
            'assigned_at' => $driverId ? now() : null,
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'address' => $serviceRequest->address,
                'fosse_type' => $serviceRequest->fosse_type,
                'estimated_volume' => $serviceRequest->estimated_volume,
                'urgency_level' => $serviceRequest->urgency_level,
                'distance_km' => $distanceKm,
                'price_amount' => $priceAmount,
                'price_formatted' => number_format($priceAmount, 0, ',', ' ') . ' FCFA',
                'payment_method' => $validated['payment_method'],
            ],
        ], 201);
    }

    /**
     * Annuler une demande de service (client).
     */
    public function cancel(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        if ($serviceRequest->client_id !== $user->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (!in_array($serviceRequest->status, ['pending', 'assigned', 'accepted'])) {
            return response()->json(['message' => 'Impossible d\'annuler cette demande.'], 422);
        }

        $serviceRequest->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
            ],
        ]);
    }

    /**
     * Noter une intervention terminée.
     */
    public function rate(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        if ($serviceRequest->client_id !== $user->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($serviceRequest->status !== 'completed') {
            return response()->json(['message' => 'L\'intervention doit être terminée pour la noter.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $serviceRequest->update([
            'rating' => $validated['rating'],
            'rating_comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'rating' => $serviceRequest->rating,
                'rating_comment' => $serviceRequest->rating_comment,
            ],
        ]);
    }

    /**
     * Calcule la distance entre deux points (formule de Haversine)
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // en mètres

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) * sin($deltaLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
