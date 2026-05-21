@extends('layouts.app')

@section('title', 'Activity Log - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>Activity Log</h1>
            <p class="subtitle">Pantau aktivitas pengguna di tenant Anda untuk keperluan audit dan keamanan.</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('users.index') }}">Kembali ke Tim & Akses</a>
        </div>
    </header>

    <div class="page-stack">
        <section class="card compact">
            <form method="GET" action="{{ route('users.activities') }}" class="filter-grid">
                <div class="field">
                    <label for="user_id">User</label>
                    <select id="user_id" name="user_id">
                        <option value="">Semua User</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="action">Aktivitas</label>
                    <select id="action" name="action">
                        <option value="">Semua Aktivitas</option>
                        @foreach($actions as $key => $label)
                            <option value="{{ $key }}" @selected(request('action') == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <button class="btn primary" type="submit"><span class="material-symbols-outlined">filter_list</span> Terapkan Filter</button>
                </div>
            </form>
        </section>

        <section class="card flush">
            <div class="panel-header">
                <h2>Riwayat Aktivitas</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>Detail / Subject</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="white-space:nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td><strong>{{ $log->user?->name ?? 'System/Deleted' }}</strong></td>
                                <td><span class="badge">{{ $actions[$log->action] ?? $log->action }}</span></td>
                                <td class="muted" style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">
                                    @if($log->subject_type && $log->subject)
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                        @if(isset($log->subject->name))
                                            - {{ $log->subject->name }}
                                        @elseif(isset($log->subject->invoice_number))
                                            - {{ $log->subject->invoice_number }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                    
                                    @if($log->meta)
                                        <br>
                                        <small>{{ json_encode($log->meta) }}</small>
                                    @endif
                                </td>
                                <td><span class="muted">{{ $log->ip_address ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-cell">Belum ada riwayat aktivitas yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                {{ $logs->links() }}
            </div>
        </section>
    </div>
@endsection
