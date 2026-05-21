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

            <details class="card compact user-access-panel" style="margin:0;">
                <summary class="btn small user-access-summary">Opsi Lanjutan: Shift & Cabang</summary>

                <div class="user-access-content">
                    <section class="user-access-section">
                        <div class="user-access-heading">
                            <h3>Jam Akses</h3>
                            <p class="muted">Kosongkan semua hari untuk akses 24 jam.</p>
                        </div>

                        <div class="schedule-list">
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
                                <div class="schedule-row">
                                    <label class="schedule-day">
                                        <input class="schedule-check" type="checkbox" name="schedules[{{ $dayNum }}][active]" value="1" {{ $isActive ? 'checked' : '' }}>
                                        <span class="schedule-day-name">{{ $dayName }}</span>
                                    </label>
                                    <div class="schedule-time-range">
                                        <input type="time" name="schedules[{{ $dayNum }}][start_time]" value="{{ $start }}">
                                        <span class="muted">s/d</span>
                                        <input type="time" name="schedules[{{ $dayNum }}][end_time]" value="{{ $end }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="user-access-section">
                        <div class="user-access-heading">
                            <h3>Akses Cabang</h3>
                            <p class="muted">Tidak memilih cabang berarti akses semua cabang.</p>
                        </div>

                        <div class="store-access-grid">
                            @php
                                $selectedStores = old('stores', $userModel->storeAccess->pluck('id')->toArray());
                            @endphp

                            @foreach($stores as $store)
                                <label class="store-access-card">
                                    <input class="schedule-check" type="checkbox" name="stores[]" value="{{ $store->id }}" {{ in_array($store->id, $selectedStores) ? 'checked' : '' }}>
                                    <span>{{ $store->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>
            </details>

            <div class="form-actions">
                <a class="btn" href="{{ route('users.index') }}">Batal</a>
                <button class="btn primary" type="submit">{{ $userModel->exists ? 'Simpan' : 'Tambah User' }}</button>
            </div>
        </form>
    </section>

    <style>
        .user-access-panel {
            overflow: visible;
        }

        .user-access-summary {
            width: fit-content;
        }

        .user-access-content {
            display: grid;
            gap: 22px;
            margin-top: 18px;
        }

        .user-access-section {
            display: grid;
            gap: 12px;
        }

        .user-access-heading h3 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        .user-access-heading p {
            margin: 0;
            font-size: 13px;
        }

        .schedule-list {
            display: grid;
            gap: 8px;
            max-width: 620px;
        }

        .schedule-row {
            display: grid;
            grid-template-columns: 128px minmax(220px, 1fr);
            align-items: center;
            gap: 14px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface-low);
        }

        .schedule-day,
        .store-access-card {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            cursor: pointer;
            font-weight: 700;
        }

        .schedule-check {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
        }

        .schedule-day-name {
            min-width: 64px;
        }

        .schedule-time-range {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            gap: 10px;
        }

        .schedule-time-range input[type="time"] {
            width: 100%;
            min-width: 0;
            height: 42px;
            padding: 8px 10px;
        }

        .store-access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 10px;
            max-width: 720px;
        }

        .store-access-card {
            min-height: 48px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface-low);
        }

        @media (max-width: 640px) {
            .schedule-row {
                grid-template-columns: 1fr;
                align-items: stretch;
                gap: 8px;
            }

            .schedule-time-range {
                grid-template-columns: 1fr auto 1fr;
            }
        }
    </style>
@endsection
