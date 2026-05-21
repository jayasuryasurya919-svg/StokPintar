@extends('layouts.app')

@section('title', 'Reset Password - StokPintar')

@section('content')
    <main class="auth-page single-pane">
        <section class="auth-form-pane">
            <div class="auth-card">
                <div class="auth-brand-line auth-brand-mobile" style="display:flex;">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>StokPintar</span>
                </div>
                <header>
                    <h1>Reset Password</h1>
                    <p class="subtitle">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.</p>
                </header>

                <form method="POST" action="{{ route('password.email') }}" class="stack">
                    @csrf
                    <div class="field">
                        <label for="email">Alamat Email</label>
                        <div class="icon-field">
                            <span class="material-symbols-outlined">mail</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@tokoanda.com" required autofocus>
                        </div>
                    </div>
                    <button class="btn primary" type="submit">Kirim Link Reset <span class="material-symbols-outlined">arrow_forward</span></button>
                </form>

                <footer>
                    <p>Ingat password? <a href="{{ route('login') }}">Kembali ke Login</a></p>
                </footer>
            </div>
        </section>
    </main>
@endsection
