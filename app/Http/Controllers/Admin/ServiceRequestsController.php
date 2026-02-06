<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceRequestsController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::with(['client', 'driver', 'location']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($driverId = $request->get('driver_id')) {
            $query->where('driver_id', $driverId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('address', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->latest('requested_at')->paginate(15)->withQueryString();
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $statuses = ['pending', 'assigned', 'accepted', 'rejected', 'in_progress', 'completed', 'cancelled'];

        return view('admin.service-requests.index', compact('requests', 'drivers', 'statuses'));
    }

    public function show($id)
    {
        $serviceRequest = ServiceRequest::with(['client', 'driver', 'location', 'intervention'])->findOrFail($id);
        $drivers = User::where('role', 'driver')
            ->whereHas('driverProfile', fn($q) => $q->where('verification_status', 'approved'))
            ->orderBy('name')
            ->get();

        return view('admin.service-requests.show', compact('serviceRequest', 'drivers'));
    }

    public function update(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,assigned,accepted,rejected,in_progress,completed,cancelled',
            'driver_id' => 'nullable|exists:users,id',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [];

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];

            // Mettre à jour les timestamps selon le statut
            switch ($validated['status']) {
                case 'assigned':
                    if (!$serviceRequest->assigned_at) {
                        $updateData['assigned_at'] = now();
                    }
                    break;
                case 'accepted':
                    if (!$serviceRequest->accepted_at) {
                        $updateData['accepted_at'] = now();
                    }
                    break;
                case 'rejected':
                    if (!$serviceRequest->rejected_at) {
                        $updateData['rejected_at'] = now();
                    }
                    break;
                case 'in_progress':
                    if (!$serviceRequest->started_at) {
                        $updateData['started_at'] = now();
                    }
                    break;
                case 'completed':
                    if (!$serviceRequest->completed_at) {
                        $updateData['completed_at'] = now();
                    }
                    break;
            }
        }

        if (isset($validated['driver_id'])) {
            $updateData['driver_id'] = $validated['driver_id'];
            if ($validated['driver_id'] && !$serviceRequest->assigned_at) {
                $updateData['assigned_at'] = now();
                $updateData['status'] = 'assigned';
            }
        }

        if (isset($validated['admin_notes'])) {
            $updateData['notes'] = $validated['admin_notes'];
        }

        $serviceRequest->update($updateData);

        return redirect()->route('admin.service-requests.show', $serviceRequest->id)
            ->with('success', 'Demande mise a jour avec succes.');
    }

    public function assignDriver(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::findOrFail($id);

        $validated = $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        $serviceRequest->update([
            'driver_id' => $validated['driver_id'],
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return redirect()->route('admin.service-requests.show', $serviceRequest->id)
            ->with('success', 'Vidangeur assigne avec succes.');
    }
}
