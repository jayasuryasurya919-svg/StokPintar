@extends('layouts.app')

@section('title', 'Kebijakan Privasi - StokPintar')

@section('content')
    <main class="legal-page">
        <section class="card">
            <div class="section-title">
                <h1>Kebijakan Privasi</h1>
                <span class="badge">StokPintar</span>
            </div>
            <div class="stack">
                <p>StokPintar menyimpan data akun, tenant, toko, produk, stok, transaksi, dan aktivitas yang diperlukan untuk menjalankan fitur aplikasi.</p>
                <p>Data digunakan untuk autentikasi, pembatasan akses role, laporan operasional, audit aktivitas, dan pengiriman email terkait akun seperti undangan tim atau reset password.</p>
                <p>Pengguna bertanggung jawab menjaga kerahasiaan kredensial login dan hanya memberikan akses kepada anggota tim yang berwenang.</p>
                <p>Untuk penggunaan production, konfigurasi email, database, queue, dan keamanan server harus disiapkan sesuai lingkungan operasional yang digunakan.</p>
            </div>
        </section>
    </main>
@endsection
