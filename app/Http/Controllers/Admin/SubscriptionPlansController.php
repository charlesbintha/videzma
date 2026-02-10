<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\ClientSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlansController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount('subscriptions')
            ->ordered()
            ->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        $periodicities = SubscriptionPlan::PERIODICITIES;
        return view('admin.subscription-plans.create', compact('periodicities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'periodicity' => ['required', 'string', Rule::in(array_keys(SubscriptionPlan::PERIODICITIES))],
            'interventions_per_period' => ['required', 'integer', 'min:1', 'max:50'],
            'max_volume_per_intervention' => ['required', 'numeric', 'min:1', 'max:100'],
            'price' => ['required', 'integer', 'min:1000'],
            'extra_volume_price' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        $plan = SubscriptionPlan::create($validated);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Forfait "' . $plan->name . '" cree avec succes.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        $periodicities = SubscriptionPlan::PERIODICITIES;
        $plan = $subscriptionPlan;
        return view('admin.subscription-plans.edit', compact('plan', 'periodicities'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'periodicity' => ['required', 'string', Rule::in(array_keys(SubscriptionPlan::PERIODICITIES))],
            'interventions_per_period' => ['required', 'integer', 'min:1', 'max:50'],
            'max_volume_per_intervention' => ['required', 'numeric', 'min:1', 'max:100'],
            'price' => ['required', 'integer', 'min:1000'],
            'extra_volume_price' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        $subscriptionPlan->update($validated);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Forfait "' . $subscriptionPlan->name . '" mis a jour.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Vérifier s'il y a des abonnements actifs
        $activeSubscriptions = ClientSubscription::where('plan_id', $subscriptionPlan->id)
            ->where('status', 'active')
            ->count();

        if ($activeSubscriptions > 0) {
            return back()->with('error', 'Impossible de supprimer ce forfait: ' . $activeSubscriptions . ' abonnement(s) actif(s).');
        }

        $name = $subscriptionPlan->name;
        $subscriptionPlan->delete();

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Forfait "' . $name . '" supprime.');
    }

    public function toggle(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update([
            'is_active' => !$subscriptionPlan->is_active,
        ]);

        $status = $subscriptionPlan->is_active ? 'active' : 'desactive';
        return back()->with('success', 'Forfait "' . $subscriptionPlan->name . '" ' . $status . '.');
    }
}
