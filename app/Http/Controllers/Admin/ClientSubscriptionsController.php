<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;

class ClientSubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientSubscription::with(['client', 'plan'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $plans = SubscriptionPlan::ordered()->get();

        $stats = [
            'total' => ClientSubscription::count(),
            'active' => ClientSubscription::where('status', 'active')
                ->where('current_period_end', '>=', now()->toDateString())
                ->count(),
            'expired' => ClientSubscription::where('current_period_end', '<', now()->toDateString())->count(),
            'paused' => ClientSubscription::where('status', 'paused')->count(),
        ];

        return view('admin.client-subscriptions.index', compact('subscriptions', 'plans', 'stats'));
    }

    public function create()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();
        $clients = User::where('role', 'client')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.client-subscriptions.create', compact('plans', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'payment_method' => ['required', 'string', 'in:cash,mobile_money,card,bank_transfer'],
            'payment_status' => ['required', 'string', 'in:pending,paid'],
            'auto_renew' => ['boolean'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $subscription = ClientSubscription::create([
            'client_id' => $validated['client_id'],
            'plan_id' => $validated['plan_id'],
            'status' => 'active',
            'current_period_start' => now()->toDateString(),
            'current_period_end' => now()->addDays($plan->period_days)->toDateString(),
            'interventions_used' => 0,
            'volume_used' => 0,
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
            'auto_renew' => $request->boolean('auto_renew', false),
        ]);

        return redirect()
            ->route('admin.client-subscriptions.index')
            ->with('success', 'Abonnement cree avec succes pour ' . $subscription->client->name);
    }

    public function show(ClientSubscription $clientSubscription)
    {
        $clientSubscription->load(['client', 'plan']);

        return view('admin.client-subscriptions.show', [
            'subscription' => $clientSubscription,
        ]);
    }

    public function edit(ClientSubscription $clientSubscription)
    {
        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('admin.client-subscriptions.edit', [
            'subscription' => $clientSubscription,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, ClientSubscription $clientSubscription)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'status' => ['required', 'string', 'in:active,paused,cancelled'],
            'current_period_end' => ['required', 'date'],
            'interventions_used' => ['required', 'integer', 'min:0'],
            'payment_status' => ['required', 'string', 'in:pending,paid'],
            'auto_renew' => ['boolean'],
        ]);

        $validated['auto_renew'] = $request->boolean('auto_renew', false);

        if ($validated['payment_status'] === 'paid' && $clientSubscription->payment_status !== 'paid') {
            $validated['paid_at'] = now();
        }

        if ($validated['status'] === 'paused' && $clientSubscription->status !== 'paused') {
            $validated['paused_at'] = now();
        }

        if ($validated['status'] === 'cancelled' && $clientSubscription->status !== 'cancelled') {
            $validated['cancelled_at'] = now();
        }

        $clientSubscription->update($validated);

        return redirect()
            ->route('admin.client-subscriptions.index')
            ->with('success', 'Abonnement mis a jour.');
    }

    public function destroy(ClientSubscription $clientSubscription)
    {
        $clientName = $clientSubscription->client->name;
        $clientSubscription->delete();

        return redirect()
            ->route('admin.client-subscriptions.index')
            ->with('success', 'Abonnement de ' . $clientName . ' supprime.');
    }

    public function pause(ClientSubscription $clientSubscription)
    {
        if ($clientSubscription->status === 'paused') {
            return back()->with('error', 'Cet abonnement est deja en pause.');
        }

        $clientSubscription->update([
            'status' => 'paused',
            'paused_at' => now(),
        ]);

        return back()->with('success', 'Abonnement mis en pause.');
    }

    public function resume(ClientSubscription $clientSubscription)
    {
        if ($clientSubscription->status !== 'paused') {
            return back()->with('error', 'Cet abonnement n\'est pas en pause.');
        }

        $clientSubscription->update([
            'status' => 'active',
            'paused_at' => null,
        ]);

        return back()->with('success', 'Abonnement reactive.');
    }

    public function renew(ClientSubscription $clientSubscription)
    {
        $clientSubscription->renew();

        return back()->with('success', 'Abonnement renouvele. Nouvelle periode jusqu\'au ' . $clientSubscription->current_period_end->format('d/m/Y'));
    }

    public function markPaid(ClientSubscription $clientSubscription)
    {
        $clientSubscription->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Paiement marque comme recu.');
    }
}
