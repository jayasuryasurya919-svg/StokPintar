<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiteEditionAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_full_tenant_features(): void
    {
        config(['app.edition' => 'full']);

        [$owner] = $this->ownerWithTenant();

        $this->actingAs($owner)->get(route('users.index'))->assertOk();
        $this->actingAs($owner)->get(route('subscription.index'))->assertOk();
        $this->actingAs($owner)->get(route('business-profile.edit'))->assertOk();
        $this->actingAs($owner)->get(route('platform.index'))->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Tenant}
     */
    private function ownerWithTenant(): array
    {
        $plan = SubscriptionPlan::query()->create([
            'code' => 'starter',
            'name' => 'Starter',
            'price' => 49000,
            'max_stores' => 1,
            'max_products' => 500,
            'max_users' => 5,
            'report_retention_days' => 30,
            'features' => ['basic_pos'],
        ]);

        $owner = User::query()->create([
            'name' => 'Owner Lite',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => User::ROLE_OWNER,
        ]);

        $tenant = Tenant::query()->create([
            'owner_id' => $owner->id,
            'subscription_plan_id' => $plan->id,
            'name' => 'Tenant Lite',
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
