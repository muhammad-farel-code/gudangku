@extends('layouts.app')

@section('title', 'Data Produk')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Master Produk</h1>
        <p class="text-secondary mb-0">Kelola katalog barang, pantau status stok, harga, dan penempatan rak gudang.</p>
    </div>
    @if(Auth::user()->hasRole('admin', 'manager'))
        <a href="{{ route('products.create') }}" class="btn btn-dark gk-btn-pill">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </a>
    @endif
</div>

{{-- Bar Pencarian & Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
            {{-- Search by Name / SKU --}}
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0" 
                           placeholder="Cari SKU atau nama produk..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filter Kategori --}}
            <div class="col-12 col-md-3">
                <select name="category_id" class="form-select bg-light border-0">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Stok Menipis --}}
            <div class="col-12 col-md-3">
                <div class="form-check form-switch d-flex align-items-center gap-2 pt-1">
                    <input class="form-check-input" type="checkbox" role="switch" id="low_stock" name="low_stock" value="1" 
                           {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label small fw-medium" for="low_stock">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Stok Menipis / Habis
                    </label>
                </div>
            </div>

            {{-- Tombol Aksi Filter --}}
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark gk-btn-pill w-100">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'low_stock', 'is_active']))
                    <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-3" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Data Produk --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">Produk</th>
                    <th scope="col">Kategori & Pemasok</th>
                    <th scope="col">Stok</th>
                    <th scope="col">Status Stok</th>
                    <th scope="col">Harga Jual</th>
                    @if(Auth::user()->hasRole('admin', 'manager'))
                        <th scope="col">Harga Beli</th>
                    @endif
                    <th scope="col">Rak</th>
                    <th scope="col" class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center fw-bold text-secondary"
                                     style="width: 44px; height: 44px; font-size: 1.1rem;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <a href="{{ route('products.show', $product) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                    <div class="small text-secondary">
                                        <span class="badge bg-light text-dark border">{{ $product->sku }}</span>
                                        @if(!$product->is_active)
                                            <span class="badge bg-danger-subtle text-danger ms-1">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $product->category->name ?? '—' }}</div>
                            <div class="small text-secondary">{{ $product->supplier->name ?? 'Tanpa Pemasok' }}</div>
                        </td>
                        <td>
                            <span class="fw-bold fs-6 {{ $product->stock <= $product->min_stock ? 'text-danger' : 'text-dark' }}">
                                {{ number_format($product->stock) }}
                            </span>
                            <span class="small text-secondary">{{ $product->unit }}</span>
                            <div class="small text-muted" style="font-size: 0.75rem;">Min: {{ $product->min_stock }}</div>
                        </td>
                        <td>
                            @if($product->stock_status === 'Habis')
                                <span class="badge bg-danger-subtle text-danger px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Habis
                                </span>
                            @elseif($product->stock_status === 'Menipis')
                                <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-exclamation-circle me-1"></i> Menipis
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> Aman
                                </span>
                            @endif
                        </td>
                        <td class="fw-semibold text-dark">
                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        </td>
                        @if(Auth::user()->hasRole('admin', 'manager'))
                            <td class="text-secondary small">
                                Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                            </td>
                        @endif
                        <td>
                            <span class="badge bg-light text-secondary border">
                                <i class="bi bi-geo-alt me-1"></i>{{ $product->rack_location ?: '—' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light rounded-pill" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(Auth::user()->hasRole('admin', 'manager'))
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-light rounded-pill" title="Edit Produk">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-danger rounded-pill" 
                                            onclick="confirmDelete('{{ route('products.destroy', $product) }}', 'produk {{ $product->name }} (SKU: {{ $product->sku }})')"
                                            title="Hapus Produk">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ Auth::user()->hasRole('admin', 'manager') ? 8 : 7 }}" class="text-center py-5">
                            <div class="text-secondary mb-2" style="font-size: 2.5rem;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h6 class="fw-semibold text-dark">Belum ada data produk</h6>
                            <p class="text-secondary small mb-3">Tidak ditemukan data produk yang sesuai dengan kriteria filter.</p>
                            @if(Auth::user()->hasRole('admin', 'manager'))
                                <a href="{{ route('products.create') }}" class="btn btn-sm btn-dark gk-btn-pill">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk Sekarang
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-secondary">
                    Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
                </span>
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

@include('partials.confirm-delete')
@endsection
