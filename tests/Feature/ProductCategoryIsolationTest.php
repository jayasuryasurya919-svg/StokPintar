<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_product_with_a_new_category_scoped_to_its_tenant(): void
    {
        [$tenant, $owner] = $this->ownerWithStore('Tenant Produk', 'owner-produk@example.com');

        $response = $this->actingAs($owner)->post(route('products.store'), [
            'name' => 'Kopi Susu',
            'sku' => 'KOPI-001',
            'unit' => 'pcs',
            'cost_price' => 10000,
            'selling_price' => 15000,
            'stock' => 20,
            'minimum_stock' => 5,
            'category_name' => 'Minuman',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('products.index'));

        $category = Category::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Minuman')
            ->first();

        $this->assertNotNull($category);

        $product = Product::withoutGlobalScopes()->where('sku', 'KOPI-001')->first();

        $this->assertNotNull($product);
        $this->assertSame($tenant->id, $product->tenant_id);
        $this->assertSame($category->id, $product->category_id);
    }

    public function test_new_category_does_not_reuse_another_tenants_category(): void
    {
        [$tenantA] = $this->ownerWithStore('Tenant A', 'owner-a@example.com');
        [$tenantB, $ownerB] = $this->ownerWithStore('Tenant B', 'owner-b@example.com');

        $foreignCategory = Category::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Minuman',
        ]);

        $this->actingAs($ownerB)->post(route('products.store'), [
            'name' => 'Teh Botol',
            'sku' => 'TEH-001',
            'unit' => 'pcs',
            'cost_price' => 5000,
            'selling_price' => 8000,
            'stock' => 30,
            'minimum_stock' => 5,
            'category_name' => 'Minuman',
            'is_active' => 1,
        ])->assertRedirect(route('products.index'));

        $tenantBCategory = Category::withoutGlobalScopes()
            ->where('tenant_id', $tenantB->id)
            ->where('name', 'Minuman')
            ->first();

        $product = Product::withoutGlobalScopes()->where('sku', 'TEH-001')->first();

        $this->assertNotNull($tenantBCategory);
        $this->assertNotSame($foreignCategory->id, $tenantBCategory->id);
        $this->assertSame($tenantBCategory->id, $product?->category_id);
    }

    /**
     * @return array{0: Tenant, 1: User}
     */
    private function ownerWithStore(string $tenantName, string $email): array
    {
        $owner = User::query()->create([
            'name' => $tenantName.' Owner',
            'email' => $email,
            'password' => 'password',
            'role' => 'owner',
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'name' => $tenantName,
            'slug' => strtolower(str_replace(' ', '-', $tenantName)),
            'status' => 'trial',
        ]);

        Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => $tenantName.' Store',
            'code' => 'MAIN-'.$tenant->id,
            'is_default' => true,
        ]);

        $owner->forceFill(['tenant_id' => $tenant->id])->save();

        return [$tenant, $owner];
    }
}
