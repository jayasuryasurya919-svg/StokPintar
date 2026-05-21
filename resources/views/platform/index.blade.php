@extends('layouts.app')

@section('title', 'Admin Platform - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Admin Platform SaaS</h1>
            <p class="subtitle">Pantau seluruh tenant, kelola status langganan, dan atur katalog paket dari level platform.</p>
        </div>
    </header>

    <div class="page-stack">
        <section class="grid-4">
            <div class="card metric-card metric-primary">
                <p class="metric-label">Total Tenant</p>
                <p class="metric-value">{{ $stats['total_tenants'] }}</p>
                <p class="metric-note">Semua tenant terdaftar</p>
                <span class="material-symbols-outlined">domain</span>
            </div>
            <div class="card metric-card">
                <p class="metric-label">Tenant Aktif</p>
                <p class="metric-value">{{ $stats['active_tenants'] }}</p>
                <p class="metric-note">Status aktif berlangganan</p>
                <span class="material-symbols-outlined">verified</span>
            </div>
            <div class="card metric-card metric-critical">
                <p class="metric-label">Tenant Suspended</p>
                <p class="metric-value">{{ $stats['suspended_tenants'] }}</p>
                <p class="metric-note">Perlu perhatian admin</p>
                <span class="material-symbols-outlined">gpp_bad</span>
            </div>
        </section>

        <section class="card flush">
            <div class="panel-header">
                <h2>Tenant Aktif di Platform</h2>
                <span class="badge">{{ $tenants->total() }} tenant</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Owner</th>
                            <th>Paket</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Masa Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr>
                                <td>
                                    <strong>{{ $tenant->name }}</strong>
                                    <div class="muted">{{ $tenant->slug }}</div>
                                </td>
                                <td>
                                    <strong>{{ $tenant->owner?->name ?? 'Belum ada owner' }}</strong>
                                    <div class="muted">{{ $tenant->owner?->email ?? '-' }}</div>
                                </td>
                                <td>{{ $tenant->subscriptionPlan?->name ?? 'Belum ada paket' }}</td>
                                <td>
                                    <div class="muted">{{ $tenant->users_count }} user</div>
                                    <div class="muted">{{ $tenant->products_count }} produk</div>
                                    <div class="muted">{{ $tenant->stores_count }} store</div>
                                </td>
                                <td>
                                    <span class="badge {{ $tenant->status === 'suspended' ? 'low' : 'ok' }}">{{ ucfirst($tenant->status) }}</span>
                                </td>
                                <td>{{ $tenant->subscription_ends_at?->format('d M Y') ?? '-' }}</td>
                                <td>
                                    <details>
                                        <summary class="btn small">Edit</summary>
                                        <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}" class="stack" style="min-width:220px; margin-top:8px;">
                                            @csrf
                                            <div class="field">
                                                <label for="tenant-status-{{ $tenant->id }}">Status</label>
                                                <select id="tenant-status-{{ $tenant->id }}" name="status">
                                                    @foreach(['trial' => 'Trial', 'active' => 'Active', 'suspended' => 'Suspended'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($tenant->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="field">
                                                <label for="tenant-plan-{{ $tenant->id }}">Paket</label>
                                                <select id="tenant-plan-{{ $tenant->id }}" name="subscription_plan_id">
                                                    @foreach($plans as $plan)
                                                        <option value="{{ $plan->id }}" @selected($tenant->subscription_plan_id === $plan->id)>{{ $plan->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn primary small" type="submit">Simpan</button>
                                        </form>
                                        @if($tenant->status === 'suspended')
                                            <form method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" style="margin-top:8px;" onsubmit="return confirm('Hapus permanen tenant {{ $tenant->name }}? Semua data toko, produk, user, dan transaksi tenant ini ikut terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn danger small" type="submit">Hapus Tenant</button>
                                            </form>
                                        @endif
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-cell">Belum ada tenant yang terdaftar di platform ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                {{ $tenants->links() }}
            </div>
        </section>

        <section class="card flush">
            <div class="panel-header">
                <h2>Katalog Paket</h2>
                <div class="action-row" style="gap:10px;">
                    <span class="badge">{{ $plans->count() }} paket</span>
                    <details>
                        <summary class="btn primary small">Buat Paket</summary>
                        <form method="POST" action="{{ route('platform.plans.store') }}" class="stack compact-popover">
                            @csrf
                            <div class="form-grid">
                                <div class="field">
                                    <label for="plan_code">Code</label>
                                    <input id="plan_code" name="code" value="{{ old('code') }}" required>
                                </div>
                                <div class="field">
                                    <label for="plan_name">Nama</label>
                                    <input id="plan_name" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="field">
                                    <label for="plan_price">Harga</label>
                                    <input id="plan_price" name="price" type="number" min="0" value="{{ old('price', 0) }}" required>
                                </div>
                                <div class="field">
                                    <label for="plan_max_stores">Max Store</label>
                                    <input id="plan_max_stores" name="max_stores" type="number" min="1" value="{{ old('max_stores') }}">
                                </div>
                                <div class="field">
                                    <label for="plan_max_products">Max Produk</label>
                                    <input id="plan_max_products" name="max_products" type="number" min="1" value="{{ old('max_products') }}">
                                </div>
                                <div class="field">
                                    <label for="plan_max_users">Max User</label>
                                    <input id="plan_max_users" name="max_users" type="number" min="1" value="{{ old('max_users') }}">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button class="btn primary small" type="submit">Tambah Paket</button>
                            </div>
                        </form>
                    </details>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Kapasitas</th>
                            <th>Fitur</th>
                            <th>Tenant</th>
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
                                <td>{{ $plan->price > 0 ? 'Rp '.number_format($plan->price, 0, ',', '.') : 'Gratis' }}</td>
                                <td>
                                    <div class="muted">{{ $plan->max_stores ?? 'Unlimited' }} toko</div>
                                    <div class="muted">{{ $plan->max_products ?? 'Unlimited' }} produk</div>
                                    <div class="muted">{{ $plan->max_users ?? 'Unlimited' }} user</div>
                                </td>
                                <td>
                                    <div class="muted">{{ $plan->report_retention_days ? 'Laporan '.$plan->report_retention_days.' hari' : 'Laporan unlimited' }}</div>
                                    @if(in_array('priority_support', $plan->features ?? [], true))
                                        <span class="badge ok" style="margin-top:4px;">Priority Support</span>
                                    @else
                                        <span class="muted">Standard support</span>
                                    @endif
                                </td>
                                <td><span class="badge money">{{ $plan->tenants_count }} tenant</span></td>
                                <td>
                                    <details>
                                        <summary class="btn small">Edit</summary>
                                        <form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="stack compact-popover">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-grid">
                                                <div class="field">
                                                    <label for="code-{{ $plan->id }}">Code</label>
                                                    <input id="code-{{ $plan->id }}" name="code" value="{{ $plan->code }}" required>
                                                </div>
                                                <div class="field">
                                                    <label for="name-{{ $plan->id }}">Nama</label>
                                                    <input id="name-{{ $plan->id }}" name="name" value="{{ $plan->name }}" required>
                                                </div>
                                                <div class="field">
                                                    <label for="price-{{ $plan->id }}">Harga</label>
                                                    <input id="price-{{ $plan->id }}" name="price" type="number" min="0" value="{{ $plan->price }}" required>
                                                </div>
                                            </div>
                                            <div class="form-actions">
                                                <button class="btn primary small" type="submit">Update</button>
                                            </div>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <style>
        .compact-popover {
            position: absolute;
            right: 0;
            z-index: 20;
            width: min(560px, calc(100vw - 48px));
            margin-top: 8px;
            padding: 16px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: 0 18px 44px rgba(11,28,48,.16);
        }
        .panel-header details,
        td details {
            position: relative;
        }
        td .compact-popover {
            position: static;
            width: min(460px, 78vw);
            box-shadow: none;
        }
    </style>
@endsection
