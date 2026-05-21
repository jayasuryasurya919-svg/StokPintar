@extends('layouts.app')

@section('title', 'Buat Password Baru - StokPintar')

@section('content')
    <main class="auth-page single-pane">
        <section class="auth-form-pane">
            <div class="auth-card">
                <div class="auth-brand-line auth-brand-mobile" style="display:flex;">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>StokPintar</span>
                </div>
                <header>
                    <h1>Buat Password Baru</h1>
                    <p class="subtitle">Gunakan password minimal 8 karakter agar akun Anda tetap aman.</p>
                </header>

                <form method="POST" action="{{ route('password.update') }}" class="stack">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="field">
                        <label for="email">Alamat Email</label>
                        <div class="icon-field">
                            <span class="material-symbols-outlined">mail</span>
                            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus>
                        </div>
                    </div>
                    <div class="field">
                        <label for="password">Password Baru</label>
                        <div class="icon-field has-action">
                            <span class="material-symbols-outlined">lock</span>
                            <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required>
                            <button class="field-action" type="button" aria-label="Tampilkan kata sandi" data-password-toggle="password">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="icon-field has-action">
                            <span class="material-symbols-outlined">lock</span>
                            <input id="password_confirmation" name="password_confirmation" type="password" required>
                            <button class="field-action" type="button" aria-label="Tampilkan konfirmasi password" data-password-toggle="password_confirmation">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                    <button class="btn primary" type="submit">Simpan Password Baru <span class="material-symbols-outlined">check</span></button>
                </form>

                <footer>
                    <p><a href="{{ route('login') }}">Kembali ke Login</a></p>
                </footer>
            </div>
        </section>
    </main>

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
