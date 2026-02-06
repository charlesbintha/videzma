<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverDocument;
use App\Models\Intervention;
use App\Models\ServiceRequest;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'users_total' => User::count(),
            'clients_total' => User::where('role', 'client')->count(),
            'drivers_total' => User::where('role', 'driver')->count(),
            'driver_docs_pending' => DriverDocument::where('status', 'pending')->count(),
            'requests_pending' => ServiceRequest::where('status', 'pending')->count(),
            'interventions_today' => Intervention::whereDate('scheduled_at', now()->toDateString())->count(),
        ];

        return view('admin.dashboard', compact('metrics'));
    }
}
