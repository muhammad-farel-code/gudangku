@extends('layouts.app')

@section('title', 'Master Kategori')
@section('page_title', 'Master Kategori')

@section('search_action')
    <form method="GET" action="{{ route('categories.index') }}" class="gk-search-box d-none d-md-block">
        <i class="bi bi-search"></i>
        <input type="text"
               name="search"
               class="gk-search-input"
               placeholder="Cari nama kategori..."
               value="{{ request('search') }}">
    </form>
@endsection

@section('page_actions')
    @can('create', App\Models\Category::class)
        <a href="{{ route('categories.create') }}" class="btn-gk-dark">
            <span>Tambah Kategori</span>
            <div class="btn-icon-circle">
                <i class="bi bi-plus-lg"></i>
            </div>
        </a>
    @endcan
@endsection

@section('content')
<div class="container-fluid p-0">

    {{-- Mobile Search Form --}}
    <form method="GET" action="{{ route('categories.index') }}" class="d-md-none mb-3">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text"
                   name="search"
                   class="form-control border-start-0"
                   placeholder="Cari nama kategori..."
                   value="{{ request('search') }}">
            <button class="btn btn-gk-dark px-3" type="submit">Cari</button>
        </div>
    </form>

    <div class="gk-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="gk-card-title">Daftar Kategori Produk</h2>
                <span class="text-muted small">Total {{ $categories->total() }} kategori terdaftar</span>
            </div>
            @if(request('search'))
                <a href="{{ route('categories.index') }}" class="btn btn-sm btn-gk-outline">
                    <i class="bi bi-x-circle me-1"></i> Reset Pencarian
                </a>
            @endif
        </div>

        <div class="table-gk-wrapper">
            <table class="table-gk">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width: 140px;">Jumlah Produk</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $cat)
                        <tr>
                            <td class="text-muted small">{{ $categories->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="gk-thumb-avatar" style="background-color: var(--gk-surface);">
                                        <i class="bi bi-tag-fill text-dark"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('categories.show', $cat) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $cat->name }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small">
                                {{ $cat->description ?: '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border px-3 py-1 fw-semibold">
                                    {{ $cat->products_count }} item
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('categories.show', $cat) }}" class="gk-action-btn" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('update', $cat)
                                        <a href="{{ route('categories.edit', $cat) }}" class="gk-action-btn" title="Ubah Kategori">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $cat)
                                        <button type="button"
                                                class="gk-action-btn btn-delete"
                                                title="Hapus Kategori"
                                                onclick="openDeleteModal('{{ route('categories.destroy', $cat) }}', 'Kategori {{ $cat->name }}')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted mb-2" style="font-size: 2.5rem;">
                                    <i class="bi bi-folder-x"></i>
                                </div>
                                <h6 class="fw-semibold text-dark">Tidak ada data kategori</h6>
                                <p class="text-muted small mb-3">Belum ada kategori yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
                                @can('create', App\Models\Category::class)
                                    <a href="{{ route('categories.create') }}" class="btn btn-sm btn-gk-dark">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Baru
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($categories->hasPages())
            <div class="pt-4 border-top mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Menampilkan data {{ $categories->firstItem() }} sampai {{ $categories->lastItem() }} dari {{ $categories->total() }} data
                </span>
                <div>
                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
