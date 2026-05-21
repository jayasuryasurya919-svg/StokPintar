<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.edition' => 'full']);
    }

    public function test_super_admin_can_view_platform_dashboard(): void
    {
        [$superAdmin, $tenant, $starter] = $this->platformContext();

        $response = $this->actingAs($superAdmin)->get(route('platform.index'));

        $response->assertOk()
            ->assertSee('Admin Platform SaaS')
            ->assertSee($tenant->name)
            ->assertSee($starter->name);
    }

    public function test_super_admin_can_update_tenant_status_and_plan(): void
    {
        [$superAdmin, $tenant] = $this->platformContext();

        $pro = SubscriptionPlan::query()->create([
            'code' => 'pro',
            'name' => 'Pro',
            'price' => 99000,
            'max_stores' => 5,
            'max_products' => null,
            'max_users' => null,
            'report_retention_days' => null,
            'features' => ['priority_support'],
        ]);

        $this->actingAs($superAdmin)->post(route('platform.tenants.update', $tenant), [
            'status' => 'suspended',
            'subscription_plan_id' => $pro->id,
        ])->assertRedirect(route('platform.index'));

        $tenant->refresh();

        $this->assertSame('suspended', $tenant->status);
        $this->assertSame($pro->id, $tenant->subscription_plan_id);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $pro->id,
            'provider' => 'manual',
            'status' => 'paused',
        ]);
    }

    public function test_super_admin_can_delete_only_suspended_tenant(): void
    {
        [$superAdmin, $tenant] = $this->platformContext();

        $this->actingAs($superAdmin)
            ->delete(route('platform.tenants.destroy', $tenant))
            ->assertForbidden();

        $tenant->forceFill(['status' => 'suspended'])->save();

        $this->actingAs($superAdmin)
            ->delete(route('platform.tenants.destroy', $tenant))
            ->assertRedirect(route('platform.index'));

        $this->assertDatabaseMissing('tenants', [
            'id' => $tenant->id,
        ]);
        $this->assertDatabaseHas('platform_audit_logs', [
            'tenant_id' => $tenant->id,
            'actor_user_id' => $superAdmin->id,
            'action' => 'delete_tenant',
            'subject_id' => $tenant->id,
        ]);
    }

    public function test_owner_cannot_delete_tenant_from_platform_routes(): void
    {
        [, $tenant, , $owner] = $this->platformContext();

        $tenant->forceFill(['status' => 'suspended'])->save();

        $this->actingAs($owner)
            ->delete(route('platform.tenants.destroy', $tenant))
            ->assertForbidden();

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
        ]);
    }

    public function test_super_admin_can_create_and_update_subscription_plan(): void
    {
        [$superAdmin, , $starter] = $this->platformContext();

        $this->actingAs($superAdmin)->post(route('platform.plans.store'), [
            'code' => 'growth',
            'name' => 'Growth',
            'price' => 149000,
            'max_stores' => 3,
            'max_products' => 2000,
            'max_users' => 15,
            'report_retention_days' => 90,
            'features' => 'basic_pos, stock_alerts, api_access',
        ])->assertRedirect(route('platform.index'));

        $growth = SubscriptionPlan::query()->where('code', 'growth')->firstOrFail();

        $this->assertSame(['basic_pos', 'stock_alerts', 'api_access'], $growth->features);

        $this->actingAs($superAdmin)->put(route('platform.plans.update', $starter), [
            'code' => 'starter',
            'name' => 'Starter Plus',
            'price' => 59000,
            'max_stores' => 2,
            'max_products' => 750,
            'max_users' => 8,
            'report_retention_days' => 45,
            'features' => 'basic_pos, stock_alerts, excel_export',
        ])->assertRedirect(route('platform.index'));

        $starter->refresh();

        $this->assertSame('Starter Plus', $starter->name);
        $this->assertSame(59000, $starter->price);
        $this->assertSame(['basic_pos', 'stock_alerts', 'excel_export'], $starter->features);
    }

    public function test_owner_cannot_access_platform_routes(): void
    {
        [, $tenant, $starter, $owner] = $this->platformContext();

        $this->actingAs($owner)->get(route('platform.index'))->assertForbidden();
        $this->actingAs($owner)->post(route('platform.tenants.update', $tenant), [
            'status' => 'active',
            'subscription_plan_id' => $starter->id,
        ])->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Tenant, 2: SubscriptionPlan, 3: User}
     */
    private function platformContext(): array
    {
        $superAdmin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $starter = SubscriptionPlan::query()->create([
            'code' => 'starter',
            'name' => 'Starter',
            'price' => 49000,
            'max_stores' => 1,
            'max_products' => 500,
            'max_users' => 5,
            'report_retention_days' => 30,
            'features' => ['basic_pos', 'stock_alerts'],
        ]);

        $owner = User::query()->create([
            'name' => 'Owner Tenant',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $starter->id,
            'name' => 'Tenant Platform',
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

        return [$superAdmin, $tenant, $starter, $owner];
    }
}
