<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\Location;
use App\Models\NavigationSession;
use App\Models\User;
use App\Services\Maps\GoogleMapsDirections;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function fastest(Request $request, User $patient, GoogleMapsDirections $maps)
    {
        $doctor = $request->user();

        if (!$doctor) {
            abort(401, 'Acces refuse.');
        }

        if (!in_array($doctor->role, ['doctor', 'admin'], true)) {
            abort(403, 'Acces refuse.');
        }

        if ($doctor->role === 'doctor') {
            $follows = ConsultationRequest::where('doctor_id', $doctor->id)
                ->where('patient_id', $patient->id)
                ->exists();

            if (!$follows) {
                abort(403, 'Acces refuse.');
            }
        }

        $validated = $request->validate([
            'origin_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'origin_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'consultation_request_id' => ['nullable', 'exists:consultation_requests,id'],
        ]);

        $originLat = $validated['origin_latitude'] ?? null;
        $originLng = $validated['origin_longitude'] ?? null;

        if ($originLat === null || $originLng === null) {
            $origin = Location::where('user_id', $doctor->id)
                ->orderByDesc('captured_at')
                ->orderByDesc('created_at')
                ->first();

            if (!$origin) {
                return response()->json([
                    'message' => 'Position du medecin manquante.',
                ], 422);
            }

            $originLat = (float) $origin->latitude;
            $originLng = (float) $origin->longitude;
        }

        $destination = Location::where('user_id', $patient->id)
            ->orderByDesc('captured_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$destination) {
            return response()->json([
                'message' => 'Position du patient manquante.',
            ], 404);
        }

        $consultation = null;

        if (!empty($validated['consultation_request_id'])) {
            $consultation = ConsultationRequest::whereKey($validated['consultation_request_id'])
                ->where('patient_id', $patient->id)
                ->when($doctor->role === 'doctor', function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id);
                })
                ->first();

            if (!$consultation) {
                return response()->json([
                    'message' => 'Consultation invalide.',
                ], 422);
            }
        }

        $route = $maps->getFastestRoute(
            $originLat,
            $originLng,
            (float) $destination->latitude,
            (float) $destination->longitude
        );

        if (!$route['ok']) {
            return response()->json([
                'message' => $route['message'],
            ], 502);
        }

        $session = NavigationSession::create([
            'doctor_id' => $doctor->id,
            'consultation_request_id' => $consultation?->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $itinerary = $session->itineraries()->create([
            'distance_km' => $route['distance_km'],
            'eta_minutes' => $route['eta_minutes'],
            'route_data' => $route['route_data'],
        ]);

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'itinerary_id' => $itinerary->id,
                'distance_km' => $itinerary->distance_km,
                'eta_minutes' => $itinerary->eta_minutes,
                'origin' => [
                    'latitude' => $originLat,
                    'longitude' => $originLng,
                ],
                'destination' => [
                    'latitude' => (float) $destination->latitude,
                    'longitude' => (float) $destination->longitude,
                ],
            ],
        ]);
    }
}
