@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Manajemen Pengguna</h1>
        <p class="text-secondary mb-0">Kelola kredensial akun, kontrol akses berbasis peran (RBAC), dan status operasional staf.</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-dark gk-btn-pill">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
    </a>
</div>

{{-- Bar Pencarian & Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            {{-- Search by Name / Email --}}
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0" 
                           placeholder="Cari nama atau email pengguna..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filter Role --}}
            <div class="col-6 col-md-3">
                <select name="role" class="form-select bg-light border-0">
                    <option value="">Semua Peran (Role)</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager Gudang</option>
                    <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff Gudang</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div class="col-6 col-md-2">
                <select name="is_active" class="form-select bg-light border-0">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            {{-- Tombol Filter & Reset --}}
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark gk-btn-pill w-100">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'role', 'is_active']))
                    <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-3" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Data Pengguna --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">Pengguna</th>
                    <th scope="col">Peran (Role)</th>
                    <th scope="col">Status Akun</th>
                    <th scope="col">Terdaftar Pada</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-light text-dark fw-bold d-flex align-items-center justify-content-center"
                                     style="width: 42px; height: 42px; font-size: 1rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">
                                        {{ $user->name }}
                                        @if($user->id === Auth::id())
                                            <span class="badge bg-light text-dark border ms-1" style="font-size: 0.7rem;">Anda</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-dark text-white rounded-pill px-3 py-1">Administrator</span>
                            @elseif($user->role === 'manager')
                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1">Manager</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">Staff Gudang</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1">
                                    <i class="bi bi-slash-circle me-1"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="small text-secondary">
                            {{ $user->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light rounded-pill px-2.5" title="Edit Pengguna">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if($user->id !== Auth::id())
                                    <button type="button" class="btn btn-sm btn-light text-danger rounded-pill px-2.5" 
                                            onclick="confirmDelete('{{ route('users.destroy', $user) }}', 'pengguna {{ $user->name }} ({{ $user->email }})')"
                                            title="Hapus Pengguna">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-secondary mb-2" style="font-size: 2.5rem;">
                                <i class="bi bi-people"></i>
                            </div>
                            <h6 class="fw-semibold text-dark">Tidak ada pengguna ditemukan</h6>
                            <p class="text-secondary small mb-3">Tidak ada data pengguna yang sesuai dengan kriteria filter.</p>
                            <a href="{{ route('users.create') }}" class="btn btn-sm btn-dark gk-btn-pill">
                                <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna Sekarang
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-secondary">
                    Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
                </span>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

@include('partials.confirm-delete')
@endsection
