@extends('layouts.app')

@section('title', 'Tim & Akses - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Tim & Akses</h1>
            <p class="subtitle">
                {{ auth()->user()->role === \App\Models\User::ROLE_OWNER
                    ? 'Kelola akun dan role anggota toko.'
                    : 'Kelola akun operasional toko.' }}
            </p>
        </div>
        <div class="actions">
            @if(auth()->user()->canPermission('activity_log.view'))
                <a class="btn" href="{{ route('users.activities') }}"><span class="material-symbols-outlined">history</span> Log Aktivitas</a>
            @endif
            @if(! empty($roleOptions))
                <a class="btn primary" href="{{ route('users.create') }}"><span class="material-symbols-outlined">person_add</span> Tambah User</a>
            @endif
        </div>
    </header>

    <div class="page-stack">
        @if(auth()->user()->canPermission('users.invite'))
            <section class="card compact">
                <details>
                    <summary class="btn small">Undang via Email</summary>
                    <form method="POST" action="{{ route('users.invite') }}" class="filter-grid" style="margin-top:12px; align-items:end;">
                        @csrf
                        <div class="field">
                            <label for="invite_email">Email</label>
                            <input id="invite_email" name="email" type="email" placeholder="nama@email.com" required>
                        </div>
                        <div class="field">
                            <label for="invite_role">Role</label>
                            <select id="invite_role" name="role" required>
                                @foreach($roleOptions as $role)
                                    <option value="{{ $role }}">{{ \App\Support\RolePermissionMap::labels()[$role] ?? ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <button class="btn primary" type="submit"><span class="material-symbols-outlined">send</span> Kirim</button>
                        </div>
                    </form>
                </details>
            </section>
        @endif

        <section class="card flush">
            <div class="panel-header">
                <h2>Daftar User</h2>
                <span class="badge">{{ $users->total() }} user</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $userRow)
                            @php
                                $canManageUserRow = auth()->user()->role === \App\Models\User::ROLE_OWNER
                                    || ! in_array($userRow->role, [\App\Models\User::ROLE_OWNER, \App\Models\User::ROLE_MANAGER], true);
                                $roleBadgeClass = match($userRow->role) {
                                    \App\Models\User::ROLE_OWNER => 'ok',
                                    \App\Models\User::ROLE_CASHIER => 'money',
                                    \App\Models\User::ROLE_STAFF_GUDANG => 'low',
                                    default => 'primary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="product-name-cell">
                                        <span class="product-thumb">{{ strtoupper(mb_substr($userRow->name, 0, 2)) }}</span>
                                        <div>
                                            <strong>{{ $userRow->name }}</strong>
                                            <div class="muted">{{ $userRow->email }}{{ $userRow->id === auth()->id() ? ' · Akun aktif' : '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge {{ $roleBadgeClass }}">{{ $userRow->roleLabel() }}</span></td>
                                <td>
                                    <div class="action-row">
                                        @if($canManageUserRow)
                                            <a class="btn small" href="{{ route('users.edit', $userRow) }}">Edit</a>
                                        @endif
                                        @if($canManageUserRow && $userRow->id !== auth()->id() && ! ($userRow->role === \App\Models\User::ROLE_OWNER && $ownerCount <= 1))
                                            <form class="inline-form" method="POST" action="{{ route('users.destroy', $userRow) }}" onsubmit="return confirm('Hapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn small danger" type="submit">Hapus</button>
                                            </form>
                                        @else
                                            <span class="muted">{{ $canManageUserRow ? 'Tidak bisa dihapus' : 'Role lebih tinggi' }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-cell">Belum ada user tambahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                {{ $users->links() }}
            </div>
        </section>
    </div>
@endsection
