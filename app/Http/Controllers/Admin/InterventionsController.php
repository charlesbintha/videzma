<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use Illuminate\Http\Request;

class InterventionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Intervention::with(['serviceRequest.client', 'serviceRequest.driver']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->get('date')) {
            $query->whereDate('scheduled_at', $date);
        }

        $interventions = $query->latest('scheduled_at')->paginate(15)->withQueryString();
        $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];

        return view('admin.interventions.index', compact('interventions', 'statuses'));
    }

    public function show($id)
    {
        $intervention = Intervention::with(['serviceRequest.client', 'serviceRequest.driver', 'serviceRequest.location'])->findOrFail($id);
        return view('admin.interventions.show', compact('intervention'));
    }

    public function update(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'driver_notes' => 'nullable|string|max:1000',
            'actual_volume' => 'nullable|numeric|min:0|max:100',
        ]);

        $updateData = [];

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];

            // Mettre à jour les timestamps selon le statut
            switch ($validated['status']) {
                case 'in_progress':
                    if (!$intervention->started_at) {
                        $updateData['started_at'] = now();
                    }
                    break;
                case 'completed':
                    if (!$intervention->completed_at) {
                        $updateData['completed_at'] = now();
                        $updateData['ended_at'] = now();
                    }
                    break;
                case 'cancelled':
                    if (!$intervention->canceled_at) {
                        $updateData['canceled_at'] = now();
                    }
                    break;
            }
        }

        if (isset($validated['driver_notes'])) {
            $updateData['driver_notes'] = $validated['driver_notes'];
        }

        if (isset($validated['actual_volume'])) {
            $updateData['actual_volume'] = $validated['actual_volume'];
        }

        $intervention->update($updateData);

        return redirect()->route('admin.interventions.show', $intervention->id)
            ->with('success', 'Intervention mise a jour avec succes.');
    }
}
