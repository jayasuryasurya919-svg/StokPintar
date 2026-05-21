<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMutation;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\POS\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoidSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_void_sale_and_restore_stock(): void
    {
        [$tenant, $owner, $store] = $this->ownerWithStore();
        $this->actingAs($owner);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Void',
            'sku' => 'VOID-001',
            'unit' => 'pcs',
            'cost_price' => 10000,
            'selling_price' => 20000,
            'stock' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $sale = app(CheckoutService::class)->checkout(
            tenantId: $tenant->id,
            cashierId: $owner->id,
            storeId: $store->id,
            items: [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            paidAmount: 100000,
        );

        $product->refresh();
        $this->assertSame(7, $product->stock);

        $response = $this->from(route('reports.index'))
            ->post(route('sales.void', $sale));

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('status');

        $product->refresh();
        $sale->refresh();

        $this->assertSame('void', $sale->status);
        $this->assertSame(10, $product->stock);
        $this->assertDatabaseHas('stock_mutations', [
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'type' => StockMutation::TYPE_ADJUSTMENT,
            'quantity' => 3,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_cashier_cannot_void_sale(): void
    {
        [$tenant, $owner, $store] = $this->ownerWithStore();
        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Void',
            'email' => 'cashier-void@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Void Cashier',
            'sku' => 'VOID-002',
            'unit' => 'pcs',
            'cost_price' => 10000,
            'selling_price' => 20000,
            'stock' => 8,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $sale = app(CheckoutService::class)->checkout(
            tenantId: $tenant->id,
            cashierId: $owner->id,
            storeId: $store->id,
            items: [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            paidAmount: 50000,
        );

        $this->actingAs($cashier)
            ->post(route('sales.void', $sale))
            ->assertForbidden();

        $sale->refresh();
        $product->refresh();

        $this->assertSame('paid', $sale->status);
        $this->assertSame(6, $product->stock);
    }

    public function test_void_menu_sale_restores_recipe_ingredient_stock(): void
    {
        [$tenant, $owner, $store] = $this->ownerWithStore();
        $this->actingAs($owner);

        $coffee = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Kopi Void',
            'sku' => 'VOID-KOPI',
            'product_type' => Product::TYPE_STOCK,
            'unit' => 'gram',
            'cost_price' => 100,
            'selling_price' => 0,
            'stock' => 80,
            'minimum_stock' => 10,
            'is_active' => false,
        ]);

        $menu = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Americano Void',
            'sku' => 'VOID-AMERICANO',
            'product_type' => Product::TYPE_MENU,
            'unit' => 'porsi',
            'cost_price' => 7000,
            'selling_price' => 18000,
            'stock' => 0,
            'minimum_stock' => 4,
            'is_active' => true,
        ]);

        $menu->recipes()->create([
            'tenant_id' => $tenant->id,
            'ingredient_product_id' => $coffee->id,
            'quantity' => 18,
        ]);

        $sale = app(CheckoutService::class)->checkout(
            tenantId: $tenant->id,
            cashierId: $owner->id,
            storeId: $store->id,
            items: [
                ['product_id' => $menu->id, 'quantity' => 2],
            ],
            paidAmount: 50000,
        );

        $coffee->refresh();
        $this->assertSame(44, $coffee->stock);

        $this->from(route('reports.index'))
            ->post(route('sales.void', $sale))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHas('status');

        $coffee->refresh();
        $sale->refresh();

        $this->assertSame(80, $coffee->stock);
        $this->assertSame('void', $sale->status);
        $this->assertDatabaseHas('stock_mutations', [
            'tenant_id' => $tenant->id,
            'product_id' => $coffee->id,
            'user_id' => $owner->id,
            'type' => StockMutation::TYPE_ADJUSTMENT,
            'quantity' => 36,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_sale_cannot_be_voided_twice(): void
    {
        [$tenant, $owner, $store] = $this->ownerWithStore();
        $this->actingAs($owner);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Double Void',
            'sku' => 'VOID-003',
            'unit' => 'pcs',
            'cost_price' => 10000,
            'selling_price' => 20000,
            'stock' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $sale = app(CheckoutService::class)->checkout(
            tenantId: $tenant->id,
            cashierId: $owner->id,
            storeId: $store->id,
            items: [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            paidAmount: 50000,
        );

        $this->post(route('sales.void', $sale))->assertSessionHas('status');

        $response = $this->from(route('reports.index'))
            ->post(route('sales.void', $sale));

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHasErrors('sale');

        $product->refresh();
        $sale->refresh();

        $this->assertSame('void', $sale->status);
        $this->assertSame(5, $product->stock);
    }

    /**
     * @return array{0: Tenant, 1: User, 2: Store}
     */
    private function ownerWithStore(): array
    {
        $owner = User::query()->create([
            'name' => 'Owner Void',
            'email' => 'owner-void@example.com',
            'password' => 'password',
            'role' => 'owner',
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Tenant Void',
            'slug' => 'tenant-void',
            'status' => 'trial',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Void Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $owner->forceFill(['tenant_id' => $tenant->id])->save();

        return [$tenant, $owner, $store];
    }
}
