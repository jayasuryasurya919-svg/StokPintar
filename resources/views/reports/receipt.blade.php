<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 24px; color: #111; }
        .receipt { width: 340px; margin: 0 auto; background: white; padding: 22px; border: 1px solid #ddd; }
        h1 { margin: 0; text-align: center; font-size: 18px; }
        .muted { color: #666; font-size: 12px; text-align: center; margin: 6px 0 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        td { padding: 5px 0; vertical-align: top; }
        .right { text-align: right; }
        .totals { border-top: 1px dashed #999; margin-top: 12px; padding-top: 10px; }
        .row { display: flex; justify-content: space-between; margin: 5px 0; }
        .toolbar { text-align: center; margin-bottom: 16px; }
        @media print { body { background: white; padding: 0; } .toolbar { display: none; } .receipt { border: 0; margin: 0; width: 100%; } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
    <div class="receipt">
        <h1>{{ $sale->store?->name ?? $sale->tenant?->name ?? 'StokPintar' }}</h1>
        <p class="muted">{{ $sale->invoice_number }}<br>{{ $sale->sold_at?->format('d M Y H:i') }} | {{ $sale->cashier?->name ?? 'Kasir' }}</p>
        <table>
            @foreach($sale->items as $item)
                <tr>
                    <td><strong>{{ $item->product_name }}</strong><br>{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
        <div class="totals">
            <div class="row"><span>Total</span><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></div>
            <div class="row"><span>Bayar</span><span>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span></div>
            <div class="row"><span>Kembali</span><strong>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</strong></div>
        </div>
    </div>
</body>
</html>
