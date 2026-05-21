@extends('layouts.app')

@section('title', 'Login - StokPintar')

@section('content')
    <main class="auth-page">
        <section class="auth-visual">
            <img
                alt="Modern Retail Interior"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJMAQcs-iZ_k9obZV7vpBvUVCvPIDcRejsCbbxKzZlT6RG_PcAVZHhmRgE3HcgyVIke6Cm3OPu3Ly205nBg_FQVsP-AcU7Px2oXLX7T-K612DBopvtbNHXg2BixalYYNPvQ8mGqURLBFPsLNxlSwiYNc0Bxbf_cnJkLfGhuHxHhF-ZYi7ffsCWyjcPL41p9yYb7PyeV9uc7zBIANEN7cujrhtUr8DBr5KXan1lyFzY_mlNAxgRB0VAakn9uHvRtYOn4NzPEGPLKpJu"
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
