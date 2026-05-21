<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_midtrans_provider_creates_pending_payment_and_redirects_to_snap(): void
    {
        config([
            'services.payment.provider' => 'midtrans',
            'services.payment.midtrans.server_key' => 'midtrans-server-key',
            'services.payment.midtrans.is_production' => false,
        ]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token',
            ], 201),
        ]);

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
        ])->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token');

        $tenant->refresh();

        $this->assertSame($starter->id, $tenant->subscription_plan_id);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $pro->id,
            'status' => 'pending',
            'provider' => 'midtrans',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['gross_amount'] === 99000
            && $request['item_details'][0]['id'] === 'pro');
    }

    public function test_midtrans_notification_activates_paid_subscription(): void
    {
        config(['services.payment.midtrans.server_key' => 'midtrans-server-key']);

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

        [, $tenant] = $this->ownerWithTenantAndPlan($starter);
        $orderId = 'SP-'.$tenant->id.'-'.$pro->id.'-20260521131800';
        $grossAmount = '99000.00';

        Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $pro->id,
            'status' => 'pending',
            'provider' => 'midtrans',
            'provider_reference' => $orderId,
            'metadata' => ['order_id' => $orderId, 'amount' => 99000],
        ]);

        $this->post(route('payments.midtrans.notification'), [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $orderId.'200'.$grossAmount.'midtrans-server-key'),
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-transaction-id',
        ])->assertOk()->assertJson(['status' => 'ok']);

        $tenant->refresh();

        $this->assertSame($pro->id, $tenant->subscription_plan_id);
        $this->assertSame('active', $tenant->status);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $pro->id,
            'status' => 'active',
            'provider' => 'midtrans',
            'provider_reference' => $orderId,
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
