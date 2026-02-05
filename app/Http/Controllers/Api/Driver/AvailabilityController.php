<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Liste des disponibilites du vidangeur.
     */
    public function index(Request $request)
    {
        $driver = $request->user();

        $availabilities = Availability::where('driver_id', $driver->id)
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'data' => $availabilities->map(fn ($a) => $this->formatAvailability($a)),
        ]);
    }

    /**
     * Creer un creneau de disponibilite.
     */
    public function store(Request $request)
    {
        $driver = $request->user();

        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $availability = Availability::create([
            'driver_id' => $driver->id,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'note' => $validated['note'] ?? null,
            'is_booked' => false,
        ]);

        return response()->json([
            'data' => $this->formatAvailability($availability),
        ], 201);
    }

    /**
     * Modifier un creneau de disponibilite.
     */
    public function update(Request $request, Availability $availability)
    {
        $driver = $request->user();

        if ($availability->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($availability->is_booked) {
            return response()->json(['message' => 'Ce creneau est deja reserve.'], 422);
        }

        $validated = $request->validate([
            'starts_at' => ['sometimes', 'date', 'after:now'],
            'ends_at' => ['sometimes', 'date', 'after:starts_at'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $availability->update($validated);

        return response()->json([
            'data' => $this->formatAvailability($availability),
        ]);
    }

    /**
     * Supprimer un creneau de disponibilite.
     */
    public function destroy(Request $request, Availability $availability)
    {
        $driver = $request->user();

        if ($availability->driver_id !== $driver->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($availability->is_booked) {
            return response()->json(['message' => 'Ce creneau est deja reserve et ne peut pas etre supprime.'], 422);
        }

        $availability->delete();

        return response()->json([
            'message' => 'Creneau supprime.',
        ]);
    }

    private function formatAvailability(Availability $a): array
    {
        return [
            'id' => $a->id,
            'starts_at' => $a->starts_at?->toIso8601String(),
            'ends_at' => $a->ends_at?->toIso8601String(),
            'is_booked' => $a->is_booked,
            'note' => $a->note,
            'created_at' => $a->created_at->toIso8601String(),
        ];
    }
}
