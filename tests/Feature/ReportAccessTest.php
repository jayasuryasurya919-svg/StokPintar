<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_view_only_own_sales_report_and_receipts(): void
    {
        [$tenant, $store, $cashier, $otherCashier] = $this->reportContext();
        $ownSale = $this->saleFor($tenant, $store, $cashier, 'OWN-001', now());
        $otherSale = $this->saleFor($tenant, $store, $otherCashier, 'OTHER-001', now());

        $this->actingAs($cashier)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee($ownSale->invoice_number)
            ->assertDontSee($otherSale->invoice_number);

        $this->actingAs($cashier)
            ->get(route('reports.receipt', $ownSale))
            ->assertOk();

        $this->actingAs($cashier)
            ->get(route('reports.receipt', $otherSale))
            ->assertForbidden();
    }

    public function test_report_retention_filters_sales_outside_plan_window(): void
    {
        [$tenant, $store, $cashier] = $this->reportContext(reportRetentionDays: 7);
        $recentSale = $this->saleFor($tenant, $store, $cashier, 'RECENT-001', now()->subDays(2));
        $oldSale = $this->saleFor($tenant, $store, $cashier, 'OLD-001', now()->subDays(20));

        $this->actingAs($cashier)
            ->get(route('reports.index', [
                'month' => now()->month,
                'year' => now()->year,
            ]))
            ->assertOk()
            ->assertSee($recentSale->invoice_number)
            ->assertDontSee($oldSale->invoice_number);
    }

    public function test_receipt_route_respects_report_retention(): void
    {
        [$tenant, $store, $cashier] = $this->reportContext(reportRetentionDays: 7);
        $oldSale = $this->saleFor($tenant, $store, $cashier, 'OLD-RECEIPT-001', now()->subDays(20));

        $this->actingAs($cashier)
            ->get(route('reports.receipt', $oldSale))
            ->assertForbidden();
    }

    public function test_cashier_dashboard_and_pos_recent_sales_show_only_own_transactions(): void
    {
        [$tenant, $store, $cashier, $otherCashier] = $this->reportContext();
        $ownSale = $this->saleFor($tenant, $store, $cashier, 'OWN-DASH-001', now());
        $otherSale = $this->saleFor($tenant, $store, $otherCashier, 'OTHER-DASH-001', now());

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($ownSale->invoice_number)
            ->assertDontSee($otherSale->invoice_number);

        $this->actingAs($cashier)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee($ownSale->invoice_number)
            ->assertDontSee($otherSale->invoice_number);
    }

    public function test_owner_does_not_see_recent_sales_panel_on_pos(): void
    {
        [$tenant, $store, $cashier] = $this->reportContext();
        $owner = $tenant->owner;
        $sale = $this->saleFor($tenant, $store, $cashier, 'OWNER-POS-001', now());

        $this->actingAs($owner)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertDontSee('Struk Terakhir')
            ->assertDontSee($sale->invoice_number);
    }

    public function test_cashier_pos_recent_sales_panel_is_limited_to_three_simple_receipts(): void
    {
        [$tenant, $store, $cashier] = $this->reportContext();

        foreach (range(1, 4) as $index) {
            $this->saleFor($tenant, $store, $cashier, 'RECENT-POS-00'.$index, now()->subMinutes(4 - $index));
        }

        $this->actingAs($cashier)
            ->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Struk Terakhir')
            ->assertSee('RECENT-POS-004')
            ->assertSee('RECENT-POS-003')
            ->assertSee('RECENT-POS-002')
            ->assertDontSee('RECENT-POS-001');
    }

    /**
     * @return array{0: Tenant, 1: Store, 2: User, 3: User}
     */
    private function reportContext(int $reportRetentionDays = 30): array
    {
        $plan = SubscriptionPlan::query()->create([
            'code' => 'report-plan-'.$reportRetentionDays,
            'name' => 'Report Plan',
            'price' => 0,
            'report_retention_days' => $reportRetentionDays,
            'features' => ['basic_pos'],
        ]);

        $owner = User::query()->create([
            'name' => 'Owner Report',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $plan->id,
            'name' => 'Tenant Report',
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $owner->forceFill(['tenant_id' => $tenant->id])->save();

        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Report',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_CASHIER,
        ]);

        $otherCashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Lain',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_CASHIER,
        ]);

        return [$tenant, $store, $cashier, $otherCashier];
    }

    private function saleFor(Tenant $tenant, Store $store, User $cashier, string $invoice, mixed $soldAt): Sale
    {
        return Sale::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'cashier_id' => $cashier->id,
            'invoice_number' => $invoice,
            'subtotal' => 10000,
            'total' => 10000,
            'paid_amount' => 10000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'paid',
            'sold_at' => $soldAt,
        ]);
    }
}
