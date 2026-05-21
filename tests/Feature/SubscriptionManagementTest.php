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

    public function test_owner_can_switch_to_free_plan_without_payment_gateway(): void
    {
        $free = SubscriptionPlan::query()->create([
            'code' => 'free',
            'name' => 'Free',
            'price' => 0,
            'max_stores' => 1,
            'max_products' => 50,
            'max_users' => 2,
            'report_retention_days' => 7,
            'features' => ['basic_pos'],
        ]);

        $paidPlan = SubscriptionPlan::query()->create([
            'code' => 'pro',
            'name' => 'Pro',
            'price' => 99000,
            'max_stores' => 5,
            'max_products' => null,
            'max_users' => null,
            'report_retention_days' => null,
            'features' => ['basic_pos', 'priority_support'],
        ]);

        [$owner, $tenant] = $this->ownerWithTenantAndPlan($paidPlan);

        $this->actingAs($owner)->post(route('subscription.plan.update'), [
            'subscription_plan_id' => $free->id,
        ])->assertRedirect(route('subscription.index'));

        $tenant->refresh();

        $this->assertSame($free->id, $tenant->subscription_plan_id);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $free->id,
            'status' => 'active',
            'provider' => 'manual',
        ]);
    }

    public function test_owner_cannot_upgrade_to_paid_plan_without_payment_gateway(): void
    {
        config(['services.payment.provider' => 'manual']);

        $free = SubscriptionPlan::query()->create([
            'code' => 'free',
            'name' => 'Free',
            'price' => 0,
            'max_stores' => 1,
            'max_products' => 50,
            'max_users' => 2,
            'report_retention_days' => 7,
            'features' => ['basic_pos'],
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

        [$owner, $tenant] = $this->ownerWithTenantAndPlan($free);

        $this->actingAs($owner)->post(route('subscription.plan.update'), [
            'subscription_plan_id' => $starter->id,
        ])->assertSessionHasErrors('subscription_plan_id');

        $tenant->refresh();

        $this->assertSame($free->id, $tenant->subscription_plan_id);
        $this->assertDatabaseMissing('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $starter->id,
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

    public function test_xendit_provider_creates_pending_invoice_and_redirects_to_invoice_url(): void
    {
        config([
            'services.payment.provider' => 'xendit',
            'services.payment.xendit.secret_key' => 'xendit-secret-key',
            'services.payment.xendit.callback_token' => 'xendit-callback-token',
        ]);

        Http::fake([
            'api.xendit.co/*' => Http::response([
                'id' => 'xendit-invoice-id',
                'invoice_url' => 'https://checkout.xendit.co/web/xendit-invoice-id',
            ], 200),
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

        $business = SubscriptionPlan::query()->create([
            'code' => 'business',
            'name' => 'Business',
            'price' => 199000,
            'max_stores' => null,
            'max_products' => null,
            'max_users' => null,
            'report_retention_days' => null,
            'features' => ['basic_pos', 'priority_support'],
        ]);

        [$owner, $tenant] = $this->ownerWithTenantAndPlan($starter);

        $this->actingAs($owner)->post(route('subscription.plan.update'), [
            'subscription_plan_id' => $business->id,
        ])->assertRedirect('https://checkout.xendit.co/web/xendit-invoice-id');

        $tenant->refresh();

        $this->assertSame($starter->id, $tenant->subscription_plan_id);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $business->id,
            'status' => 'pending',
            'provider' => 'xendit',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.xendit.co/v2/invoices'
            && $request['amount'] === 199000
            && $request['items'][0]['name'] === 'Langganan StokPintar Business');
    }

    public function test_xendit_callback_activates_paid_subscription(): void
    {
        config(['services.payment.xendit.callback_token' => 'xendit-callback-token']);

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

        $business = SubscriptionPlan::query()->create([
            'code' => 'business',
            'name' => 'Business',
            'price' => 199000,
            'max_stores' => null,
            'max_products' => null,
            'max_users' => null,
            'report_retention_days' => null,
            'features' => ['basic_pos', 'priority_support'],
        ]);

        [, $tenant] = $this->ownerWithTenantAndPlan($starter);
        $externalId = 'SP-'.$tenant->id.'-'.$business->id.'-20260521131800';

        Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $business->id,
            'status' => 'pending',
            'provider' => 'xendit',
            'provider_reference' => $externalId,
            'metadata' => ['external_id' => $externalId, 'amount' => 199000],
        ]);

        $this->withHeader('x-callback-token', 'xendit-callback-token')
            ->post(route('payments.xendit.callback'), [
                'external_id' => $externalId,
                'status' => 'PAID',
                'id' => 'xendit-invoice-id',
                'payment_method' => 'QRIS',
                'paid_at' => '2026-05-21T06:18:00.000Z',
            ])->assertOk()->assertJson(['status' => 'ok']);

        $tenant->refresh();

        $this->assertSame($business->id, $tenant->subscription_plan_id);
        $this->assertSame('active', $tenant->status);
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $business->id,
            'status' => 'active',
            'provider' => 'xendit',
            'provider_reference' => $externalId,
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
