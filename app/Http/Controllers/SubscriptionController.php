<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant?->load(['subscriptionPlan', 'stores', 'products', 'users']);

        $plans = SubscriptionPlan::query()->orderBy('price')->get();

        $latestSubscription = Subscription::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest('starts_at')
            ->latest('created_at')
            ->first();

        return view('subscription.index', compact('tenant', 'plans', 'latestSubscription'));
    }

    public function updatePlan(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_if(! $tenant, 404);

        $data = $request->validate([
            'subscription_plan_id' => ['required', 'integer', Rule::exists('subscription_plans', 'id')],
        ]);

        $plan = SubscriptionPlan::query()->findOrFail($data['subscription_plan_id']);

        $tenant->update([
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'provider' => 'manual',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'metadata' => [
                'changed_by' => $request->user()->id,
                'source' => 'owner-panel',
            ],
        ]);

        return redirect()->route('subscription.index')->with('status', "Paket berhasil diubah ke {$plan->name}.");
    }

    public function updateTenant(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_if(! $tenant, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['trial', 'active'])],
        ]);

        $tenant->update($data);

        return redirect()->route('subscription.index')->with('status', 'Pengaturan tenant berhasil diperbarui.');
    }
}
