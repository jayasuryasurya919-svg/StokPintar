@extends('layouts.app')

@section('title', 'Laporan - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Laporan</h1>
            <p class="subtitle">Ringkasan penjualan dan transaksi toko.</p>
        </div>
        <div class="actions">
            @if(auth()->user()->canPermission('reports.cashier'))
                <a class="btn" href="{{ route('reports.cashier') }}"><span class="material-symbols-outlined">analytics</span> Performa Kasir</a>
            @endif
            @if(auth()->user()->canPermission('reports.export'))
                <a class="btn" href="{{ route('reports.export.pdf', request()->query()) }}" target="_blank"><span class="material-symbols-outlined">picture_as_pdf</span> PDF</a>
                <a class="btn" href="{{ route('reports.export.excel', request()->query()) }}"><span class="material-symbols-outlined">table_view</span> Excel</a>
            @endif
        </div>
    </header>

    <div class="page-stack">
        <section class="card compact">
            <form method="GET" action="{{ route('reports.index') }}" class="filter-grid">
                <div class="field">
                    <label for="month">Bulan</label>
                    <select id="month" name="month">
                        @foreach(range(1, 12) as $option)
                            <option value="{{ $option }}" @selected($month === $option)>{{ DateTime::createFromFormat('!m', $option)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="year">Tahun</label>
                    <input id="year" name="year" type="number" value="{{ $year }}">
                </div>
                <div class="field">
                    <button class="btn primary" type="submit"><span class="material-symbols-outlined">filter_list</span> Terapkan</button>
                </div>
            </form>
        </section>

        <section class="grid-3">
            <div class="card metric-card metric-primary">
                <p class="metric-label">Pendapatan</p>
                <p class="metric-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="metric-note">Periode terpilih</p>
            </div>
            <div class="card metric-card">
                <p class="metric-label">Transaksi</p>
                <p class="metric-value">{{ $totalTransactions }}</p>
                <p class="metric-note">Transaksi selesai</p>
            </div>
            <div class="card metric-card">
                <p class="metric-label">Rata-rata</p>
                <p class="metric-value">Rp {{ number_format($averageBasket, 0, ',', '.') }}</p>
                <p class="metric-note">Nilai per struk</p>
            </div>
        </section>

        <section class="card flush">
            <div class="panel-header">
                <h2>Transaksi</h2>
                <span class="badge money">{{ $sales->total() }} transaksi</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th class="text-right">Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>
                                    <strong>{{ $sale->invoice_number }}</strong>
                                    <div class="muted">{{ strtoupper($sale->payment_method) }} · {{ ucfirst($sale->status) }}</div>
                                </td>
                                <td>{{ $sale->sold_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td>{{ $sale->cashier?->name ?? 'Kasir' }}</td>
                                <td class="text-right price">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                <td>
                                    <div class="action-row">
                                        @if(auth()->user()->canPermission('sales.receipt'))
                                            <a class="btn small" href="{{ route('reports.receipt', $sale) }}" target="_blank">Struk</a>
                                        @endif
                                        @if($sale->status === 'paid' && auth()->user()->canPermission('sales.void'))
                                            <form class="inline-form" method="POST" action="{{ route('sales.void', $sale) }}" onsubmit="return confirm('Void transaksi ini dan kembalikan stok?')">
                                                @csrf
                                                <button class="btn small danger" type="submit">Void</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-cell">Belum ada transaksi pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer">{{ $sales->links() }}</div>
        </section>
    </div>
@endsection
