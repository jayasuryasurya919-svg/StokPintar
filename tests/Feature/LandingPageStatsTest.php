<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_uses_real_database_stats(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Tenant Landing',
            'slug' => 'tenant-landing',
            'status' => 'active',
        ]);

        $store = Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Store Landing',
            'code' => 'LANDING',
            'is_default' => true,
        ]);

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Landing A',
            'unit' => 'pcs',
            'selling_price' => 10000,
            'stock' => 5,
            'minimum_stock' => 1,
        ]);

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => 'Produk Landing B',
            'unit' => 'pcs',
            'selling_price' => 12000,
            'stock' => 8,
            'minimum_stock' => 1,
        ]);

        Sale::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'invoice_number' => 'LANDING-001',
            'subtotal' => 1250000,
            'total' => 1250000,
            'paid_amount' => 1250000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'paid',
            'sold_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('1</div><div class="stat-label">Toko Terdaftar', false)
            ->assertSee('Rp 1,3jt</div><div class="stat-label">Omzet Diproses', false)
            ->assertSee('2</div><div class="stat-label">Produk Dikelola', false)
            ->assertSee('1</div><div class="stat-label">Tenant Aktif', false)
            ->assertDontSee('1.200+')
            ->assertDontSee('99.9%');
    }
}
