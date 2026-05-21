<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_scoped_models_return_no_rows_without_a_resolved_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Tenant Satu',
            'slug' => 'tenant-satu',
            'status' => 'trial',
        ]);

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Produk Bocor',
            'unit' => 'pcs',
            'cost_price' => 1000,
            'selling_price' => 1500,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        auth()->logout();

        $this->assertSame(0, Product::query()->count());
        $this->assertNull(Product::query()->first());
    }

    public function test_authenticated_user_only_sees_products_from_its_own_tenant(): void
    {
        $tenantA = Tenant::query()->create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'status' => 'trial',
        ]);

        $tenantB = Tenant::query()->create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'status' => 'trial',
        ]);

        $user = User::query()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Owner A',
            'email' => 'owner-a@example.com',
            'password' => 'password',
            'role' => 'owner',
        ]);

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Produk Tenant A',
            'unit' => 'pcs',
            'cost_price' => 1000,
            'selling_price' => 1500,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Produk Tenant B',
            'unit' => 'pcs',
            'cost_price' => 2000,
            'selling_price' => 2500,
            'stock' => 5,
            'minimum_stock' => 1,
        ]);

        $this->actingAs($user);

        $this->assertSame(['Produk Tenant A'], Product::query()->pluck('name')->all());
    }
}
