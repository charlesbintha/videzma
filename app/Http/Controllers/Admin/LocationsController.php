<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.locations.index', [
            'drivers' => User::where('role', 'driver')->orderBy('name')->get(['id', 'name']),
            'selectedDriver' => $request->input('driver_id'),
            'search' => $request->input('search'),
            'mapData' => $this->payload($request),
            'mapsKey' => config('services.google_maps.key'),
        ]);
    }

    public function data(Request $request)
    {
        return response()->json($this->payload($request))->header('Cache-Control', 'no-store');
    }

    private function payload(Request $request): array
    {
        $filters = $request->validate(['driver_id' => 'nullable|integer', 'search' => 'nullable|string|max:100']);
        $query = ServiceRequest::whereIn('status', ServiceRequest::ACTIVE_STATUSES)
            ->with(['client:id,name', 'driver:id,name', 'location']);
        if ($filters['driver_id'] ?? null) {
            $query->where('driver_id', $filters['driver_id']);
        }
        if ($search = $filters['search'] ?? null) {
            $query->where(function ($query) use ($search) {
                $query->where('address', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('name', 'like', "%{$search}%"));
            });
        }
        $total = (clone $query)->count();
        $services = $query->latest('id')->limit(300)->get();
        $positions = Location::whereIn('user_id', $services->pluck('driver_id')->filter()->unique())
            ->where('id', function ($query) {
                $query->select('recent.id')->from('locations as recent')
                    ->whereColumn('recent.user_id', 'locations.user_id')
                    ->orderByRaw('COALESCE(recent.captured_at, recent.created_at) DESC')
                    ->orderByDesc('recent.id')->limit(1);
            })->get()->keyBy('user_id');
        $missions = $services->map(function (ServiceRequest $service) use ($positions) {
            $position = $positions[$service->driver_id] ?? null;
            $tracking = $service->isTrackingActive();

            return [
                'id' => $service->id, 'client' => $service->client?->name ?? 'Client',
                'status' => ServiceRequest::STATUS_LABELS[$service->status] ?? $service->status,
                'address' => $service->address ?: $service->location?->address,
                'destination' => $service->location ? [
                    'lat' => (float) $service->location->latitude, 'lng' => (float) $service->location->longitude,
                ] : null,
                'driver' => $service->driver ? ['id' => $service->driver_id, 'name' => $service->driver->name] : null,
                'tracking' => $tracking,
                'position' => $tracking && $position && $position->recordedAt()?->gte($service->navigation_started_at) ? [
                    'lat' => (float) $position->latitude, 'lng' => (float) $position->longitude,
                    'captured_at' => $position->recordedAt()?->toIso8601String(), 'fresh' => $position->isFresh(),
                ] : null,
                'url' => route('admin.service-requests.show', $service),
            ];
        })->values();

        return ['missions' => $missions, 'total' => $total, 'updated_at' => now()->toIso8601String()];
    }
}
