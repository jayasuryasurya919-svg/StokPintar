@extends('layouts.app')

@section('title', 'Full Setup - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Full Setup</h1>
            <p class="subtitle">Semua fitur operasional aktif: POS, stok, laporan, export, cabang, tim, resep F&B, dan barcode.</p>
        </div>
    </header>

    <div class="page-stack">
        <section class="grid-3">
            <div class="card metric-card metric-primary">
                <p class="metric-label">Mode Aktif</p>
                <p class="metric-value">{{ $tenant?->subscriptionPlan?->name ?? 'Full Setup' }}</p>
                <p class="metric-note">{{ ucfirst($tenant?->status ?? 'active') }}</p>
            </div>
            <div class="card metric-card">
                <p class="metric-label">Produk</p>
                <p class="metric-value">{{ $tenant?->productUsageLabel() ?? '0 / Unlimited' }}</p>
                <p class="metric-note">Tanpa batas paket</p>
            </div>
            <div class="card metric-card">
                <p class="metric-label">User</p>
                <p class="metric-value">{{ $tenant?->userUsageLabel() ?? '0 / Unlimited' }}</p>
                <p class="metric-note">Semua role aktif</p>
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

        <section class="card flush">
            <div class="panel-header">
                <h2>Fitur Aktif</h2>
                <span class="badge">Full</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Mode</th>
                            <th>Harga</th>
                            <th>Yang Aktif</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td>
                                    <strong>{{ $plan->name }}</strong>
                                    <div class="muted">{{ strtoupper($plan->code) }}</div>
                                </td>
                                <td>{{ $plan->price > 0 ? 'Rp '.number_format($plan->price, 0, ',', '.').'/bln' : 'Aktif penuh' }}</td>
                                <td>
                                    <div class="muted">{{ $plan->max_stores ?? 'Unlimited' }} toko</div>
                                    <div class="muted">{{ $plan->max_products ?? 'Unlimited' }} produk - {{ $plan->max_users ?? 'Unlimited' }} user</div>
                                    <div class="muted">Laporan {{ $plan->report_retention_days ? $plan->report_retention_days.' hari' : 'unlimited' }}</div>
                                    <div class="muted">POS, export, barcode, role tim, cabang, dan resep F&B</div>
                                </td>
                                <td>
                                    @if($tenant?->subscription_plan_id === $plan->id)
                                        <span class="badge ok">Aktif</span>
                                    @else
                                        <form method="POST" action="{{ route('subscription.plan.update') }}">
                                            @csrf
                                            <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                                            <button class="btn small primary" type="submit">Aktifkan</button>
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
