<table>
    <thead>
        <tr>
            <th colspan="5">Laporan Penjualan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</th>
        </tr>
        <tr>
            <th>Invoice</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Metode</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_number }}</td>
                <td>{{ $sale->sold_at?->format('d M Y H:i') ?? '-' }}</td>
                <td>{{ $sale->cashier?->name ?? 'Kasir' }}</td>
                <td>{{ strtoupper($sale->payment_method) }}</td>
                <td>{{ $sale->total }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
