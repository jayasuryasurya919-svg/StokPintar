<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant?->load(['subscriptionPlan', 'stores', 'products', 'users']);

        $plans = SubscriptionPlan::query()->orderBy('price')->get();

        $latestSubscription = Subscription::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest('starts_at')
            ->latest('created_at')
            ->first();

        return view('subscription.index', compact('tenant', 'plans', 'latestSubscription'));
    }

    public function updatePlan(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_if(! $tenant, 404);

        $data = $request->validate([
            'subscription_plan_id' => ['required', 'integer', Rule::exists('subscription_plans', 'id')],
        ]);

        $plan = SubscriptionPlan::query()->findOrFail($data['subscription_plan_id']);

        if ($this->shouldUseMidtrans($plan)) {
            return $this->createMidtransPayment($request, $tenant, $plan);
        }

        if ($this->shouldUseXendit($plan)) {
            return $this->createXenditPayment($request, $tenant, $plan);
        }

        if ($plan->price > 0) {
            return back()->withErrors([
                'subscription_plan_id' => 'Payment gateway belum aktif. Pilih Midtrans/Xendit di .env sebelum upgrade ke paket berbayar.',
            ]);
        }

        $this->activateSubscription($tenant, $plan, 'manual', null, [
            'changed_by' => $request->user()->id,
            'source' => 'owner-panel',
        ]);

        return redirect()->route('subscription.index')->with('status', "Paket berhasil diubah ke {$plan->name}.");
    }

    public function midtransNotification(Request $request): JsonResponse
    {
        $serverKey = (string) config('services.payment.midtrans.server_key');
        abort_if($serverKey === '', 503, 'Midtrans is not configured.');

        $data = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'string'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'fraud_status' => ['nullable', 'string'],
            'payment_type' => ['nullable', 'string'],
            'transaction_id' => ['nullable', 'string'],
        ]);

        $expectedSignature = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].$serverKey);
        abort_unless(hash_equals($expectedSignature, $data['signature_key']), 403, 'Invalid Midtrans signature.');

        $subscription = Subscription::withoutGlobalScopes()
            ->where('provider', 'midtrans')
            ->where('provider_reference', $data['order_id'])
            ->first();

        if (! $subscription) {
            return response()->json(['status' => 'ignored']);
        }

        $status = $data['transaction_status'];
        $fraudStatus = $data['fraud_status'] ?? null;

        if (in_array($status, ['capture', 'settlement'], true) && $fraudStatus !== 'deny') {
            $tenant = Tenant::withoutGlobalScopes()->find($subscription->tenant_id);
            $plan = SubscriptionPlan::query()->find($subscription->subscription_plan_id);

            if ($tenant && $plan) {
                $this->activateSubscription($tenant, $plan, 'midtrans', $data['order_id'], array_merge($subscription->metadata ?? [], [
                    'payment_type' => $data['payment_type'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'paid_via_webhook' => true,
                ]));
            }
        } elseif (in_array($status, ['cancel', 'deny', 'expire'], true)) {
            $subscription->update([
                'status' => 'failed',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'midtrans_status' => $status,
                    'fraud_status' => $fraudStatus,
                ]),
            ]);
        } else {
            $subscription->update([
                'status' => 'pending',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'midtrans_status' => $status,
                    'fraud_status' => $fraudStatus,
                ]),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function xenditCallback(Request $request): JsonResponse
    {
        $callbackToken = (string) config('services.payment.xendit.callback_token');
        abort_if($callbackToken === '', 503, 'Xendit is not configured.');
        abort_unless(hash_equals($callbackToken, (string) $request->header('x-callback-token')), 403, 'Invalid Xendit callback token.');

        $data = $request->validate([
            'external_id' => ['required', 'string'],
            'status' => ['required', 'string'],
            'id' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'string'],
        ]);

        $subscription = Subscription::withoutGlobalScopes()
            ->where('provider', 'xendit')
            ->where('provider_reference', $data['external_id'])
            ->first();

        if (! $subscription) {
            return response()->json(['status' => 'ignored']);
        }

        $status = strtoupper($data['status']);

        if (in_array($status, ['PAID', 'SETTLED'], true)) {
            $tenant = Tenant::withoutGlobalScopes()->find($subscription->tenant_id);
            $plan = SubscriptionPlan::query()->find($subscription->subscription_plan_id);

            if ($tenant && $plan) {
                $this->activateSubscription($tenant, $plan, 'xendit', $data['external_id'], array_merge($subscription->metadata ?? [], [
                    'invoice_id' => $data['id'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                    'paid_at' => $data['paid_at'] ?? null,
                    'paid_via_webhook' => true,
                ]));
            }
        } elseif (in_array($status, ['EXPIRED', 'FAILED'], true)) {
            $subscription->update([
                'status' => 'failed',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'xendit_status' => $status,
                ]),
            ]);
        } else {
            $subscription->update([
                'status' => 'pending',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'xendit_status' => $status,
                ]),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function updateTenant(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_if(! $tenant, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['trial', 'active'])],
        ]);

        $tenant->update($data);

        return redirect()->route('subscription.index')->with('status', 'Pengaturan tenant berhasil diperbarui.');
    }

    private function activateSubscription(Tenant $tenant, SubscriptionPlan $plan, string $provider, ?string $providerReference, array $metadata): Subscription
    {
        $tenant->update([
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $payload = [
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'provider' => $provider,
            'provider_reference' => $providerReference,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'metadata' => $metadata,
        ];

        if ($providerReference === null) {
            return Subscription::withoutGlobalScopes()->create($payload);
        }

        return Subscription::withoutGlobalScopes()->updateOrCreate([
            'provider' => $provider,
            'provider_reference' => $providerReference,
        ], $payload);
    }

    private function shouldUseMidtrans(SubscriptionPlan $plan): bool
    {
        return $plan->price > 0
            && config('services.payment.provider') === 'midtrans'
            && filled(config('services.payment.midtrans.server_key'));
    }

    private function shouldUseXendit(SubscriptionPlan $plan): bool
    {
        return $plan->price > 0
            && config('services.payment.provider') === 'xendit'
            && filled(config('services.payment.xendit.secret_key'));
    }

    private function createMidtransPayment(Request $request, Tenant $tenant, SubscriptionPlan $plan): RedirectResponse
    {
        $orderId = 'SP-'.$tenant->id.'-'.$plan->id.'-'.now()->format('YmdHis');
        $amount = (int) $plan->price;

        $subscription = Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
            'provider' => 'midtrans',
            'provider_reference' => $orderId,
            'starts_at' => null,
            'ends_at' => null,
            'metadata' => [
                'changed_by' => $request->user()->id,
                'source' => 'owner-panel',
                'amount' => $amount,
                'order_id' => $orderId,
            ],
        ]);

        $response = Http::withBasicAuth((string) config('services.payment.midtrans.server_key'), '')
            ->acceptJson()
            ->post($this->midtransSnapEndpoint(), [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
                'item_details' => [[
                    'id' => $plan->code,
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => 'Langganan StokPintar '.$plan->name,
                ]],
                'callbacks' => [
                    'finish' => route('subscription.index'),
                ],
                'notification_url' => route('payments.midtrans.notification'),
            ]);

        if ($response->failed() || ! $response->json('redirect_url')) {
            $subscription->update([
                'status' => 'failed',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'midtrans_error' => $response->json() ?: $response->body(),
                ]),
            ]);

            return back()->withErrors(['subscription_plan_id' => 'Payment gateway belum bisa membuat transaksi. Cek konfigurasi Midtrans.']);
        }

        $subscription->update([
            'metadata' => array_merge($subscription->metadata ?? [], [
                'snap_token' => $response->json('token'),
                'redirect_url' => $response->json('redirect_url'),
            ]),
        ]);

        return redirect()->away($response->json('redirect_url'));
    }

    private function midtransSnapEndpoint(): string
    {
        return config('services.payment.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function createXenditPayment(Request $request, Tenant $tenant, SubscriptionPlan $plan): RedirectResponse
    {
        $externalId = 'SP-'.$tenant->id.'-'.$plan->id.'-'.now()->format('YmdHis');
        $amount = (int) $plan->price;

        $subscription = Subscription::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
            'provider' => 'xendit',
            'provider_reference' => $externalId,
            'starts_at' => null,
            'ends_at' => null,
            'metadata' => [
                'changed_by' => $request->user()->id,
                'source' => 'owner-panel',
                'amount' => $amount,
                'external_id' => $externalId,
            ],
        ]);

        $response = Http::withBasicAuth((string) config('services.payment.xendit.secret_key'), '')
            ->acceptJson()
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $externalId,
                'amount' => $amount,
                'currency' => 'IDR',
                'payer_email' => $request->user()->email,
                'description' => 'Langganan StokPintar '.$plan->name,
                'success_redirect_url' => route('subscription.index'),
                'failure_redirect_url' => route('subscription.index'),
                'callback_url' => route('payments.xendit.callback'),
                'items' => [[
                    'name' => 'Langganan StokPintar '.$plan->name,
                    'quantity' => 1,
                    'price' => $amount,
                    'category' => 'Subscription',
                ]],
            ]);

        if ($response->failed() || ! $response->json('invoice_url')) {
            $subscription->update([
                'status' => 'failed',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'xendit_error' => $response->json() ?: $response->body(),
                ]),
            ]);

            return back()->withErrors(['subscription_plan_id' => 'Payment gateway belum bisa membuat invoice Xendit. Cek konfigurasi Xendit.']);
        }

        $subscription->update([
            'metadata' => array_merge($subscription->metadata ?? [], [
                'invoice_id' => $response->json('id'),
                'redirect_url' => $response->json('invoice_url'),
            ]),
        ]);

        return redirect()->away($response->json('invoice_url'));
    }
}
