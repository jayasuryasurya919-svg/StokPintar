@extends('layouts.app')

@section('title', 'Akun Saya - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Akun Saya</h1>
            <p class="subtitle">Perbarui identitas login dan password akun Anda.</p>
        </div>
    </header>

    <div class="page-stack">
        <section class="card">
            <form method="POST" action="{{ route('account.update') }}" class="stack">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Nama Lengkap</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="email">Email Login</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="field">
                        <label>Role</label>
                        <input value="{{ $user->roleLabel() }}" disabled>
                    </div>
                    <div class="field">
                        <label for="current_password">Password Saat Ini</label>
                        <input id="current_password" name="current_password" type="password" placeholder="Wajib untuk ubah email/password">
                    </div>
                    <div class="field">
                        <label for="password">Password Baru</label>
                        <input id="password" name="password" type="password" placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn primary" type="submit"><span class="material-symbols-outlined">save</span> Simpan Akun</button>
                </div>
            </form>
        </section>
    </div>
@endsection
