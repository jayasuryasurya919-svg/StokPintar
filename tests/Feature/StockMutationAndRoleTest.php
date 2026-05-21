<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMutation;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMutationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_record_stock_in_mutation(): void
    {
        [$tenant, $owner, $store] = $this->userWithTenantAndStore('owner', 'owner-mutation@example.com');

        $product = $this->productFor($tenant->id, $store->id, 10);

        $response = $this->from(route('products.index'))
            ->actingAs($owner)
            ->post(route('products.stock.store', $product), [
                'type' => 'in',
                'quantity' => 5,
                'notes' => 'Restock supplier',
            ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame(15, $product->stock);
        $this->assertDatabaseHas('stock_mutations', [
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'type' => 'in',
            'quantity' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
        ]);
    }

    public function test_owner_cannot_record_stock_out_below_zero(): void
    {
        [$tenant, $owner, $store] = $this->userWithTenantAndStore('owner', 'owner-negative@example.com');

        $product = $this->productFor($tenant->id, $store->id, 2);

        $response = $this->from(route('products.index'))
            ->actingAs($owner)
            ->post(route('products.stock.store', $product), [
                'type' => 'out',
                'quantity' => 5,
                'notes' => 'Stok rusak',
            ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasErrors('quantity');

        $product->refresh();

        $this->assertSame(2, $product->stock);
        $this->assertSame(0, StockMutation::withoutGlobalScopes()->count());
    }

    public function test_cashier_is_forbidden_from_owner_product_pages_and_stock_mutation(): void
    {
        [$tenant, $cashier, $store] = $this->userWithTenantAndStore('cashier', 'cashier-role@example.com');

        $product = $this->productFor($tenant->id, $store->id, 6);

        $this->actingAs($cashier)->get(route('products.index'))->assertForbidden();
        $this->actingAs($cashier)->post(route('products.stock.store', $product), [
            'type' => 'in',
            'quantity' => 1,
        ])->assertForbidden();
    }

    /**
     * @return array{0: Tenant, 1: User, 2: Store}
     */
    private function userWithTenantAndStore(string $role, string $email): array
    {
        $user = User::query()->create([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => 'password',
            'role' => $role,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $user->id,
            'name' => ucfirst($role).' Tenant',
            'slug' => str_replace('@', '-', $email),
            'status' => 'trial',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $user->forceFill(['tenant_id' => $tenant->id])->save();

        return [$tenant, $user, $store];
    }

    private function productFor(int $tenantId, int $storeId, int $stock): Product
    {
        return Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenantId,
            'store_id' => $storeId,
            'name' => 'Produk Mutasi',
            'sku' => 'MUT-'.fake()->unique()->numerify('###'),
            'unit' => 'pcs',
            'cost_price' => 5000,
            'selling_price' => 8000,
            'stock' => $stock,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);
    }
}
