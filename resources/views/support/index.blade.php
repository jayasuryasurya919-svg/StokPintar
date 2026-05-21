@extends('layouts.app')

@section('title', 'Support - StokPintar')

@section('content')
    <header class="topbar">
        <div>
            <h1>{{ $isPlatformAdmin ? 'Support Platform' : 'Bantuan Support' }}</h1>
            <p class="subtitle">
                {{ $isPlatformAdmin ? 'Pantau dan tindak lanjuti tiket bantuan dari semua tenant.' : 'Kirim kendala operasional toko ke admin platform StokPintar.' }}
            </p>
        </div>
    </header>

    <div class="page-stack">
        @if(! $isPlatformAdmin)
            <section class="grid-2">
                <div class="card metric-card {{ $hasPrioritySupport ? 'metric-primary' : '' }}">
                    <p class="metric-label">Status Support</p>
                    <p class="metric-value">{{ $hasPrioritySupport ? 'Priority' : 'Regular' }}</p>
                    <p class="metric-note">{{ $hasPrioritySupport ? 'Tiket Anda otomatis masuk antrean prioritas.' : 'Upgrade ke paket Business untuk antrean prioritas.' }}</p>
                    <span class="material-symbols-outlined">{{ $hasPrioritySupport ? 'workspace_premium' : 'support_agent' }}</span>
                </div>
                <div class="card compact">
                    <h2 style="margin:0 0 12px;">Buat Tiket</h2>
                    <form method="POST" action="{{ route('support.store') }}" class="stack">
                        @csrf
                        <div class="field">
                            <label for="subject">Judul Masalah</label>
                            <input id="subject" name="subject" value="{{ old('subject') }}" placeholder="Contoh: barcode tidak terbaca di POS" required>
                            @error('subject')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="message">Detail</label>
                            <textarea id="message" name="message" rows="5" placeholder="Jelaskan kendala, halaman yang dipakai, dan langkah yang sudah dicoba." required>{{ old('message') }}</textarea>
                            @error('message')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-actions">
                            <button class="btn primary" type="submit">
                                <span class="material-symbols-outlined">send</span> Kirim Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <section class="card flush">
            <div class="panel-header">
                <h2>{{ $isPlatformAdmin ? 'Semua Tiket Support' : 'Tiket Saya' }}</h2>
                <span class="badge">{{ $tickets->total() }} tiket</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tiket</th>
                            @if($isPlatformAdmin)
                                <th>Tenant</th>
                            @endif
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Catatan Admin</th>
                            @if($isPlatformAdmin)
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <strong>{{ $ticket->subject }}</strong>
                                    <div class="muted" style="max-width:360px;">{{ $ticket->message }}</div>
                                    <div class="muted">Oleh: {{ $ticket->requester?->name ?? '-' }}</div>
                                </td>
                                @if($isPlatformAdmin)
                                    <td>
                                        <strong>{{ $ticket->tenant?->name ?? '-' }}</strong>
                                        <div class="muted">{{ $ticket->tenant?->subscriptionPlan?->name ?? 'Tanpa paket' }}</div>
                                    </td>
                                @endif
                                <td>
                                    <span class="badge {{ $ticket->priority === 'priority' ? 'ok' : '' }}">
                                        {{ $ticket->priority === 'priority' ? 'Priority Support' : 'Regular' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->status === 'resolved' ? 'ok' : ($ticket->status === 'open' ? 'money' : '') }}">
                                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $ticket->admin_note ?: '-' }}</td>
                                @if($isPlatformAdmin)
                                    <td>
                                        <details>
                                            <summary class="btn small">Update</summary>
                                            <form method="POST" action="{{ route('support.update', $ticket) }}" class="stack" style="min-width:240px; margin-top:8px;">
                                                @csrf
                                                @method('PUT')
                                                <div class="field">
                                                    <label for="status-{{ $ticket->id }}">Status</label>
                                                    <select id="status-{{ $ticket->id }}" name="status">
                                                        @foreach(['open' => 'Open', 'in_progress' => 'Diproses', 'resolved' => 'Selesai'] as $value => $label)
                                                            <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label for="note-{{ $ticket->id }}">Catatan Admin</label>
                                                    <textarea id="note-{{ $ticket->id }}" name="admin_note" rows="3">{{ $ticket->admin_note }}</textarea>
                                                </div>
                                                <button class="btn primary small" type="submit">Simpan</button>
                                            </form>
                                        </details>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isPlatformAdmin ? 7 : 6 }}" class="empty-cell">Belum ada tiket support.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                {{ $tickets->links() }}
            </div>
        </section>
    </div>
@endsection
