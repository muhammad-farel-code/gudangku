@extends('layouts.app')

@section('title', 'Master Pemasok')
@section('page_title', 'Master Pemasok')

@section('search_action')
    <form method="GET" action="{{ route('suppliers.index') }}" class="gk-search-box d-none d-md-block">
        <i class="bi bi-search"></i>
        <input type="text"
               name="search"
               class="gk-search-input"
               placeholder="Cari nama, kontak, email..."
               value="{{ request('search') }}">
    </form>
@endsection

@section('page_actions')
    @can('create', App\Models\Supplier::class)
        <a href="{{ route('suppliers.create') }}" class="btn-gk-dark">
            <span>Tambah Pemasok</span>
            <div class="btn-icon-circle">
                <i class="bi bi-plus-lg"></i>
            </div>
        </a>
    @endcan
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- Mobile Search Form --}}
    <form method="GET" action="{{ route('suppliers.index') }}" class="d-md-none mb-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text"
                   name="search"
                   class="form-control border-start-0"
                   placeholder="Cari nama, kontak..."
                   value="{{ request('search') }}">
            <button class="btn btn-gk-dark px-3" type="submit">Cari</button>
        </div>
    </form>

    <div class="gk-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="gk-card-title">Daftar Rekanan Pemasok</h2>
                <span class="text-muted small">Total {{ $suppliers->total() }} vendor pemasok terdaftar</span>
            </div>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-gk-outline">
                    <i class="bi bi-x-circle me-1"></i> Reset Pencarian
                </a>
            @endif
        </div>

        <div class="table-gk-wrapper">
            <table class="table-gk">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Pemasok</th>
                        <th>Kontak Person</th>
                        <th>Kontak / Telepon</th>
                        <th>Alamat Gudang / Kantor</th>
                        <th class="text-center">Produk</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $sup)
                        <tr>
                            <td class="text-muted small">{{ $suppliers->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="gk-thumb-avatar" style="background-color: var(--gk-surface);">
                                        <i class="bi bi-truck text-dark"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('suppliers.show', $sup) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $sup->name }}
                                        </a>
                                        @if($sup->email)
                                            <div class="text-muted small">{{ $sup->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium text-dark small">
                                    <i class="bi bi-person me-1 text-muted"></i>{{ $sup->contact_person ?: '-' }}
                                </span>
                            </td>
                            <td class="small">
                                <div><i class="bi bi-telephone me-1 text-muted"></i>{{ $sup->phone ?: '-' }}</div>
                            </td>
                            <td class="text-muted small" style="max-width: 250px;">
                                <span class="d-inline-block text-truncate" style="max-width: 240px;" title="{{ $sup->address }}">
                                    {{ $sup->address ?: '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border px-3 py-1 fw-semibold">
                                    {{ $sup->products_count }} item
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('suppliers.show', $sup) }}" class="gk-action-btn" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('update', $sup)
                                        <a href="{{ route('suppliers.edit', $sup) }}" class="gk-action-btn" title="Ubah Pemasok">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $sup)
                                        <button type="button"
                                                class="gk-action-btn btn-delete"
                                                title="Hapus Pemasok"
                                                onclick="openDeleteModal('{{ route('suppliers.destroy', $sup) }}', 'Pemasok {{ $sup->name }}')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-2" style="font-size: 2.5rem;">
                                    <i class="bi bi-truck-flatbed"></i>
                                </div>
                                <h6 class="fw-semibold text-dark">Tidak ada data pemasok</h6>
                                <p class="text-muted small mb-3">Belum ada mitra pemasok yang cocok dengan kata kunci pencarian Anda.</p>
                                @can('create', App\Models\Supplier::class)
                                    <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-gk-dark">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Pemasok Baru
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($suppliers->hasPages())
            <div class="pt-4 border-top mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Menampilkan data {{ $suppliers->firstItem() }} sampai {{ $suppliers->lastItem() }} dari {{ $suppliers->total() }} data
                </span>
                <div>
                    {{ $suppliers->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
