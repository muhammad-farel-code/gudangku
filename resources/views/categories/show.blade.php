@extends('layouts.app')

@section('title', 'Detail Kategori: ' . $category->name)
@section('page_title', 'Detail Kategori')

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('categories.index') }}" class="btn btn-gk-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        @can('update', $category)
            <a href="{{ route('categories.edit', $category) }}" class="btn btn-gk-dark">
                <i class="bi bi-pencil me-1"></i> Ubah Kategori
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row g-3 mb-4">
        {{-- Card Info Kategori --}}
        <div class="col-12 col-lg-4">
            <div class="gk-card h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="gk-stat-icon-wrap" style="width: 48px; height: 48px; border-radius: 12px; background-color: var(--gk-surface); color: var(--gk-text); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-0">{{ $category->name }}</h2>
                        <span class="text-muted small">ID: #{{ $category->id }}</span>
                    </div>
                </div>

                <div class="p-3 rounded-3 mb-3" style="background-color: var(--gk-surface);">
                    <div class="text-muted small mb-1">Total Produk Terdaftar</div>
                    <div class="h3 fw-bold text-dark mb-0">{{ $category->products_count }} <span class="fs-6 fw-normal text-muted">Item</span></div>
                </div>

                <div class="mb-3">
                    <span class="text-muted small fw-semibold d-block mb-1">Deskripsi:</span>
                    <p class="small text-secondary mb-0">
                        {{ $category->description ?: 'Tidak ada deskripsi khusus untuk kategori ini.' }}
                    </p>
                </div>

                <div class="pt-3 border-top small text-muted">
                    <div><i class="bi bi-clock me-1"></i> Dibuat: {{ $category->created_at->format('d M Y, H:i') }}</div>
                    <div><i class="bi bi-arrow-repeat me-1"></i> Terakhir diperbarui: {{ $category->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Tabel Produk di Kategori ini --}}
        <div class="col-12 col-lg-8">
            <div class="gk-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="gk-card-title">Daftar Produk Terkait</h3>
                        <span class="text-muted small">Produk yang tergabung dalam kategori {{ $category->name }}</span>
                    </div>
                </div>

                <div class="table-gk-wrapper">
                    <table class="table-gk">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Nama Produk</th>
                                <th>Stok</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $prod)
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $prod->sku }}</span></td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $prod->name }}</div>
                                        <div class="text-muted small">{{ $prod->rack_location ? 'Lokasi: ' . $prod->rack_location : '' }}</div>
                                    </td>
                                    <td class="fw-bold">{{ $prod->stock }} {{ $prod->unit }}</td>
                                    <td>Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$prod->stock_status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        Belum ada produk yang didaftarkan pada kategori ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="pt-3 border-top mt-3">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
