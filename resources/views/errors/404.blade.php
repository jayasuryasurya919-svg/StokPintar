@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan - StokPintar')

@section('content')
    <div class="pos-empty" style="min-height:60vh; border:none;">
        <span class="material-symbols-outlined" style="font-size:64px; color:var(--muted)">search_off</span>
        <h1 style="color:var(--ink); margin:16px 0 8px;">404 - Halaman Tidak Ditemukan</h1>
        <p class="subtitle" style="margin:0 0 24px; max-width:100%">Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="{{ auth()->user()?->canPermission('platform.manage') ? route('platform.index') : route('dashboard') }}" class="btn primary">Kembali</a>
    </div>
@endsection
