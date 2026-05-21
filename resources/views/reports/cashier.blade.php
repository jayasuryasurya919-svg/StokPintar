@extends('layouts.app')

@section('title', 'Performa Kasir - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Performa Kasir</h1>
            <p class="subtitle">Analisis kinerja tiap kasir berdasarkan jumlah transaksi dan omzet.</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('reports.index') }}">Kembali ke Laporan Utama</a>
        </div>
    </header>

    <div class="page-stack">
        <section class="card compact">
            <form method="GET" action="{{ route('reports.cashier') }}" class="filter-grid" style="align-items:end;">
                <div class="field">
                    <label for="month">Bulan</label>
                    <select id="month" name="month">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($month == $m)>
                                {{ \DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="year">Tahun</label>
                    <select id="year" name="year">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <button class="btn primary" type="submit" style="width:100%"><span class="material-symbols-outlined">filter_list</span> Terapkan Filter</button>
                </div>
            </form>
        </section>

        <section class="card flush">
            <div class="panel-header">
                <h2>Breakdown Transaksi per Kasir</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Kasir</th>
                            <th style="text-align:right">Total Transaksi</th>
                            <th style="text-align:right">Total Omzet</th>
                            <th style="text-align:right">Rata-rata Nilai Transaksi</th>
                            <th>Metode Terfavorit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performance as $perf)
                            <tr>
                                <td><strong>{{ $perf['cashier_name'] }}</strong></td>
                                <td style="text-align:right">{{ number_format($perf['total_transactions'], 0, ',', '.') }}x</td>
                                <td style="text-align:right" class="text-green"><strong>Rp {{ number_format($perf['total_revenue'], 0, ',', '.') }}</strong></td>
                                <td style="text-align:right">Rp {{ number_format($perf['avg_transaction'], 0, ',', '.') }}</td>
                                <td><span class="badge">{{ $perf['favorite_payment_method'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-cell">Belum ada data transaksi berbayar pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
