<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMutation;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_history_rejects_invalid_filters(): void
    {
        [$owner] = $this->stockHistoryContext();

        $this->actingAs($owner)
            ->get(route('stock-history.index', [
                'type' => 'not-a-type',
                'date_from' => '2026-05-20',
                'date_to' => '2026-05-19',
            ]))
            ->assertSessionHasErrors(['type', 'date_to']);
    }

    public function test_stock_history_rejects_product_from_other_tenant(): void
    {
        [$owner] = $this->stockHistoryContext();
        [, , $foreignProduct] = $this->stockHistoryContext('other');

        $this->actingAs($owner)
            ->get(route('stock-history.index', [
                'product_id' => $foreignProduct->id,
            ]))
            ->assertSessionHasErrors('product_id');
    }

    /**
     * @return array{0: User, 1: Store, 2: Product}
     */
    private function stockHistoryContext(string $suffix = 'main'): array
    {
        $owner = User::query()->create([
            'name' => 'Owner Stock '.$suffix,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'name' => 'Tenant Stock '.$suffix,
            'slug' => fake()->unique()->slug(),
            'status' => 'trial',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Stock Store '.$suffix,
            'code' => strtoupper($suffix),
            'is_default' => true,
        ]);

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Stock '.$suffix,
            'sku' => 'STOCK-'.fake()->unique()->numerify('###'),
            'unit' => 'pcs',
            'cost_price' => 5000,
            'selling_price' => 9000,
            'stock' => 5,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        StockMutation::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'product_id' => $product->id,
            'user_id' => $owner->id,
            'type' => StockMutation::TYPE_IN,
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
        ]);

        $owner->forceFill(['tenant_id' => $tenant->id])->save();

        return [$owner, $store, $product];
    }
}
