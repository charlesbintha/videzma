<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\DriverDocument;
use App\Models\Intervention;
use App\Models\PaymentReceipt;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->integer('days', 7);
        $days = in_array($period, [7, 30], true) ? $period : 7;
        $start = now()->subDays($days - 1)->startOfDay();
        $receipts = PaymentReceipt::whereBetween('paid_at', [$start, now()->endOfDay()])->get();
        $series = collect(range(0, $days - 1))->map(function ($offset) use ($start, $receipts) {
            $date = $start->copy()->addDays($offset);

            return ['label' => $date->format('d/m'), 'amount' => $receipts->filter(fn ($receipt) => $receipt->paid_at->isSameDay($date))->sum('amount')];
        });
        $metrics = [
            'requests_pending' => ServiceRequest::where('status', 'pending')->count(),
            'interventions_today' => Intervention::whereDate('scheduled_at', today())->count(),
            'drivers_total' => User::where('role', 'driver')->where('status', 'active')->whereHas('driverProfile', fn ($query) => $query->where('verification_status', 'approved'))->count(),
            'driver_docs_pending' => DriverDocument::where('status', 'pending')->count(),
            'revenue_month' => PaymentReceipt::whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'payments_pending' => ServiceRequest::where('payment_status', 'pending')->where('payment_method', '!=', 'cash')->count() + ClientSubscription::where('payment_status', 'pending')->where('payment_method', '!=', 'cash')->count(),
        ];
        $statusCounts = ServiceRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $recentRequests = ServiceRequest::with(['client', 'driver'])->latest('requested_at')->limit(5)->get();
        $todayInterventions = Intervention::with('serviceRequest.client')->whereDate('scheduled_at', today())->orderBy('scheduled_at')->limit(4)->get();
        $estimatedRevenue = PaymentReceipt::where('source', 'legacy_estimate')->where('paid_at', '>=', min($start, now()->startOfMonth()))->exists();

        return view('admin.dashboard', compact('metrics', 'series', 'days', 'statusCounts', 'recentRequests', 'todayInterventions', 'estimatedRevenue'));
    }
}
