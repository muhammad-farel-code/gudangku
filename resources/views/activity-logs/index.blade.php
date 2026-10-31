@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')
@section('page_title', 'Log Aktivitas')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Log Aktivitas (Audit Trail)</h1>
        <p class="text-secondary mb-0">Rekam jejak seluruh aktivitas pengguna, perubahan data master, mutasi stok, dan autentikasi sistem.</p>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-2 align-items-center">
            {{-- Search Box --}}
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0" 
                           placeholder="Cari deskripsi, aksi, IP..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filter Aksi --}}
            <div class="col-6 col-md-2">
                <select name="action" class="form-select bg-light border-0">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                            {{ $act }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Pengguna --}}
            <div class="col-6 col-md-3">
                <select name="user_id" class="form-select bg-light border-0">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tanggal Mulai --}}
            <div class="col-6 col-md-2">
                <input type="date" name="start_date" class="form-control bg-light border-0" 
                       value="{{ request('start_date') }}" title="Tanggal Mulai">
            </div>

            {{-- Tombol Filter & Reset --}}
            <div class="col-6 col-md-2 d-flex gap-2">
                <input type="date" name="end_date" class="form-control bg-light border-0" 
                       value="{{ request('end_date') }}" title="Tanggal Akhir">
                <button type="submit" class="btn btn-dark rounded-pill px-3" title="Terapkan Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                @if(request()->hasAny(['search', 'action', 'user_id', 'start_date', 'end_date']))
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-light rounded-pill px-3" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Audit Trail --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th scope="col" class="ps-4">Waktu</th>
                    <th scope="col">Pengguna</th>
                    <th scope="col">Tipe Aksi</th>
                    <th scope="col">Deskripsi Aktivitas</th>
                    <th scope="col" class="pe-4">Alamat IP</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-secondary text-nowrap">
                            <div>{{ $log->created_at->format('d M Y') }}</div>
                            <div style="font-size: 0.72rem;">{{ $log->created_at->format('H:i:s') }} WIB</div>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="fw-semibold text-dark">{{ $log->user->name }}</div>
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">
                                    {{ $log->user->role }}
                                </span>
                            @else
                                <span class="text-muted">Sistem / Tamu</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match(true) {
                                    str_contains($log->action, 'login') => 'bg-success-subtle text-success',
                                    str_contains($log->action, 'logout') => 'bg-secondary-subtle text-secondary',
                                    str_contains($log->action, 'create') || str_contains($log->action, 'stock_in') => 'bg-primary-subtle text-primary',
                                    str_contains($log->action, 'update') || str_contains($log->action, 'stock_adjustment') => 'bg-warning-subtle text-warning-emphasis',
                                    str_contains($log->action, 'delete') || str_contains($log->action, 'stock_out') => 'bg-danger-subtle text-danger',
                                    default => 'bg-light text-dark border',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-2.5 py-1">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="text-dark lh-sm" style="max-width: 450px;">
                            {{ $log->description }}
                        </td>
                        <td class="pe-4 font-monospace text-secondary small">
                            {{ $log->ip_address ?: '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            <i class="bi bi-shield-check fs-2 d-block mb-2"></i>
                            Tidak ada riwayat aktivitas yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-secondary">
                    Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} catatan aktivitas
                </span>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection
