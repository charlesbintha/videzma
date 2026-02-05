<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class ConsultationsController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsultationRequest::with(['patient', 'doctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $consultations = $query->orderByDesc('requested_at')->paginate(20)->withQueryString();

        return view('admin.consultations.index', compact('consultations'));
    }

    public function show(ConsultationRequest $consultation)
    {
        $consultation->load(['patient', 'doctor', 'location', 'appointment']);
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();

        return view('admin.consultations.show', compact('consultation', 'doctors'));
    }

    public function update(Request $request, ConsultationRequest $consultation)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'assigned', 'accepted', 'rejected', 'canceled', 'closed', 'expired'])],
            'doctor_id' => ['nullable', 'exists:users,id'],
            'sla_due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $consultation->doctor_id = $validated['doctor_id'];
        $consultation->status = $validated['status'];
        $consultation->sla_due_at = $validated['sla_due_at'];
        $consultation->notes = $validated['notes'];

        if ($validated['status'] === 'accepted' && !$consultation->accepted_at) {
            $consultation->accepted_at = now();
        }

        if ($validated['status'] === 'rejected' && !$consultation->rejected_at) {
            $consultation->rejected_at = now();
        }

        if ($validated['status'] === 'canceled' && !$consultation->canceled_at) {
            $consultation->canceled_at = now();
        }

        if ($validated['status'] === 'closed' && !$consultation->closed_at) {
            $consultation->closed_at = now();
        }

        $consultation->save();

        return redirect()
            ->route('admin.consultations.show', $consultation)
            ->with('status', 'Consultation mise a jour.');
    }

    public function storeAppointment(Request $request, ConsultationRequest $consultation)
    {
        if ($consultation->appointment) {
            return back()->withErrors(['scheduled_at' => 'Appointment already exists.']);
        }

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
        ]);

        Appointment::create([
            'consultation_request_id' => $consultation->id,
            'scheduled_at' => Carbon::parse($validated['scheduled_at']),
            'status' => 'scheduled',
        ]);

        return redirect()
            ->route('admin.consultations.show', $consultation)
            ->with('status', 'Rendez-vous cree.');
    }
}
