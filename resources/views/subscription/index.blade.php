@extends('layouts.app')

@section('title', 'Paket & Full Setup - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Paket & Full Setup</h1>
            <p class="subtitle">Free, Starter, Pro, dan Business tetap tersedia. Semua paket disiapkan lengkap; perbedaannya ada di kapasitas, cabang, dan dukungan.</p>
        </div>
    </header>

    <div class="page-stack">
        <section class="grid-3">
            <div class="card metric-card metric-primary">
                <p class="metric-label">Paket Aktif</p>
                <p class="metric-value">{{ $tenant?->subscriptionPlan?->name ?? 'Belum ada' }}</p>
                <p class="metric-note">{{ ucfirst($tenant?->status ?? 'unknown') }}</p>
            </div>
            <div class="card metric-card">
                <p class="metric-label">Produk</p>
                <p class="metric-value">{{ $tenant?->productUsageLabel() ?? '0 / 0' }}</p>
                <p class="metric-note">Pemakaian saat ini</p>
            </div>
            <div class="card metric-card">
                <p class="metric-label">User</p>
                <p class="metric-value">{{ $tenant?->userUsageLabel() ?? '0 / 0' }}</p>
                <p class="metric-note">Semua role tersedia</p>
            </div>
        </section>

        <section class="card compact">
            <details>
                <summary class="btn small">Pengaturan Tenant</summary>
                <form method="POST" action="{{ route('subscription.tenant.update') }}" class="filter-grid" style="margin-top:12px; align-items:end;">
                    @csrf
                    <div class="field">
                        <label for="tenant_name">Nama Tenant</label>
                        <input id="tenant_name" name="name" value="{{ old('name', $tenant?->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="tenant_status">Status</label>
                        <select id="tenant_status" name="status" required>
                            @foreach(['trial' => 'Trial', 'active' => 'Active'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $tenant?->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <button class="btn primary" type="submit">Simpan</button>
                    </div>
                </form>
            </details>
        </section>

        @if($latestSubscription?->status === 'pending' && ($latestSubscription->metadata['redirect_url'] ?? null))
            <section class="card compact">
                <div class="section-title">
                    <div>
                        <h2>Pembayaran Menunggu</h2>
                        <p class="muted" style="margin:4px 0 0;">Selesaikan pembayaran paket {{ $latestSubscription->plan?->name }} melalui {{ strtoupper($latestSubscription->provider) }}.</p>
                    </div>
                    <a class="btn primary" href="{{ $latestSubscription->metadata['redirect_url'] }}">
                        Lanjutkan Pembayaran
                    </a>
                </div>
            </section>
        @endif

        <section class="card flush">
            <div class="panel-header">
                <h2>Pilih Paket</h2>
                <span class="badge">{{ $plans->count() }} paket</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Kapasitas</th>
                            <th>Full Setup</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td>
                                    <strong>{{ $plan->name }}</strong>
                                    <div class="muted">{{ strtoupper($plan->code) }}</div>
                                </td>
                                <td>{{ $plan->price > 0 ? 'Rp '.number_format($plan->price, 0, ',', '.').'/bln' : 'Rp 0/bln' }}</td>
                                <td>
                                    <div class="muted">{{ $plan->max_stores ?? 'Unlimited' }} toko</div>
                                    <div class="muted">{{ $plan->max_products ?? 'Unlimited' }} produk - {{ $plan->max_users ?? 'Unlimited' }} user</div>
                                    <div class="muted">Laporan {{ $plan->report_retention_days ? $plan->report_retention_days.' hari' : 'unlimited' }}</div>
                                </td>
                                <td>
                                    <div class="muted">POS, stok, laporan, export, barcode</div>
                                    <div class="muted">Role tim, shift, akses cabang, resep F&B</div>
                                    @if(in_array('priority_support', $plan->features ?? [], true))
                                        <span class="badge ok" style="margin-top:6px">Priority Support</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tenant?->subscription_plan_id === $plan->id)
                                        <span class="badge ok">Aktif</span>
                                    @else
                                        <form method="POST" action="{{ route('subscription.plan.update') }}">
                                            @csrf
                                            <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                                            <button class="btn small primary" type="submit">Pilih</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
