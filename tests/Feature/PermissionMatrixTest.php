<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.edition' => 'full']);
    }

    public function test_manager_can_access_reports_stock_history_and_user_management_but_not_products_or_subscription(): void
    {
        [$manager, $product] = $this->teamUserWithTenantAndProduct(User::ROLE_MANAGER, 'manager@example.com');

        $this->actingAs($manager)->get(route('dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('reports.index'))->assertOk();
        $this->actingAs($manager)->get(route('stock-history.index'))->assertOk();
        $this->actingAs($manager)->get(route('users.index'))->assertOk();

        $this->actingAs($manager)->get(route('products.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('subscription.index'))->assertForbidden();
        $this->actingAs($manager)->post(route('products.stock.store', $product), [
            'type' => 'in',
            'quantity' => 1,
        ])->assertForbidden();
    }

    public function test_viewer_is_read_only_for_dashboard_and_reports_only(): void
    {
        [$viewer] = $this->teamUserWithTenantAndProduct(User::ROLE_VIEWER, 'viewer@example.com');

        $this->actingAs($viewer)->get(route('dashboard'))->assertOk();
        $this->actingAs($viewer)->get(route('reports.index'))->assertOk();

        $this->actingAs($viewer)->get(route('reports.export.pdf'))->assertForbidden();
        $this->actingAs($viewer)->get(route('reports.cashier'))->assertForbidden();
        $this->actingAs($viewer)->get(route('stock-history.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('pos.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('users.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('subscription.index'))->assertForbidden();
    }

    public function test_staff_gudang_can_mutate_stock_and_view_history_but_not_reports_or_pos(): void
    {
        [$staffGudang, $product] = $this->teamUserWithTenantAndProduct(User::ROLE_STAFF_GUDANG, 'gudang@example.com');

        $this->actingAs($staffGudang)->get(route('dashboard'))->assertOk();
        $this->actingAs($staffGudang)->get(route('products.index'))->assertOk();
        $this->actingAs($staffGudang)->get(route('stock-history.index'))->assertOk();

        $this->actingAs($staffGudang)->post(route('products.stock.store', $product), [
            'type' => 'in',
            'quantity' => 3,
            'notes' => 'Penerimaan supplier',
        ])->assertRedirect();

        $product->refresh();
        $this->assertSame(11, $product->stock);

        $this->actingAs($staffGudang)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($staffGudang)->get(route('pos.index'))->assertForbidden();
    }

    public function test_cashier_can_access_pos_and_own_reports_but_not_exports_or_cashier_performance(): void
    {
        [$cashier] = $this->teamUserWithTenantAndProduct(User::ROLE_CASHIER, 'cashier-matrix@example.com');

        $this->actingAs($cashier)->get(route('dashboard'))->assertOk();
        $this->actingAs($cashier)->get(route('pos.index'))->assertOk();
        $this->actingAs($cashier)->get(route('reports.index'))->assertOk();

        $this->actingAs($cashier)->get(route('reports.export.pdf'))->assertForbidden();
        $this->actingAs($cashier)->get(route('reports.cashier'))->assertForbidden();
        $this->actingAs($cashier)->get(route('products.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('users.index'))->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Product}
     */
    private function teamUserWithTenantAndProduct(string $role, string $email): array
    {
        $owner = User::query()->create([
            'name' => 'Owner Tenant',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Tenant Role Matrix',
            'slug' => fake()->unique()->slug(),
            'status' => 'trial',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $owner->forceFill(['tenant_id' => $tenant->id])->save();

        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => ucfirst(str_replace('_', ' ', $role)),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
        ]);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Matrix',
            'sku' => 'PM-'.fake()->unique()->numerify('###'),
            'unit' => 'pcs',
            'cost_price' => 5000,
            'selling_price' => 9000,
            'stock' => 8,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        return [$user, $product];
    }
}
