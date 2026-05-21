@extends('layouts.app')

@section('title', 'Dashboard - StokPintar')

@section('content')
<header class="topbar">
    <div>
        <h1>{{ $stockFocused ? 'Dashboard Stok' : ($cashierFocused ? 'Dashboard Kasir' : ($reportFocused ? 'Dashboard Laporan' : 'Dashboard')) }}</h1>
        <p class="subtitle">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong>.
            {{ $stockFocused ? 'Berikut ringkasan stok dan mutasi toko.' : ($cashierFocused ? 'Berikut ringkasan transaksi kasir Anda.' : ($reportFocused ? 'Berikut ringkasan laporan penjualan toko.' : ($ownSalesOnly ? 'Berikut ringkasan transaksi Anda.' : 'Berikut ringkasan bisnis Anda.'))) }}
        </p>
    </div>
    <div class="actions">
        @if(auth()->user()->canPermission('pos.access'))
            <a href="{{ route('pos.index') }}" class="btn primary"><span class="material-symbols-outlined">point_of_sale</span> Buka POS</a>
        @endif
        @if(auth()->user()->canPermission('products.manage'))
            <a href="{{ route('products.create') }}" class="btn"><span class="material-symbols-outlined">add</span> Tambah Produk</a>
        @endif
        @if($stockFocused)
            <a href="{{ route('products.index') }}" class="btn primary"><span class="material-symbols-outlined">inventory_2</span> Kelola Stok</a>
        @endif
    </div>
</header>

@if(session('status'))
    <div class="alert success" style="margin-bottom:16px">{{ session('status') }}</div>
@endif

<div class="page-stack">
    <section class="grid-4">
        @if(! $stockFocused)
            <div class="card metric-card metric-primary">
                <p class="metric-label">{{ $ownSalesOnly ? 'Penjualan Saya Hari Ini' : 'Omzet Hari Ini' }}</p>
                <p class="metric-value">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                <p class="metric-note">{{ $todaySalesCount }} transaksi POS</p>
                <span class="material-symbols-outlined">payments</span>
            </div>
        @endif

        @if(! $cashierFocused && ! $reportFocused)
            <div class="card metric-card {{ $stockFocused ? 'metric-primary' : '' }}">
                <p class="metric-label">Total Produk</p>
                <p class="metric-value">{{ $totalProducts }}</p>
                <p class="metric-note">Produk aktif toko ini</p>
                <span class="material-symbols-outlined">inventory_2</span>
            </div>

            <div class="card metric-card {{ $lowStockCount > 0 ? 'metric-critical' : '' }}">
                <p class="metric-label">Stok Kritis</p>
                <p class="metric-value">{{ $lowStockCount }}</p>
                <p class="metric-note">Produk perlu restock</p>
                <span class="material-symbols-outlined">warning</span>
            </div>
        @endif

        @if(! $stockFocused)
            <div class="card metric-card">
                <p class="metric-label">{{ $ownSalesOnly ? 'Penjualan Saya Bulan Ini' : 'Omzet Bulan Ini' }}</p>
                <p class="metric-value">Rp {{ number_format($monthRevenue, 0, ',', '.') }}</p>
                <p class="metric-note">{{ $monthSalesCount }} transaksi</p>
                <span class="material-symbols-outlined">trending_up</span>
            </div>
        @endif
    </section>

    @if(! $stockFocused)
        <section class="card chart-card">
            <div class="section-title">
                <h2>{{ $ownSalesOnly ? 'Transaksi Saya 7 Hari Terakhir' : 'Omzet 7 Hari Terakhir' }}</h2>
                <div class="chart-legend"><span class="chart-dot"></span> {{ $ownSalesOnly ? 'Penjualan Harian' : 'Pendapatan Harian' }}</div>
            </div>
            <div style="position:relative;height:260px">
                <canvas id="revenueChart"></canvas>
            </div>
        </section>
    @endif

    @if(! $cashierFocused && ! $reportFocused)
    <div class="grid-2">
        <section class="card flush">
            <div class="panel-header">
                <h2>Alert Stok Menipis</h2>
                <span class="badge {{ $lowStockCount > 0 ? 'low' : 'ok' }}">{{ $lowStockCount }} item</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Produk</th><th>SKU</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->sku ?: '-' }}</td>
                                <td><strong style="color:var(--rose)">{{ $product->stock }} {{ $product->unit }}</strong></td>
                                <td>{{ $product->minimum_stock }}</td>
                                <td><span class="badge low">Restock</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-cell">Semua stok aman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if(! $stockFocused)
            <section class="card">
                <div class="section-title">
                    <h2>{{ $ownSalesOnly ? 'Transaksi Saya Terbaru' : 'Transaksi Terbaru' }}</h2>
                    <span class="badge money">POS</span>
                </div>
                <div class="activity-list">
                    @forelse($recentSales as $sale)
                        <div class="activity-item">
                            <div>
                                <strong>{{ $sale->invoice_number }}</strong>
                                <div class="muted">{{ $sale->cashier?->name ?? 'Kasir' }} - {{ $sale->created_at->format('d M H:i') }}</div>
                            </div>
                            <div class="price">Rp {{ number_format($sale->total, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <p class="muted">Belum ada transaksi hari ini.</p>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
    @endif

    @if($reportFocused)
        <section class="card">
            <div class="section-title">
                <h2>Transaksi Terbaru</h2>
                <span class="badge money">Laporan</span>
            </div>
            <div class="activity-list">
                @forelse($recentSales as $sale)
                    <div class="activity-item">
                        <div>
                            <strong>{{ $sale->invoice_number }}</strong>
                            <div class="muted">{{ $sale->cashier?->name ?? 'Kasir' }} - {{ $sale->created_at->format('d M H:i') }}</div>
                        </div>
                        <div class="price">Rp {{ number_format($sale->total, 0, ',', '.') }}</div>
                    </div>
                @empty
                    <p class="muted">Belum ada transaksi hari ini.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if($cashierFocused)
        <section class="card">
            <div class="section-title">
                <h2>Transaksi Saya Terbaru</h2>
                <span class="badge money">POS</span>
            </div>
            <div class="activity-list">
                @forelse($recentSales as $sale)
                    <div class="activity-item">
                        <div>
                            <strong>{{ $sale->invoice_number }}</strong>
                            <div class="muted">{{ $sale->created_at->format('d M H:i') }}</div>
                        </div>
                        <div class="action-row">
                            <div class="price">Rp {{ number_format($sale->total, 0, ',', '.') }}</div>
                            @if(auth()->user()->canPermission('sales.receipt'))
                                <a class="btn small" href="{{ route('reports.receipt', $sale) }}" target="_blank">Struk</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="muted">Belum ada transaksi hari ini.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if(auth()->user()->canPermission('stock_history.view'))
        <section class="card flush">
            <div class="panel-header">
                <h2>Riwayat Mutasi Stok Terbaru</h2>
                <a href="{{ route('stock-history.index') }}" class="btn small">Lihat Semua</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Waktu</th><th>Produk</th><th>Tipe</th><th>Qty</th><th>Stok</th><th>Catatan</th></tr></thead>
                    <tbody>
                        @forelse($recentMutations as $m)
                            <tr>
                                <td>{{ $m->created_at->format('d M H:i') }}</td>
                                <td>{{ $m->product?->name ?? '-' }}</td>
                                <td><span class="badge {{ $m->quantity < 0 ? 'low' : 'ok' }}">{{ strtoupper($m->type) }}</span></td>
                                <td class="{{ $m->quantity < 0 ? '' : 'price' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                                <td>{{ $m->stock_before }} -> {{ $m->stock_after }}</td>
                                <td class="muted">{{ $m->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-cell">Belum ada mutasi stok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>

@if(! $stockFocused)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function(){
        const labels = @json($revenueChart['labels']);
        const data   = @json($revenueChart['values']);
        const ctx    = document.getElementById('revenueChart').getContext('2d');
        const grad   = ctx.createLinearGradient(0, 0, 0, 260);
        grad.addColorStop(0,'rgba(0,53,39,.22)');
        grad.addColorStop(1,'rgba(0,53,39,0)');
        new Chart(ctx, {
            type:'line',
            data:{labels,datasets:[{
                label:@json($ownSalesOnly ? 'Penjualan Saya (Rp)' : 'Omzet (Rp)'),data,
                borderColor:'#003527',backgroundColor:grad,
                borderWidth:3,fill:true,tension:.4,
                pointBackgroundColor:'#003527',pointRadius:5,pointHoverRadius:8
            }]},
            options:{
                responsive:true,maintainAspectRatio:false,
                plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'Rp '+c.parsed.y.toLocaleString('id-ID')}}},
                scales:{
                    y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.06)'},ticks:{callback:v=>'Rp '+v.toLocaleString('id-ID'),font:{size:11}}},
                    x:{grid:{display:false},ticks:{font:{size:11}}}
                }
            }
        });
    })();
    </script>
@endif
@endsection
