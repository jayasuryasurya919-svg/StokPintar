<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 28px; }
        h1 { margin: 0 0 4px; font-size: 22px; }
        .muted { color: #555; margin: 0 0 18px; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
        .box { border: 1px solid #ddd; padding: 12px; }
        .box span { display: block; color: #555; font-size: 12px; }
        .box strong { display: block; margin-top: 6px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .toolbar { margin-bottom: 16px; }
        @media print { .toolbar { display: none; } body { margin: 12mm; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <h1>Laporan Penjualan</h1>
    <p class="muted">Periode {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>

    <div class="summary">
        <div class="box"><span>Total Pendapatan</span><strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></div>
        <div class="box"><span>Total Transaksi</span><strong>{{ $totalTransactions }}</strong></div>
        <div class="box"><span>Rata-rata Keranjang</span><strong>Rp {{ number_format($averageBasket, 0, ',', '.') }}</strong></div>
    </div>

    <table>
        <thead>
            <tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->sold_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td>{{ $sale->cashier?->name ?? 'Kasir' }}</td>
                    <td>{{ strtoupper($sale->payment_method) }}</td>
                    <td class="right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
