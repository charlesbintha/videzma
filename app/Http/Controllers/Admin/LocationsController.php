<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function index(Request $request)
    {
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();
        $selectedDoctor = $request->input('doctor_id');
        $search = $request->input('search');

        $patientsQuery = User::where('role', 'patient')->with('latestLocation');
        $doctor = null;
        $doctorLocation = null;

        if ($selectedDoctor) {
            $doctor = User::where('role', 'doctor')->with('latestLocation')->find($selectedDoctor);
            $doctorLocation = $doctor?->latestLocation;

            $patientIds = ConsultationRequest::where('doctor_id', $selectedDoctor)
                ->whereNotNull('patient_id')
                ->distinct()
                ->pluck('patient_id');

            $patientsQuery->whereIn('id', $patientIds);
        }

        if ($search) {
            $patientsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $patients = $patientsQuery->orderBy('name')->get();

        $mapPatients = $patients->filter(function (User $patient) {
            return $patient->latestLocation !== null;
        })->map(function (User $patient) {
            $location = $patient->latestLocation;
            $timestamp = $location->captured_at ?? $location->created_at;

            return [
                'id' => $patient->id,
                'name' => $patient->name,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'address' => $location->address,
                'captured_at' => $timestamp?->toIso8601String(),
            ];
        })->values();

        $mapsKey = config('services.google_maps.key');
        $mapId = config('services.google_maps.map_id');
        $doctorMap = null;

        if ($doctor && $doctorLocation) {
            $doctorMap = [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'latitude' => (float) $doctorLocation->latitude,
                'longitude' => (float) $doctorLocation->longitude,
            ];
        }

        return view('admin.locations.index', [
            'doctors' => $doctors,
            'patients' => $patients,
            'mapPatients' => $mapPatients,
            'selectedDoctor' => $selectedDoctor,
            'search' => $search,
            'mapsKey' => $mapsKey,
            'mapId' => $mapId,
            'doctorMap' => $doctorMap,
        ]);
    }
}
