<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestsController extends Controller
{
    /**
     * Liste des demandes assignees au vidangeur connecte.
     */
    public function index(Request $request)
    {
        $driver = $request->user();

        $services = ServiceRequest::where('driver_id', $driver->id)
            ->with(['client:id,name,phone', 'location:id,latitude,longitude,address', 'intervention'])
            ->orderByDesc('requested_at')
            ->get();

        return response()->json([
            'data' => $services->map(fn ($s) => $this->formatServiceRequest($s)),
        ]);
    }

    /**
     * Liste des demandes en attente assignees au vidangeur.
     */
    public function pending(Request $request)
    {
        $driver = $request->user();

        $services = ServiceRequest::where('driver_id', $driver->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->with(['client:id,name,phone', 'location:id,latitude,longitude,address'])
            ->orderBy('sla_due_at')
            ->get();

        return response()->json([
            'data' => $services->map(fn ($s) => $this->formatServiceRequest($s)),
        ]);
    }

    /**
     * Detail d'une demande avec commentaires.
     */
    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $serviceRequest->load([
            'client:id,name,email,phone',
            'location',
            'intervention',
            'comments' => fn ($q) => $q->with('author:id,name')->orderBy('created_at'),
        ]);

        return response()->json([
            'data' => $this->formatServiceRequestDetail($serviceRequest),
        ]);
    }

    /**
     * Accepter une demande de service.
     */
    public function accept(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (!in_array($serviceRequest->status, ['pending', 'assigned'])) {
            return response()->json(['message' => 'Impossible d\'accepter cette demande.'], 422);
        }

        $serviceRequest->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'accepted_at' => $serviceRequest->accepted_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Rejeter une demande de service.
     */
    public function reject(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if (!in_array($serviceRequest->status, ['pending', 'assigned'])) {
            return response()->json(['message' => 'Impossible de rejeter cette demande.'], 422);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $serviceRequest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'driver_notes' => $validated['reason'] ?? $serviceRequest->driver_notes,
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'rejected_at' => $serviceRequest->rejected_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Demarrer une intervention.
     */
    public function start(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($serviceRequest->status !== 'accepted') {
            return response()->json(['message' => 'La demande doit etre acceptee avant de commencer.'], 422);
        }

        $serviceRequest->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'started_at' => $serviceRequest->started_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Terminer une intervention.
     */
    public function complete(Request $request, ServiceRequest $serviceRequest)
    {
        $driver = $request->user();

        if ($serviceRequest->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($serviceRequest->status !== 'in_progress') {
            return response()->json(['message' => 'L\'intervention doit etre en cours.'], 422);
        }

        $validated = $request->validate([
            'actual_volume' => ['required', 'numeric', 'min:1', 'max:50'],
            'driver_notes' => ['nullable', 'string', 'max:2000'],
            'photo_before' => ['nullable', 'string', 'max:500'],
            'photo_after' => ['nullable', 'string', 'max:500'],
        ]);

        // Recalculer le prix final basé sur le volume réel
        $actualVolume = $validated['actual_volume'];
        $finalPrice = ServiceRequest::BASE_PRICE + (($actualVolume - 1) * ServiceRequest::PRICE_PER_M3);

        $serviceRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_volume' => $actualVolume,
            'price_amount' => $finalPrice,
            'driver_notes' => $validated['driver_notes'] ?? $serviceRequest->driver_notes,
            'photo_before' => $validated['photo_before'] ?? $serviceRequest->photo_before,
            'photo_after' => $validated['photo_after'] ?? $serviceRequest->photo_after,
        ]);

        return response()->json([
            'data' => [
                'id' => $serviceRequest->id,
                'status' => $serviceRequest->status,
                'completed_at' => $serviceRequest->completed_at->toIso8601String(),
                'actual_volume' => $serviceRequest->actual_volume,
                'price_amount' => $finalPrice,
                'price_formatted' => number_format($finalPrice, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }

    private function formatServiceRequest(ServiceRequest $s): array
    {
        return [
            'id' => $s->id,
            'status' => $s->status,
            'address' => $s->address,
            'fosse_type' => $s->fosse_type,
            'estimated_volume' => $s->estimated_volume,
            'urgency_level' => $s->urgency_level,
            'price_amount' => $s->price_amount,
            'price_formatted' => $s->formatted_price,
            'client_notes' => $s->client_notes,
            'requested_at' => $s->requested_at?->toIso8601String(),
            'accepted_at' => $s->accepted_at?->toIso8601String(),
            'started_at' => $s->started_at?->toIso8601String(),
            'completed_at' => $s->completed_at?->toIso8601String(),
            'sla_due_at' => $s->sla_due_at?->toIso8601String(),
            'client' => $s->client ? [
                'id' => $s->client->id,
                'name' => $s->client->name,
                'phone' => $s->client->phone,
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
    }

    private function formatServiceRequestDetail(ServiceRequest $s): array
    {
        $data = $this->formatServiceRequest($s);

        $data['client'] = $s->client ? [
            'id' => $s->client->id,
            'name' => $s->client->name,
            'email' => $s->client->email,
            'phone' => $s->client->phone,
        ] : null;

        $data['location'] = $s->location ? [
            'id' => $s->location->id,
            'latitude' => $s->location->latitude,
            'longitude' => $s->location->longitude,
            'address' => $s->location->address,
            'captured_at' => $s->location->captured_at?->toIso8601String(),
        ] : null;

        $data['intervention'] = $s->intervention ? [
            'id' => $s->intervention->id,
            'scheduled_at' => $s->intervention->scheduled_at?->toIso8601String(),
            'status' => $s->intervention->status,
            'started_at' => $s->intervention->started_at?->toIso8601String(),
            'ended_at' => $s->intervention->ended_at?->toIso8601String(),
            'completed_at' => $s->intervention->completed_at?->toIso8601String(),
        ] : null;

        $data['comments'] = $s->comments->map(fn ($comment) => [
            'id' => $comment->id,
            'content' => $comment->content,
            'is_internal' => $comment->is_internal,
            'author' => $comment->author ? [
                'id' => $comment->author->id,
                'name' => $comment->author->name,
            ] : null,
            'created_at' => $comment->created_at->toIso8601String(),
        ])->toArray();

        return $data;
    }
}
