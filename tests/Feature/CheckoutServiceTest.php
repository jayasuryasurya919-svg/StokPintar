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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_sale_items_and_stock_mutation_and_reduces_stock(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();
        $this->actingAs($cashier);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Kopi Arabica',
            'sku' => 'KOPI-001',
            'unit' => 'pcs',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'stock' => 10,
            'minimum_stock' => 2,
            'is_active' => true,
        ]);

        $sale = app(CheckoutService::class)->checkout(
            tenantId: $tenant->id,
            cashierId: $cashier->id,
            storeId: $store->id,
            items: [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            paidAmount: 50000,
        );

        $product->refresh();

        $this->assertSame(8, $product->stock);
        $this->assertSame(30000, $sale->subtotal);
        $this->assertSame(30000, $sale->total);
        $this->assertSame(20000, $sale->change_amount);
        $this->assertCount(1, $sale->fresh('items')->items);
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'tenant_id' => $tenant->id,
            'cashier_id' => $cashier->id,
            'payment_method' => 'cash',
        ]);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'line_total' => 30000,
        ]);
        $this->assertDatabaseHas('stock_mutations', [
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'user_id' => $cashier->id,
            'type' => StockMutation::TYPE_SALE,
            'quantity' => -2,
            'stock_before' => 10,
            'stock_after' => 8,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_checkout_rejects_when_paid_amount_is_less_than_subtotal(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();
        $this->actingAs($cashier);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Teh Melati',
            'sku' => 'TEH-001',
            'unit' => 'pcs',
            'cost_price' => 5000,
            'selling_price' => 12000,
            'stock' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        try {
            app(CheckoutService::class)->checkout(
                tenantId: $tenant->id,
                cashierId: $cashier->id,
                storeId: $store->id,
                items: [
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
                paidAmount: 10000,
            );

            $this->fail('Checkout should have thrown a validation exception for insufficient payment.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('paid_amount', $exception->errors());
        }

        $product->refresh();

        $this->assertSame(10, $product->stock);
        $this->assertSame(0, Sale::withoutGlobalScopes()->count());
        $this->assertSame(0, StockMutation::withoutGlobalScopes()->count());
    }

    public function test_checkout_rejects_when_stock_is_insufficient(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();
        $this->actingAs($cashier);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Brownies',
            'sku' => 'BRW-001',
            'unit' => 'pcs',
            'cost_price' => 9000,
            'selling_price' => 15000,
            'stock' => 1,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        try {
            app(CheckoutService::class)->checkout(
                tenantId: $tenant->id,
                cashierId: $cashier->id,
                storeId: $store->id,
                items: [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
                paidAmount: 50000,
            );

            $this->fail('Checkout should have thrown a validation exception for insufficient stock.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items', $exception->errors());
        }

        $product->refresh();

        $this->assertSame(1, $product->stock);
        $this->assertSame(0, Sale::withoutGlobalScopes()->count());
        $this->assertSame(0, StockMutation::withoutGlobalScopes()->count());
    }

    public function test_checkout_menu_reduces_recipe_ingredient_stock(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();
        $this->actingAs($cashier);

        $coffee = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Biji Kopi',
            'sku' => 'ING-KOPI',
            'product_type' => Product::TYPE_STOCK,
            'unit' => 'gram',
            'cost_price' => 100,
            'selling_price' => 0,
            'stock' => 100,
            'minimum_stock' => 10,
            'is_active' => false,
        ]);

        $milk = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Susu',
            'sku' => 'ING-SUSU',
            'product_type' => Product::TYPE_STOCK,
            'unit' => 'ml',
            'cost_price' => 20,
            'selling_price' => 0,
            'stock' => 500,
            'minimum_stock' => 100,
            'is_active' => false,
        ]);

        $menu = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Es Kopi Susu',
            'sku' => 'MENU-KOPSU',
            'product_type' => Product::TYPE_MENU,
            'unit' => 'porsi',
            'cost_price' => 8000,
            'selling_price' => 22000,
            'stock' => 0,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $menu->recipes()->createMany([
            ['tenant_id' => $tenant->id, 'ingredient_product_id' => $coffee->id, 'quantity' => 18],
            ['tenant_id' => $tenant->id, 'ingredient_product_id' => $milk->id, 'quantity' => 150],
        ]);

        $sale = app(CheckoutService::class)->checkout(
            tenantId: $tenant->id,
            cashierId: $cashier->id,
            storeId: $store->id,
            items: [
                ['product_id' => $menu->id, 'quantity' => 2],
            ],
            paidAmount: 50000,
        );

        $coffee->refresh();
        $milk->refresh();
        $menu->refresh();

        $this->assertSame(64, $coffee->stock);
        $this->assertSame(200, $milk->stock);
        $this->assertSame(0, $menu->stock);
        $this->assertSame(44000, $sale->total);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $menu->id,
            'quantity' => 2,
            'line_total' => 44000,
        ]);
        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $coffee->id,
            'quantity' => -36,
            'reference_id' => $sale->id,
        ]);
        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $milk->id,
            'quantity' => -300,
            'reference_id' => $sale->id,
        ]);
    }

    public function test_checkout_rejects_product_from_different_store(): void
    {
        [$tenant, $cashier, $store] = $this->tenantCashierAndStore();
        $this->actingAs($cashier);

        $otherStore = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Cabang Lain',
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

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        try {
            app(CheckoutService::class)->checkout(
                tenantId: $tenant->id,
                cashierId: $cashier->id,
                storeId: $store->id,
                items: [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
                paidAmount: 50000,
            );
        } finally {
            $this->assertSame(0, Sale::withoutGlobalScopes()->count());
            $this->assertSame(0, StockMutation::withoutGlobalScopes()->count());
        }
    }

    /**
     * @return array{0: Tenant, 1: User, 2: Store}
     */
    private function tenantCashierAndStore(): array
    {
        $cashier = User::query()->create([
            'name' => 'Kasir Demo',
            'email' => 'cashier@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $cashier->id,
            'name' => 'Tenant POS',
            'slug' => 'tenant-pos',
            'status' => 'trial',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $cashier->forceFill(['tenant_id' => $tenant->id])->save();

        return [$tenant, $cashier, $store];
    }
}
