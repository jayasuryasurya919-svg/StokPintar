@extends('layouts.app')

@section('title', 'Terima Undangan Tim - StokPintar')

@section('content')
<div class="auth-page">
    <div class="auth-visual">
        <div class="auth-copy">
            <div class="auth-brand-line">
                <span class="material-symbols-outlined">inventory_2</span>
                StokPintar
            </div>
            <h2>Bergabung dengan Tim</h2>
            <p>Anda diundang sebagai {{ \App\Support\RolePermissionMap::labels()[$invitation->role] ?? $invitation->role }} di {{ $invitation->tenant->name }}.</p>
        </div>
    </div>
    
    <div class="auth-form-pane">
        <div class="auth-card">
            <div class="auth-brand-mobile auth-brand-line" style="color:var(--green)">
                <span class="material-symbols-outlined">inventory_2</span>
                StokPintar
            </div>
            <header>
                <h1>Lengkapi Profil Anda</h1>
                <p class="subtitle" style="margin-top:6px;">Lengkapi nama dan buat password untuk akun email <strong>{{ $invitation->email }}</strong></p>
            </header>

            <form method="POST" action="{{ route('invite.accept', $invitation->token) }}" class="stack">
                @csrf
                <div class="field">
                    <label for="name">Nama Lengkap</label>
                    <div class="icon-field">
                        <span class="material-symbols-outlined">person</span>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="icon-field">
                        <span class="material-symbols-outlined">lock</span>
                        <input id="password" type="password" name="password" required>
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <div class="icon-field">
                        <span class="material-symbols-outlined">lock</span>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>

                <button class="btn primary" type="submit" style="width:100%; margin-top:8px;">Terima Undangan & Masuk</button>
            </form>
        </div>
    </div>
</div>
@endsection
