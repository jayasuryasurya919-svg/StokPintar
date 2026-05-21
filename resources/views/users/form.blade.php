@extends('layouts.app')

@section('title', ($userModel->exists ? 'Edit User' : 'Tambah User').' - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>{{ $userModel->exists ? 'Edit User' : 'Tambah User' }}</h1>
            <p class="subtitle">Isi data utama dulu. Shift dan akses cabang bisa diatur sebagai opsi lanjutan.</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('users.index') }}">Kembali</a>
        </div>
    </header>

    <section class="card form-shell">
        <form method="POST" action="{{ $action }}" class="stack">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="form-grid">
                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name', $userModel->name) }}" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $userModel->email) }}" required>
                </div>
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach($roleOptions as $role)
                            <option value="{{ $role }}" @selected(old('role', $userModel->role ?: 'cashier') === $role)>{{ \App\Support\RolePermissionMap::labels()[$role] ?? ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="password">{{ $userModel->exists ? 'Password Baru' : 'Password' }}</label>
                    <input id="password" name="password" type="password" placeholder="{{ $userModel->exists ? 'Kosongkan jika tetap' : 'Minimal 8 karakter' }}" {{ $userModel->exists ? '' : 'required' }}>
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" {{ $userModel->exists ? '' : 'required' }}>
                </div>
            </div>

            <details class="card compact" style="margin:0;">
                <summary class="btn small">Opsi Lanjutan: Shift & Cabang</summary>

                <div class="form-grid" style="margin-top:16px;">
                    <div style="grid-column:1 / -1;">
                        <h3 style="margin:0 0 4px;">Jam Akses</h3>
                        <p class="muted" style="font-size:13px; margin:0 0 12px;">Kosongkan semua hari untuk akses 24 jam.</p>

                        <div style="display:grid; gap:8px; max-width:560px;">
                            @php
                                $days = [
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                    0 => 'Minggu',
                                ];
                                $existingSchedules = $userModel->accessSchedules->keyBy('day_of_week');
                            @endphp

                            @foreach($days as $dayNum => $dayName)
                                @php
                                    $sched = $existingSchedules->get($dayNum);
                                    $isActive = old("schedules.{$dayNum}.active", $sched ? true : false);
                                    $start = old("schedules.{$dayNum}.start_time", $sched ? \Carbon\Carbon::parse($sched->start_time)->format('H:i') : '08:00');
                                    $end = old("schedules.{$dayNum}.end_time", $sched ? \Carbon\Carbon::parse($sched->end_time)->format('H:i') : '17:00');
                                @endphp
                                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:8px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
                                    <label style="display:flex; align-items:center; gap:8px; width:96px; margin:0;">
                                        <input type="checkbox" name="schedules[{{ $dayNum }}][active]" value="1" {{ $isActive ? 'checked' : '' }}>
                                        <span>{{ $dayName }}</span>
                                    </label>
                                    <input type="time" name="schedules[{{ $dayNum }}][start_time]" value="{{ $start }}" style="width:auto; padding:4px 8px;">
                                    <span class="muted">s/d</span>
                                    <input type="time" name="schedules[{{ $dayNum }}][end_time]" value="{{ $end }}" style="width:auto; padding:4px 8px;">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <h3 style="margin:12px 0 4px;">Akses Cabang</h3>
                        <p class="muted" style="font-size:13px; margin:0 0 12px;">Tidak memilih cabang berarti akses semua cabang.</p>

                        <div style="display:flex; flex-wrap:wrap; gap:10px;">
                            @php
                                $selectedStores = old('stores', $userModel->storeAccess->pluck('id')->toArray());
                            @endphp

                            @foreach($stores as $store)
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:8px 12px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
                                    <input type="checkbox" name="stores[]" value="{{ $store->id }}" {{ in_array($store->id, $selectedStores) ? 'checked' : '' }}>
                                    <span>{{ $store->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </details>

            <div class="form-actions">
                <a class="btn" href="{{ route('users.index') }}">Batal</a>
                <button class="btn primary" type="submit">{{ $userModel->exists ? 'Simpan' : 'Tambah User' }}</button>
            </div>
        </form>
    </section>
@endsection
