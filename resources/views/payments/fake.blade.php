@extends('layouts.app')

@section('title', 'Pembayaran Simulasi - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Pembayaran Simulasi</h1>
            <p class="subtitle">Alur pembayaran aktif untuk demo. Tidak ada saldo, kartu, atau uang asli yang diproses.</p>
        </div>
    </header>

    <div class="page-stack" style="max-width:760px;">
        <section class="card compact">
            <div class="section-title">
                <div>
                    <h2>{{ $subscription->plan?->name ?? 'Paket Berbayar' }}</h2>
                    <p class="muted" style="margin:4px 0 0;">Nomor pembayaran: {{ $subscription->provider_reference }}</p>
                </div>
                <span class="badge ok">Demo</span>
            </div>

            <div class="grid-2" style="margin-top:18px;">
                <div class="soft-panel">
                    <p class="metric-label">Total Bayar</p>
                    <p class="metric-value">Rp {{ number_format((int) ($subscription->metadata['amount'] ?? $subscription->plan?->price ?? 0), 0, ',', '.') }}</p>
                    <p class="metric-note">Simulasi tagihan bulanan</p>
                </div>
                <div class="soft-panel">
                    <p class="metric-label">Status</p>
                    <p class="metric-value">Pending</p>
                    <p class="metric-note">Klik bayar untuk mengaktifkan paket</p>
                </div>
            </div>

            <div class="empty-state" style="padding:24px 0 10px;">
                <h3>Mode sementara: pembayaran pura-pura</h3>
                <p>Halaman ini menggantikan Midtrans/Xendit selama demo. Nanti ketika gateway asli sudah siap, cukup ganti provider pembayaran di server.</p>
            </div>

            <form method="POST" action="{{ route('payments.fake.complete', $subscription) }}" class="action-row" style="justify-content:flex-end;">
                @csrf
                <a class="btn" href="{{ route('subscription.index') }}">Kembali</a>
                <button class="btn primary" type="submit">Bayar Simulasi</button>
            </form>
        </section>
    </div>
@endsection
