<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['consultationRequest.patient', 'consultationRequest.doctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $appointments = $query->orderByDesc('scheduled_at')->paginate(20)->withQueryString();

        return view('admin.appointments.index', compact('appointments'));
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['consultationRequest.patient', 'consultationRequest.doctor']);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['scheduled', 'in_progress', 'completed', 'canceled'])],
            'scheduled_at' => ['required', 'date'],
        ]);

        $appointment->status = $validated['status'];
        $appointment->scheduled_at = Carbon::parse($validated['scheduled_at']);

        if ($validated['status'] === 'in_progress' && !$appointment->started_at) {
            $appointment->started_at = now();
        }

        if ($validated['status'] === 'completed') {
            $appointment->started_at = $appointment->started_at ?? now();
            $appointment->completed_at = now();
            $appointment->ended_at = now();
        }

        if ($validated['status'] === 'canceled' && !$appointment->canceled_at) {
            $appointment->canceled_at = now();
        }

        $appointment->save();

        return redirect()
            ->route('admin.appointments.show', $appointment)
            ->with('status', 'Rendez-vous mis a jour.');
    }
}
