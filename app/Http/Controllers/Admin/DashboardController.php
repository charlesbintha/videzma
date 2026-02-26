<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\DriverDocument;
use App\Models\Intervention;
use App\Models\ServiceRequest;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'users_total'          => User::count(),
            'clients_total'        => User::where('role', 'client')->count(),
            'drivers_total'        => User::where('role', 'driver')->count(),
            'driver_docs_pending'  => DriverDocument::where('status', 'pending')->count(),
            'requests_pending'     => ServiceRequest::where('status', 'pending')->count(),
            'interventions_today'  => Intervention::whereDate('scheduled_at', now()->toDateString())->count(),

            // Métriques financières
            'revenue_today'        => ServiceRequest::where('payment_status', 'paid')
                                        ->whereDate('paid_at', now()->toDateString())
                                        ->sum('price_amount')
                                    + ClientSubscription::where('payment_status', 'paid')
                                        ->whereDate('paid_at', now()->toDateString())
                                        ->join('subscription_plans', 'client_subscriptions.plan_id', '=', 'subscription_plans.id')
                                        ->sum('subscription_plans.price'),

            'revenue_month'        => ServiceRequest::where('payment_status', 'paid')
                                        ->whereMonth('paid_at', now()->month)
                                        ->whereYear('paid_at', now()->year)
                                        ->sum('price_amount')
                                    + ClientSubscription::where('payment_status', 'paid')
                                        ->whereMonth('paid_at', now()->month)
                                        ->whereYear('paid_at', now()->year)
                                        ->join('subscription_plans', 'client_subscriptions.plan_id', '=', 'subscription_plans.id')
                                        ->sum('subscription_plans.price'),

            'payments_pending'     => ServiceRequest::where('payment_status', 'pending')
                                        ->whereNotIn('payment_method', ['cash'])
                                        ->whereIn('status', ['pending', 'assigned', 'accepted', 'in_progress', 'completed'])
                                        ->count(),

            'payments_pending_amount' => ServiceRequest::where('payment_status', 'pending')
                                        ->whereNotIn('payment_method', ['cash'])
                                        ->whereIn('status', ['pending', 'assigned', 'accepted', 'in_progress', 'completed'])
                                        ->sum('price_amount'),
        ];

        return view('admin.dashboard', compact('metrics'));
    }
}
