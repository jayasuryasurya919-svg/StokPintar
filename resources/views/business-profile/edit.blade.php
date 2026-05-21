@extends('layouts.app')

@section('title', 'Profil Bisnis - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Profil Bisnis</h1>
            <p class="subtitle">Atur nama toko, kontak, dan alamat yang tampil di struk.</p>
        </div>
    </header>

    <div class="page-stack">
        <section class="card">
            <form method="POST" action="{{ route('business-profile.update') }}" enctype="multipart/form-data" class="stack">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="tenant_name">Nama Bisnis</label>
                        <input id="tenant_name" name="tenant_name" value="{{ old('tenant_name', $tenant->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="store_name">Nama Toko</label>
                        <input id="store_name" name="store_name" value="{{ old('store_name', $defaultStore?->name) }}" required>
                    </div>
                    <div class="field">
                        <label for="store_phone">No. Telepon</label>
                        <input id="store_phone" name="store_phone" value="{{ old('store_phone', $defaultStore?->phone) }}">
                    </div>
                    <div class="field">
                        <label for="store_code">Kode Toko</label>
                        <input id="store_code" name="store_code" value="{{ old('store_code', $defaultStore?->code) }}">
                    </div>
                    <div class="field" style="grid-column:1 / -1;">
                        <label for="store_address">Alamat</label>
                        <textarea id="store_address" name="store_address" rows="3">{{ old('store_address', $defaultStore?->address) }}</textarea>
                    </div>
                </div>

                <details class="card compact" style="margin:0;">
                    <summary class="btn small">Logo Toko</summary>
                    <div class="field" style="margin-top:12px;">
                        <label for="logo">Upload Logo</label>
                        <input id="logo" name="logo" type="file" accept="image/*">
                    </div>
                </details>

                <div class="form-actions">
                    <button class="btn primary" type="submit"><span class="material-symbols-outlined">save</span> Simpan</button>
                </div>
            </form>
        </section>
    </div>
@endsection
