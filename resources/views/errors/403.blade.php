@extends('layouts.app')

@section('title', 'Akses Ditolak - StokPintar')

@section('content')
    <div class="pos-empty" style="min-height:60vh; border:none;">
        <span class="material-symbols-outlined" style="font-size:64px; color:var(--rose)">gpp_bad</span>
        <h1 style="color:var(--ink); margin:16px 0 8px;">403 - Akses Ditolak</h1>
        <p class="subtitle" style="margin:0 0 24px; max-width:100%">Maaf, Anda tidak memiliki izin untuk mengakses halaman atau aksi ini.</p>
        <a href="{{ auth()->user()?->canPermission('platform.manage') ? route('platform.index') : route('dashboard') }}" class="btn primary">Kembali</a>
    </div>
@endsection
