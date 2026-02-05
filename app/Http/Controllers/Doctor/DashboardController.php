<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = auth()->user();

        $metrics = [
            'consultations_pending' => ConsultationRequest::where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'assigned'])
                ->count(),
            'consultations_accepted' => ConsultationRequest::where('doctor_id', $doctor->id)
                ->where('status', 'accepted')
                ->count(),
            'appointments_today' => Appointment::whereHas('consultationRequest', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
                ->whereDate('scheduled_at', now()->toDateString())
                ->count(),
            'appointments_upcoming' => Appointment::whereHas('consultationRequest', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
                ->where('scheduled_at', '>', now())
                ->where('status', 'scheduled')
                ->count(),
        ];

        $pendingConsultations = ConsultationRequest::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'assigned'])
            ->with(['patient:id,name,phone', 'location:id,address'])
            ->orderBy('sla_due_at')
            ->take(5)
            ->get();

        $todayAppointments = Appointment::whereHas('consultationRequest', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->id);
        })
            ->with(['consultationRequest.patient:id,name,phone'])
            ->whereDate('scheduled_at', now()->toDateString())
            ->orderBy('scheduled_at')
            ->get();

        return view('doctor.dashboard', compact('metrics', 'pendingConsultations', 'todayAppointments'));
    }
}
