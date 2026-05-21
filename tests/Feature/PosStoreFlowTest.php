<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosStoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_store_creates_a_sale_and_redirects_with_success_message(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Jus Jeruk',
            'sku' => 'JUS-001',
            'unit' => 'pcs',
            'cost_price' => 7000,
            'selling_price' => 18000,
            'stock' => 12,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->post(route('pos.store'), [
            'paid_amount' => 50000,
            'payment_method' => 'qris',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('status');

        $product->refresh();

        $this->assertSame(10, $product->stock);
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', [
            'cashier_id' => $cashier->id,
            'payment_method' => 'qris',
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_pos_store_rejects_when_all_item_quantities_are_zero(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Keripik',
            'sku' => 'KRP-001',
            'unit' => 'pcs',
            'cost_price' => 4000,
            'selling_price' => 7000,
            'stock' => 12,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->from(route('pos.index'))
            ->actingAs($cashier)
            ->post(route('pos.store'), [
                'paid_amount' => 10000,
                'payment_method' => 'cash',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 0],
                ],
            ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_pos_store_rejects_product_from_different_active_store(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();

        $otherStore = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Flow Store 2',
            'code' => 'BRANCH',
            'is_default' => false,
        ]);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $otherStore->id,
            'name' => 'Produk Cabang Lain',
            'sku' => 'OTHER-001',
            'unit' => 'pcs',
            'cost_price' => 5000,
            'selling_price' => 10000,
            'stock' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $response = $this->withSession(['store_id' => $store->id])
            ->from(route('pos.index'))
            ->actingAs($cashier)
            ->post(route('pos.store'), [
                'paid_amount' => 50000,
                'payment_method' => 'cash',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
            ]);

        $response->assertNotFound();

        $product->refresh();

        $this->assertSame(5, $product->stock);
        $this->assertDatabaseCount('sales', 0);
    }

    /**
     * @return array{0: Tenant, 1: User, 2: Store}
     */
    private function tenantCashierAndStore(): array
    {
        $cashier = User::query()->create([
            'name' => 'Kasir Flow',
            'email' => 'cashier-flow@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $cashier->id,
            'name' => 'Tenant Flow',
            'slug' => 'tenant-flow',
            'status' => 'trial',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Flow Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $cashier->forceFill(['tenant_id' => $tenant->id])->save();

        return [$tenant, $cashier, $store];
    }
}
