<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $paymentStatus = $request->get('payment_status');
        $paymentMethod = $request->get('payment_method');
        $type          = $request->get('type', 'all'); // all | service | subscription
        $search        = $request->get('search');
        $dateFrom      = $request->get('date_from');
        $dateTo        = $request->get('date_to');

        // --- Demandes de service ---
        $serviceQuery = ServiceRequest::with(['client:id,name,phone', 'driver:id,name'])
            ->select('id', 'client_id', 'driver_id', 'price_amount', 'payment_method',
                     'payment_status', 'payment_reference', 'paid_at', 'requested_at', 'address');

        if ($paymentStatus) {
            $serviceQuery->where('payment_status', $paymentStatus);
        }
        if ($paymentMethod) {
            $serviceQuery->where('payment_method', $paymentMethod);
        }
        if ($search) {
            $serviceQuery->where(function ($q) use ($search) {
                $q->where('address', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }
        if ($dateFrom) {
            $serviceQuery->where('requested_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $serviceQuery->where('requested_at', '<=', $dateTo . ' 23:59:59');
        }

        $servicePayments = $type !== 'subscription' ? $serviceQuery->latest('requested_at')->get() : collect();

        // --- Souscriptions ---
        $subQuery = ClientSubscription::with(['client:id,name,phone', 'plan:id,name,price'])
            ->select('id', 'client_id', 'plan_id', 'payment_method', 'payment_status',
                     'payment_reference', 'paid_at', 'created_at', 'current_period_start');

        if ($paymentStatus) {
            $subQuery->where('payment_status', $paymentStatus);
        }
        if ($paymentMethod) {
            $subQuery->where('payment_method', $paymentMethod);
        }
        if ($search) {
            $subQuery->whereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }
        if ($dateFrom) {
            $subQuery->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $subQuery->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $subscriptionPayments = $type !== 'service' ? $subQuery->latest()->get() : collect();

        // --- Statistiques globales (sans les filtres pour avoir les totaux réels) ---
        $stats = [
            'total_revenue'          => ServiceRequest::where('payment_status', 'paid')->sum('price_amount')
                                      + ClientSubscription::where('payment_status', 'paid')
                                            ->join('subscription_plans', 'client_subscriptions.plan_id', '=', 'subscription_plans.id')
                                            ->sum('subscription_plans.price'),

            'revenue_this_month'     => ServiceRequest::where('payment_status', 'paid')
                                            ->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)
                                            ->sum('price_amount')
                                      + ClientSubscription::where('payment_status', 'paid')
                                            ->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)
                                            ->join('subscription_plans', 'client_subscriptions.plan_id', '=', 'subscription_plans.id')
                                            ->sum('subscription_plans.price'),

            'pending_count'          => ServiceRequest::where('payment_status', 'pending')
                                            ->whereNotIn('payment_method', ['cash'])->count()
                                      + ClientSubscription::where('payment_status', 'pending')
                                            ->whereNotIn('payment_method', ['cash'])->count(),

            'pending_amount'         => ServiceRequest::where('payment_status', 'pending')
                                            ->whereNotIn('payment_method', ['cash'])
                                            ->sum('price_amount'),

            'failed_count'           => ServiceRequest::where('payment_status', 'failed')->count()
                                      + ClientSubscription::where('payment_status', 'failed')->count(),
        ];

        return view('admin.payments.index', compact(
            'servicePayments',
            'subscriptionPayments',
            'stats',
            'paymentStatus',
            'paymentMethod',
            'type',
            'search',
            'dateFrom',
            'dateTo'
        ));
    }
}
