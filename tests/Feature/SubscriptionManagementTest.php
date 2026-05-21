<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.edition' => 'full']);
    }

    public function test_owner_can_view_subscription_dashboard(): void
    {
        [$owner] = $this->ownerWithTenantAndPlan();

        $this->actingAs($owner)
            ->get(route('subscription.index'))
            ->assertOk()
            ->assertSee('Paket & Full Setup')
            ->assertSee('Pilih Paket');
    }

    public function test_owner_can_switch_subscription_plan_and_subscription_record_is_created(): void
    {
        $starter = SubscriptionPlan::query()->create([
            'code' => 'starter',
            'name' => 'Starter',
            'price' => 49000,
            'max_stores' => 1,
            'max_products' => 500,
            'max_users' => 5,
            'report_retention_days' => 30,
            'features' => ['basic_pos'],
        ]);

        $pro = SubscriptionPlan::query()->create([
            'code' => 'pro',
            'name' => 'Pro',
            'price' => 99000,
            'max_stores' => 5,
            'max_products' => null,
            'max_users' => null,
            'report_retention_days' => null,
            'features' => ['basic_pos', 'priority_support'],
        ]);

        [$owner, $tenant] = $this->ownerWithTenantAndPlan($starter);

        $this->actingAs($owner)->post(route('subscription.plan.update'), [
            'subscription_plan_id' => $pro->id,
        ])->assertRedirect(route('subscription.index'));

        $tenant->refresh();

        $this->assertSame($pro->id, $tenant->subscription_plan_id);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $pro->id,
            'status' => 'active',
            'provider' => 'manual',
        ]);
    }

    public function test_owner_can_update_basic_tenant_settings(): void
    {
        [$owner, $tenant] = $this->ownerWithTenantAndPlan();

        $this->actingAs($owner)->post(route('subscription.tenant.update'), [
            'name' => 'Tenant Baru',
            'status' => 'active',
        ])->assertRedirect(route('subscription.index'));

        $tenant->refresh();

        $this->assertSame('Tenant Baru', $tenant->name);
        $this->assertSame('active', $tenant->status);
    }

    public function test_owner_cannot_suspend_own_tenant_from_subscription_settings(): void
    {
        [$owner, $tenant] = $this->ownerWithTenantAndPlan();

        $this->actingAs($owner)->post(route('subscription.tenant.update'), [
            'name' => 'Tenant Suspended Sendiri',
            'status' => 'suspended',
        ])->assertSessionHasErrors('status');

        $tenant->refresh();

        $this->assertSame('trial', $tenant->status);
    }

    public function test_cashier_cannot_access_subscription_area(): void
    {
        [$owner, $tenant] = $this->ownerWithTenantAndPlan();

        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Subscription',
            'email' => 'cashier-subscription@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $this->actingAs($cashier)->get(route('subscription.index'))->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function ownerWithTenantAndPlan(?SubscriptionPlan $plan = null): array
    {
        if (! $plan) {
            $plan = SubscriptionPlan::query()->create([
                'code' => 'free',
                'name' => 'Gratis',
                'price' => 0,
                'max_stores' => 1,
                'max_products' => 50,
                'max_users' => 2,
                'report_retention_days' => 7,
                'features' => ['basic_pos'],
            ]);
        }

        $owner = User::query()->create([
            'name' => 'Owner Subscription',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'owner',
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $plan->id,
            'name' => 'Tenant Subscription',
            'slug' => fake()->unique()->slug(),
            'status' => 'trial',
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
