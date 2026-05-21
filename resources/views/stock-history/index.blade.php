@extends('layouts.app')

@section('title', 'Riwayat Stok - StokPintar')

@section('content')
<header class="topbar">
    <div>
        <h1>Riwayat Stok</h1>
        <p class="subtitle">Audit trail lengkap semua mutasi stok: masuk, keluar, penjualan POS, dan penyesuaian.</p>
    </div>
</header>

<div class="page-stack">

<section class="grid-3" style="grid-template-columns:repeat(3,1fr)">
    <div class="card metric-card">
        <p class="metric-label">Total Catatan</p>
        <p class="metric-value">{{ $mutations->total() }}</p>
        <p class="metric-note">Semua mutasi stok</p>
        <span class="material-symbols-outlined">history</span>
    </div>
    <div class="card metric-card metric-primary">
        <p class="metric-label">Total Stok Masuk</p>
        <p class="metric-value">+{{ number_format($totalIn) }}</p>
        <p class="metric-note">Unit diterima</p>
        <span class="material-symbols-outlined">arrow_downward</span>
    </div>
    <div class="card metric-card metric-critical">
        <p class="metric-label">Total Stok Keluar</p>
        <p class="metric-value">-{{ number_format($totalOut) }}</p>
        <p class="metric-note">Unit terjual/keluar</p>
        <span class="material-symbols-outlined">arrow_upward</span>
    </div>
</section>

{{-- Filter --}}
<section class="card compact">
    <form method="GET" action="{{ route('stock-history.index') }}">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end">
            <div class="field">
                <label>Produk</label>
                <select name="product_id">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                            {{ $p->name }}{{ $p->sku ? ' — '.$p->sku : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="grid-column: 1 / -1;">
                <label>Tipe Mutasi</label>
                <div class="category-pills">
                    <button type="button" class="pill {{ !request('type') ? 'active' : '' }}" onclick="this.form.type.value=''; this.form.submit();">Semua Tipe</button>
                    @foreach($types as $val => $label)
                        <button type="button" class="pill {{ request('type') === $val ? 'active' : '' }}" onclick="this.form.type.value='{{ $val }}'; this.form.submit();">{{ $label }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="type" value="{{ request('type') }}">
            </div>
            <div class="field">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="field">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="field" style="flex-shrink:0">
                <button class="btn primary" type="submit">
                    <span class="material-symbols-outlined">filter_list</span> Filter
                </button>
            </div>
        </div>
        @if(request()->hasAny(['product_id','type','date_from','date_to']))
            <div style="margin-top:8px">
                <a href="{{ route('stock-history.index') }}" class="btn small danger">
                    <span class="material-symbols-outlined" style="font-size:16px">close</span> Reset Filter
                </a>
            </div>
        @endif
    </form>
</section>

<section class="card flush">
    <div class="panel-header">
        <h2>Log Mutasi Stok</h2>
        <span class="badge">{{ $mutations->total() }} catatan</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <th>Tipe</th>
                    <th>Qty</th>
                    <th>Stok Sebelum</th>
                    <th>Stok Sesudah</th>
                    <th>Oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $m)
                    <tr>
                        <td style="white-space:nowrap">{{ $m->created_at->format('d M Y H:i') }}</td>
                        <td>
                            <strong>{{ $m->product?->name ?? '-' }}</strong>
                            @if($m->product?->sku)
                                <div class="muted" style="font-size:12px">{{ $m->product->sku }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($m->type) {
                                    'in'         => 'ok',
                                    'out'        => 'low',
                                    'sale'       => 'low',
                                    'adjustment' => 'money',
                                    default      => ''
                                };
                                $typeLabel = match($m->type) {
                                    'in'         => 'Masuk',
                                    'out'        => 'Keluar',
                                    'sale'       => 'Penjualan',
                                    'adjustment' => 'Penyesuaian',
                                    default      => strtoupper($m->type)
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span>
                        </td>
                        <td class="{{ $m->quantity < 0 ? '' : 'price' }}" style="font-weight:700">
                            {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
                        </td>
                        <td>{{ $m->stock_before }}</td>
                        <td><strong>{{ $m->stock_after }}</strong></td>
                        <td>{{ $m->user?->name ?? 'Sistem' }}</td>
                        <td class="muted">{{ $m->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-cell">Tidak ada data mutasi stok untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">{{ $mutations->links() }}</div>
</section>
</div>
@endsection
