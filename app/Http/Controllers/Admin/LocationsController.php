<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function index(Request $request)
    {
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $selectedDriver = $request->input('driver_id');
        $search = $request->input('search');

        $clientsQuery = User::where('role', 'client')->with('latestLocation');
        $driver = null;
        $driverLocation = null;

        if ($selectedDriver) {
            $driver = User::where('role', 'driver')->with('latestLocation')->find($selectedDriver);
            $driverLocation = $driver?->latestLocation;

            $clientIds = ServiceRequest::where('driver_id', $selectedDriver)
                ->whereNotNull('client_id')
                ->distinct()
                ->pluck('client_id');

            $clientsQuery->whereIn('id', $clientIds);
        }

        if ($search) {
            $clientsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $clients = $clientsQuery->orderBy('name')->get();

        $mapClients = $clients->filter(function (User $client) {
            return $client->latestLocation !== null;
        })->map(function (User $client) {
            $location = $client->latestLocation;
            $timestamp = $location->captured_at ?? $location->created_at;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'address' => $location->address,
                'captured_at' => $timestamp?->toIso8601String(),
            ];
        })->values();

        $mapsKey = config('services.google_maps.key');
        $mapId = config('services.google_maps.map_id');
        $driverMap = null;

        if ($driver && $driverLocation) {
            $driverMap = [
                'id' => $driver->id,
                'name' => $driver->name,
                'latitude' => (float) $driverLocation->latitude,
                'longitude' => (float) $driverLocation->longitude,
            ];
        }

        return view('admin.locations.index', [
            'drivers' => $drivers,
            'clients' => $clients,
            'mapClients' => $mapClients,
            'selectedDriver' => $selectedDriver,
            'search' => $search,
            'mapsKey' => $mapsKey,
            'mapId' => $mapId,
            'driverMap' => $driverMap,
        ]);
    }
}
