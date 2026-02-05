<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationRequest;
use App\Models\DoctorDocument;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'users_total' => User::count(),
            'patients_total' => User::where('role', 'patient')->count(),
            'doctors_total' => User::where('role', 'doctor')->count(),
            'doctor_docs_pending' => DoctorDocument::where('status', 'pending')->count(),
            'consultations_pending' => ConsultationRequest::where('status', 'pending')->count(),
            'appointments_today' => Appointment::whereDate('scheduled_at', now()->toDateString())->count(),
        ];

        return view('admin.dashboard', compact('metrics'));
    }
}
