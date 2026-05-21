<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.edition' => 'full']);
    }

    public function test_owner_can_create_cashier_for_same_tenant(): void
    {
        [$owner, $tenant] = $this->ownerWithTenant();

        $response = $this->actingAs($owner)->post(route('users.store'), [
            'name' => 'Kasir Baru',
            'email' => 'kasir-baru@example.com',
            'role' => 'cashier',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'kasir-baru@example.com',
            'role' => 'cashier',
        ]);
    }

    public function test_owner_can_update_cashier_in_same_tenant(): void
    {
        [$owner, $tenant] = $this->ownerWithTenant();

        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Lama',
            'email' => 'kasir-lama@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $response = $this->actingAs($owner)->put(route('users.update', $cashier), [
            'name' => 'Kasir Update',
            'email' => 'kasir-update@example.com',
            'role' => 'cashier',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $cashier->id,
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Update',
            'email' => 'kasir-update@example.com',
        ]);
    }

    public function test_owner_can_delete_cashier_but_not_self(): void
    {
        [$owner, $tenant] = $this->ownerWithTenant();

        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Hapus',
            'email' => 'kasir-hapus@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $this->actingAs($owner)->delete(route('users.destroy', $cashier))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $cashier->id,
        ]);

        $this->actingAs($owner)->from(route('users.index'))->delete(route('users.destroy', $owner))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('user');
    }

    public function test_cashier_is_forbidden_from_user_management_routes(): void
    {
        [$owner, $tenant] = $this->ownerWithTenant();

        $cashier = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Kasir Biasa',
            'email' => 'kasir-biasa@example.com',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $this->actingAs($cashier)->get(route('users.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('users.create'))->assertForbidden();
    }

    public function test_manager_can_create_operational_user_but_not_owner_or_manager(): void
    {
        [$owner, $tenant] = $this->ownerWithTenant();

        $manager = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Manager Tim',
            'email' => 'manager-tim@example.com',
            'password' => 'password',
            'role' => User::ROLE_MANAGER,
        ]);

        $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Viewer Baru',
            'email' => 'viewer-baru@example.com',
            'role' => User::ROLE_VIEWER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'viewer-baru@example.com',
            'role' => User::ROLE_VIEWER,
        ]);

        $this->actingAs($manager)->from(route('users.create'))->post(route('users.store'), [
            'name' => 'Owner Ilegal',
            'email' => 'owner-ilegal@example.com',
            'role' => User::ROLE_OWNER,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('role');

        $this->actingAs($manager)->get(route('users.edit', $owner))->assertForbidden();
    }

    public function test_user_store_access_must_belong_to_actor_tenant(): void
    {
        [$owner, $tenant] = $this->ownerWithTenant();

        $otherTenant = Tenant::query()->create([
            'name' => 'Tenant Lain',
            'slug' => fake()->unique()->slug(),
            'status' => 'trial',
        ]);

        $foreignStore = Store::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Foreign Store',
            'code' => 'FOREIGN',
            'is_default' => true,
        ]);

        $this->actingAs($owner)->from(route('users.create'))->post(route('users.store'), [
            'name' => 'Kasir Store Asing',
            'email' => 'kasir-store-asing@example.com',
            'role' => 'cashier',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'stores' => [$foreignStore->id],
        ])->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('stores.0');

        $this->assertDatabaseMissing('users', [
            'tenant_id' => $tenant->id,
            'email' => 'kasir-store-asing@example.com',
        ]);
    }

    public function test_owner_cannot_add_user_when_plan_limit_is_reached(): void
    {
        $plan = SubscriptionPlan::query()->create([
            'code' => 'starter-limit',
            'name' => 'Starter Limit',
            'price' => 0,
            'max_stores' => 1,
            'max_products' => 50,
            'max_users' => 1,
            'report_retention_days' => 7,
        ]);

        [$owner] = $this->ownerWithTenant($plan);

        $this->actingAs($owner)->from(route('users.create'))->post(route('users.store'), [
            'name' => 'Kasir Limit',
            'email' => 'kasir-limit@example.com',
            'role' => 'cashier',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('plan');
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function ownerWithTenant(?SubscriptionPlan $plan = null): array
    {
        $owner = User::query()->create([
            'name' => 'Owner Tim',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'owner',
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $plan?->id,
            'name' => 'Tenant Tim',
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
