<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    /**
     * Liste tous les forfaits disponibles.
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(fn ($plan) => $this->formatPlan($plan));

        return response()->json([
            'data' => $plans,
        ]);
    }

    /**
     * Détail d'un forfait.
     */
    public function showPlan(SubscriptionPlan $plan)
    {
        if (!$plan->is_active) {
            return response()->json([
                'message' => 'Forfait non disponible.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatPlan($plan),
        ]);
    }

    /**
     * Abonnement actif du client.
     */
    public function mySubscription(Request $request)
    {
        $subscription = ClientSubscription::with('plan')
            ->forClient($request->user()->id)
            ->active()
            ->first();

        if (!$subscription) {
            return response()->json([
                'data' => null,
                'message' => 'Aucun abonnement actif.',
            ]);
        }

        return response()->json([
            'data' => $this->formatSubscription($subscription),
        ]);
    }

    /**
     * Historique des abonnements du client.
     */
    public function history(Request $request)
    {
        $subscriptions = ClientSubscription::with('plan')
            ->forClient($request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($sub) => $this->formatSubscription($sub));

        return response()->json([
            'data' => $subscriptions,
        ]);
    }

    /**
     * Souscrire à un forfait.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'payment_method' => ['required', 'string', 'in:cash,orange_money,wave,card'],
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        if (!$plan->is_active) {
            return response()->json([
                'message' => 'Ce forfait n\'est plus disponible.',
            ], 422);
        }

        // Vérifier si le client a déjà un abonnement actif
        $existingSubscription = ClientSubscription::forClient($request->user()->id)
            ->active()
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'message' => 'Vous avez deja un abonnement actif. Annulez-le d\'abord.',
                'current_subscription' => $this->formatSubscription($existingSubscription),
            ], 422);
        }

        // Créer l'abonnement
        $subscription = ClientSubscription::create([
            'client_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'status' => ClientSubscription::STATUS_ACTIVE,
            'current_period_start' => now()->toDateString(),
            'current_period_end' => now()->addDays($plan->period_days)->toDateString(),
            'interventions_used' => 0,
            'volume_used' => 0,
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'pending',
            'auto_renew' => true,
        ]);

        $subscription->load('plan');

        return response()->json([
            'message' => 'Abonnement souscrit avec succes.',
            'data' => $this->formatSubscription($subscription),
        ], 201);
    }

    /**
     * Annuler l'abonnement.
     */
    public function cancel(Request $request)
    {
        $subscription = ClientSubscription::forClient($request->user()->id)
            ->active()
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif a annuler.',
            ], 404);
        }

        $subscription->update([
            'status' => ClientSubscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        return response()->json([
            'message' => 'Abonnement annule. Vous pouvez continuer a utiliser les interventions restantes jusqu\'a la fin de la periode.',
            'data' => $this->formatSubscription($subscription->fresh('plan')),
        ]);
    }

    /**
     * Mettre en pause l'abonnement.
     */
    public function pause(Request $request)
    {
        $subscription = ClientSubscription::forClient($request->user()->id)
            ->where('status', ClientSubscription::STATUS_ACTIVE)
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Aucun abonnement actif a mettre en pause.',
            ], 404);
        }

        $subscription->update([
            'status' => ClientSubscription::STATUS_PAUSED,
            'paused_at' => now(),
        ]);

        return response()->json([
            'message' => 'Abonnement mis en pause.',
            'data' => $this->formatSubscription($subscription->fresh('plan')),
        ]);
    }

    /**
     * Reprendre un abonnement en pause.
     */
    public function resume(Request $request)
    {
        $subscription = ClientSubscription::forClient($request->user()->id)
            ->where('status', ClientSubscription::STATUS_PAUSED)
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Aucun abonnement en pause a reprendre.',
            ], 404);
        }

        $subscription->update([
            'status' => ClientSubscription::STATUS_ACTIVE,
            'paused_at' => null,
        ]);

        return response()->json([
            'message' => 'Abonnement repris.',
            'data' => $this->formatSubscription($subscription->fresh('plan')),
        ]);
    }

    /**
     * Estimer le prix d'un forfait.
     */
    public function estimate(Request $request)
    {
        $validated = $request->validate([
            'periodicity' => ['required', 'string', 'in:weekly,biweekly,monthly,quarterly,yearly'],
            'interventions_per_period' => ['required', 'integer', 'min:1', 'max:10'],
            'volume' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ]);

        $volume = $validated['volume'] ?? 10;
        $interventions = $validated['interventions_per_period'];

        // Prix de base par intervention
        $basePrice = 15000;
        $pricePerM3 = 5000;
        $pricePerIntervention = $basePrice + ($volume * $pricePerM3);

        // Prix total pour la période
        $totalWithoutDiscount = $pricePerIntervention * $interventions;

        // Remise selon la périodicité
        $discountPercent = match($validated['periodicity']) {
            'weekly' => 5,
            'biweekly' => 8,
            'monthly' => 10,
            'quarterly' => 15,
            'yearly' => 20,
            default => 0,
        };

        $discount = ($totalWithoutDiscount * $discountPercent) / 100;
        $finalPrice = $totalWithoutDiscount - $discount;

        return response()->json([
            'data' => [
                'periodicity' => $validated['periodicity'],
                'periodicity_label' => SubscriptionPlan::PERIODICITIES[$validated['periodicity']] ?? $validated['periodicity'],
                'interventions_per_period' => $interventions,
                'volume_per_intervention' => $volume,
                'price_per_intervention' => $pricePerIntervention,
                'total_without_discount' => $totalWithoutDiscount,
                'discount_percent' => $discountPercent,
                'discount_amount' => (int) $discount,
                'final_price' => (int) $finalPrice,
                'formatted_price' => number_format($finalPrice, 0, ',', ' ') . ' FCFA',
            ],
        ]);
    }

    private function formatPlan(SubscriptionPlan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'description' => $plan->description,
            'periodicity' => $plan->periodicity,
            'periodicity_label' => $plan->periodicity_label,
            'period_days' => $plan->period_days,
            'interventions_per_period' => $plan->interventions_per_period,
            'max_volume_per_intervention' => (float) $plan->max_volume_per_intervention,
            'price' => $plan->price,
            'formatted_price' => $plan->formatted_price,
            'extra_volume_price' => $plan->extra_volume_price,
            'discount_percent' => $plan->discount_percent,
            'is_featured' => $plan->is_featured,
        ];
    }

    private function formatSubscription(ClientSubscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'plan' => $this->formatPlan($subscription->plan),
            'current_period_start' => $subscription->current_period_start->toDateString(),
            'current_period_end' => $subscription->current_period_end->toDateString(),
            'interventions_used' => $subscription->interventions_used,
            'remaining_interventions' => $subscription->remaining_interventions,
            'volume_used' => (float) $subscription->volume_used,
            'remaining_days' => $subscription->remaining_days,
            'payment_method' => $subscription->payment_method,
            'payment_status' => $subscription->payment_status,
            'auto_renew' => $subscription->auto_renew,
            'created_at' => $subscription->created_at->toIso8601String(),
        ];
    }
}
