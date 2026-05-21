<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformAdminController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::withoutGlobalScopes()
            ->with(['owner', 'subscriptionPlan'])
            ->withCount(['users', 'products', 'stores'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'tenants_page');

        $plans = SubscriptionPlan::query()
            ->withCount('tenants')
            ->orderBy('price')
            ->get();

        $stats = [
            'total_tenants' => Tenant::withoutGlobalScopes()->count(),
            'active_tenants' => Tenant::withoutGlobalScopes()->where('status', 'active')->count(),
            'suspended_tenants' => Tenant::withoutGlobalScopes()->where('status', 'suspended')->count(),
            'monthly_plan_value' => Tenant::withoutGlobalScopes()
                ->join('subscription_plans', 'subscription_plans.id', '=', 'tenants.subscription_plan_id')
                ->where('tenants.status', '!=', 'suspended')
                ->sum('subscription_plans.price'),
        ];

        return view('platform.index', compact('tenants', 'plans', 'stats'));
    }

    public function updateTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);

        $data = $request->validate([
            'status' => ['required', Rule::in(['trial', 'active', 'suspended'])],
            'subscription_plan_id' => ['required', 'integer', Rule::exists('subscription_plans', 'id')],
        ]);

        $plan = SubscriptionPlan::query()->findOrFail($data['subscription_plan_id']);

        $tenant->update([
            'status' => $data['status'],
            'subscription_plan_id' => $plan->id,
            'subscription_ends_at' => $data['status'] === 'suspended' ? $tenant->subscription_ends_at : now()->addMonth(),
        ]);

        Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => $data['status'] === 'suspended' ? 'paused' : 'active',
            'provider' => 'manual',
            'starts_at' => now(),
            'ends_at' => $data['status'] === 'suspended' ? null : now()->addMonth(),
            'metadata' => [
                'changed_by' => $request->user()->id,
                'source' => 'platform-panel',
                'tenant_status' => $data['status'],
            ],
        ]);

        return redirect()->route('platform.index')->with('status', "Tenant {$tenant->name} berhasil diperbarui.");
    }

    public function destroyTenant(Tenant $tenant): RedirectResponse
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenant->id);

        abort_unless($tenant->status === 'suspended', 403, 'Hanya tenant suspended yang bisa dihapus permanen.');

        $tenantName = $tenant->name;
        DB::table('platform_audit_logs')->insert([
            'tenant_id' => $tenant->id,
            'actor_user_id' => auth()->id(),
            'action' => 'delete_tenant',
            'subject_type' => Tenant::class,
            'subject_id' => $tenant->id,
            'metadata' => json_encode([
                'tenant_name' => $tenant->name,
                'tenant_slug' => $tenant->slug,
                'status' => $tenant->status,
            ]),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenant->delete();

        return redirect()->route('platform.index')->with('status', "Tenant {$tenantName} berhasil dihapus permanen.");
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $data = $this->validatedPlan($request);

        SubscriptionPlan::query()->create($data);

        return redirect()->route('platform.index')->with('status', 'Paket subscription baru berhasil dibuat.');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $this->validatedPlan($request, $plan);

        $plan->update($data);

        return redirect()->route('platform.index')->with('status', "Paket {$plan->name} berhasil diperbarui.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPlan(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('subscription_plans', 'code')->ignore($plan?->id)],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'integer', 'min:0'],
            'max_stores' => ['nullable', 'integer', 'min:1'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'report_retention_days' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'string'],
        ]);

        $data['features'] = collect(explode(',', (string) ($data['features'] ?? '')))
            ->map(fn (string $feature) => trim($feature))
            ->filter()
            ->values()
            ->all();

        return $data;
    }
}
