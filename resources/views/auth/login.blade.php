@extends('layouts.app')

@section('title', 'Login - StokPintar')

@section('content')
    <main class="auth-page">
        <section class="auth-visual">
            <img
                alt="Modern Retail Interior"
                src="{{ asset('images/auth-retail.svg') }}"
            >
            <div class="auth-copy">
                <div class="auth-brand-line">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>StokPintar</span>
                </div>
                <h2>Efisiensi operasional di ujung jari Anda.</h2>
                <p>Kelola stok, pantau penjualan real-time, dan jalankan transaksi toko dengan lebih rapi, cepat, dan ringan.</p>
            </div>
        </section>

        <section class="auth-form-pane">
            <div class="auth-card">
                <div class="auth-brand-line auth-brand-mobile">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>StokPintar</span>
                </div>
                <header>
                    <h1>Selamat Datang Kembali</h1>
                    <p class="subtitle">Masuk untuk mengelola stok dan transaksi toko Anda.</p>
                </header>

                <form method="POST" action="{{ route('login.store') }}" class="stack">
                    @csrf
                    <div class="field">
                        <label for="email">Alamat Email</label>
                        <div class="icon-field">
                            <span class="material-symbols-outlined">mail</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@tokoanda.com" required autofocus>
                        </div>
                    </div>
                    <div class="field">
                        <div class="field-label-row">
                            <label for="password">Kata Sandi</label>
                            <a href="{{ route('password.request') }}">Lupa Password?</a>
                        </div>
                        <div class="icon-field has-action">
                            <span class="material-symbols-outlined">lock</span>
                            <input id="password" name="password" type="password" placeholder="********" required>
                            <button class="field-action" type="button" aria-label="Tampilkan kata sandi" data-password-toggle="password">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="auth-options">
                        <label class="remember-check">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            <span>Ingat saya</span>
                        </label>
                    </div>
                    <button class="btn primary" type="submit">Masuk Sekarang <span class="material-symbols-outlined">arrow_forward</span></button>
                </form>

                <div class="auth-divider"><span>atau</span></div>

                <a class="btn google-login-button" href="{{ route('login.google') }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="google-mark">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.3 9.14 5.38 12 5.38z"/>
                    </svg>
                    Masuk dengan Google
                </a>

                <footer>
                    <p>Belum punya akun? <a href="{{ route('register') }}">Daftar Toko Sekarang</a></p>
                </footer>
            </div>
        </section>
    </main>

    <footer class="auth-bottom-footer">
        <strong>StokPintar</strong>
        <span>&copy; {{ date('Y') }} StokPintar. membantu operasional toko harian.</span>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-password-toggle]').forEach(button => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.passwordToggle);
                    const icon = button.querySelector('.material-symbols-outlined');

                    if (!input || !icon) return;

                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    icon.textContent = isHidden ? 'visibility_off' : 'visibility';
                    button.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                });
            });
        });
    </script>
@endsection
