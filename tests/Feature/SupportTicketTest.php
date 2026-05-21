<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.edition' => 'full']);
    }

    public function test_owner_with_priority_support_creates_priority_ticket(): void
    {
        [$owner, $tenant] = $this->tenantOwner(['priority_support']);

        $this->actingAs($owner)->post(route('support.store'), [
            'subject' => 'POS tidak bisa scan barcode',
            'message' => 'Kamera sudah aktif, tetapi barcode tidak terbaca.',
        ])->assertRedirect(route('support.index'));

        $this->assertDatabaseHas('support_tickets', [
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'subject' => 'POS tidak bisa scan barcode',
            'priority' => 'priority',
            'status' => 'open',
        ]);
    }

    public function test_owner_without_priority_support_creates_regular_ticket(): void
    {
        [$owner, $tenant] = $this->tenantOwner(['basic_pos']);

        $this->actingAs($owner)->post(route('support.store'), [
            'subject' => 'Butuh bantuan laporan',
            'message' => 'Saya ingin cek laporan harian.',
        ])->assertRedirect(route('support.index'));

        $this->assertDatabaseHas('support_tickets', [
            'tenant_id' => $tenant->id,
            'priority' => 'normal',
            'status' => 'open',
        ]);
    }

    public function test_platform_admin_can_update_support_ticket_status(): void
    {
        [$owner, $tenant] = $this->tenantOwner(['priority_support']);
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'support-admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $ticket = SupportTicket::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'priority' => 'priority',
            'status' => 'open',
            'subject' => 'Butuh bantuan cepat',
            'message' => 'Tolong cek setup cabang.',
        ]);

        $this->actingAs($admin)->put(route('support.update', $ticket->id), [
            'status' => 'resolved',
            'admin_note' => 'Sudah ditangani oleh admin platform.',
        ])->assertRedirect(route('support.index'));

        $ticket->refresh();

        $this->assertSame('resolved', $ticket->status);
        $this->assertSame('Sudah ditangani oleh admin platform.', $ticket->admin_note);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_support_page_filters_priority_tickets(): void
    {
        [$owner, $tenant] = $this->tenantOwner(['priority_support']);

        SupportTicket::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'priority' => 'priority',
            'status' => 'open',
            'subject' => 'Tiket prioritas',
            'message' => 'Butuh bantuan cepat.',
        ]);

        SupportTicket::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'priority' => 'normal',
            'status' => 'open',
            'subject' => 'Tiket regular',
            'message' => 'Bantuan biasa.',
        ]);

        $this->actingAs($owner)
            ->get(route('support.index', ['filter' => 'priority']))
            ->assertOk()
            ->assertSee('Tiket prioritas')
            ->assertDontSee('Tiket regular')
            ->assertSee('Priority');
    }

    public function test_cashier_cannot_access_support_area(): void
    {
        [, $tenant] = $this->tenantOwner(['priority_support']);
        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Support',
            'email' => 'cashier-support@example.com',
            'password' => 'password',
            'role' => User::ROLE_CASHIER,
        ]);

        $this->actingAs($cashier)->get(route('support.index'))->assertForbidden();
    }

    /**
     * @param array<int, string> $features
     * @return array{0: User, 1: Tenant}
     */
    private function tenantOwner(array $features): array
    {
        $plan = SubscriptionPlan::query()->create([
            'code' => 'support-plan-'.fake()->unique()->numberBetween(100, 999),
            'name' => 'Support Plan',
            'price' => 199000,
            'max_stores' => null,
            'max_products' => null,
            'max_users' => null,
            'report_retention_days' => null,
            'features' => $features,
        ]);

        $owner = User::query()->create([
            'name' => 'Owner Support',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $plan->id,
            'name' => 'Tenant Support',
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        Store::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Store',
            'code' => 'MAIN',
            'is_default' => true,
        ]);

        $owner->forceFill(['tenant_id' => $tenant->id])->save();

        return [$owner, $tenant];
    }
}
