<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationRequest;
use Illuminate\Http\Request;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $doctor = auth()->user();

        $query = Appointment::whereHas('consultationRequest', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->id);
        })->with(['consultationRequest.patient:id,name,phone']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->input('date'));
        }

        $appointments = $query->orderBy('scheduled_at', 'desc')->paginate(20)->withQueryString();

        return view('doctor.appointments.index', compact('appointments'));
    }

    public function show(Appointment $appointment)
    {
        $doctor = auth()->user();

        if ($appointment->consultationRequest->doctor_id !== $doctor->id) {
            abort(403);
        }

        $appointment->load(['consultationRequest.patient', 'consultationRequest.location']);

        return view('doctor.appointments.show', compact('appointment'));
    }

    public function create()
    {
        $doctor = auth()->user();

        $consultations = ConsultationRequest::where('doctor_id', $doctor->id)
            ->where('status', 'accepted')
            ->doesntHave('appointment')
            ->with('patient:id,name')
            ->get();

        return view('doctor.appointments.create', compact('consultations'));
    }

    public function store(Request $request)
    {
        $doctor = auth()->user();

        $validated = $request->validate([
            'consultation_request_id' => ['required', 'exists:consultation_requests,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $consultation = ConsultationRequest::find($validated['consultation_request_id']);

        if ($consultation->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($consultation->status !== 'accepted') {
            return back()->withErrors(['consultation_request_id' => 'La consultation doit etre acceptee.']);
        }

        if ($consultation->appointment) {
            return back()->withErrors(['consultation_request_id' => 'Un RDV existe deja.']);
        }

        $appointment = Appointment::create([
            'consultation_request_id' => $consultation->id,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'scheduled',
        ]);

        return redirect()
            ->route('doctor.appointments.show', $appointment)
            ->with('status', 'Rendez-vous cree.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $doctor = auth()->user();

        if ($appointment->consultationRequest->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($appointment->status !== 'scheduled') {
            return back()->withErrors(['status' => 'Ce RDV ne peut pas etre replanifie.']);
        }

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $appointment->update([
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        return back()->with('status', 'Rendez-vous replanifie.');
    }

    public function cancel(Appointment $appointment)
    {
        $doctor = auth()->user();

        if ($appointment->consultationRequest->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($appointment->status !== 'scheduled') {
            return back()->withErrors(['status' => 'Ce RDV ne peut pas etre annule.']);
        }

        $appointment->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        return back()->with('status', 'Rendez-vous annule.');
    }

    public function start(Appointment $appointment)
    {
        $doctor = auth()->user();

        if ($appointment->consultationRequest->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($appointment->status !== 'scheduled') {
            return back()->withErrors(['status' => 'Ce RDV ne peut pas etre demarre.']);
        }

        $appointment->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return back()->with('status', 'Rendez-vous demarre.');
    }

    public function complete(Appointment $appointment)
    {
        $doctor = auth()->user();

        if ($appointment->consultationRequest->doctor_id !== $doctor->id) {
            abort(403);
        }

        if ($appointment->status !== 'in_progress') {
            return back()->withErrors(['status' => 'Ce RDV ne peut pas etre termine.']);
        }

        $appointment->update([
            'status' => 'completed',
            'ended_at' => now(),
            'completed_at' => now(),
        ]);

        return back()->with('status', 'Rendez-vous termine.');
    }
}
