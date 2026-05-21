@extends('layouts.app')

@section('title', 'Register Toko - StokPintar')

@section('content')
    <div class="register-shell">
        <header class="register-header">
            <strong class="register-brand">StokPintar</strong>
            <a class="btn" href="{{ route('login') }}">Login</a>
        </header>

        <main class="register-main">
            <section class="register-copy">
                <h1>Mulai Kelola Stok Lebih Pintar</h1>
                <p>Daftarkan toko Anda dan mulai pakai aplikasi stok dan kasir yang ringan, rapi, dan mudah dipakai setiap hari.</p>

                <div class="feature-grid">
                    <div class="feature-card">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <strong>Stok Akurat</strong>
                        <span class="muted">Update stok otomatis secara real-time untuk setiap transaksi.</span>
                    </div>
                    <div class="feature-card primary">
                        <span class="material-symbols-outlined">point_of_sale</span>
                        <strong>POS Terintegrasi</strong>
                        <span>Proses pembayaran cepat dan stok langsung tercatat.</span>
                    </div>
                    <div class="feature-wide">
                        <span>Dipercaya untuk operasional UMKM yang butuh stok rapi dan transaksi cepat.</span>
                    </div>
                </div>
            </section>

            <section class="register-card">
                <header>
                    <h1>Register Toko</h1>
                    <p class="subtitle">Owner pertama otomatis dibuat dan bisa langsung login.</p>
                </header>

                <form method="POST" action="{{ route('register.store') }}" class="stack">
                    @csrf
                    <div class="field">
                        <label for="owner_name">Nama Lengkap</label>
                        <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" placeholder="John Doe" required>
                    </div>
                    <div class="field">
                        <label for="store_name">Nama Toko</label>
                        <input id="store_name" name="store_name" value="{{ old('store_name') }}" placeholder="Nama Bisnis Anda" required>
                    </div>
                    <div class="field">
                        <label for="email">Email Bisnis</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="owner@bisnis.com" required>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" placeholder="Min. 8 karakter" required>
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required>
                        </div>
                    </div>
                    <label class="terms-check">
                        <input type="checkbox" name="terms" value="1" required>
                        <span>Saya menyetujui <a href="{{ route('legal.terms') }}" target="_blank">Syarat & Ketentuan</a> serta <a href="{{ route('legal.privacy') }}" target="_blank">Kebijakan Privasi</a> StokPintar.</span>
                    </label>
                    <button class="btn primary" type="submit"><span class="material-symbols-outlined">storefront</span> Buat Akun Toko</button>
                </form>

                <div class="login-link-box">
                    <p class="muted">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
                </div>
            </section>
        </main>

        <footer class="register-footer">
            <div><strong class="register-brand">StokPintar</strong> <span>&copy; {{ date('Y') }} StokPintar. Versi Full untuk operasional toko.</span></div>
        </footer>
    </div>
@endsection
