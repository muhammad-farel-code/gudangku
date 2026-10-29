@extends('layouts.app')

@section('title', 'Detail Pemasok: ' . $supplier->name)
@section('page_title', 'Detail Pemasok')

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('suppliers.index') }}" class="btn btn-gk-outline">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        @can('update', $supplier)
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-gk-dark">
                <i class="bi bi-pencil me-1"></i> Ubah Data
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row g-3 mb-4">
        {{-- Card Info Pemasok --}}
        <div class="col-12 col-lg-4">
            <div class="gk-card h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="gk-stat-icon-wrap" style="width: 48px; height: 48px; border-radius: 12px; background-color: var(--gk-surface); color: var(--gk-text); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-0">{{ $supplier->name }}</h2>
                        <span class="text-muted small">ID: #{{ $supplier->id }}</span>
                    </div>
                </div>

                <div class="p-3 rounded-3 mb-3" style="background-color: var(--gk-surface);">
                    <div class="text-muted small mb-1">Total Produk Dipasok</div>
                    <div class="h3 fw-bold text-dark mb-0">{{ $supplier->products_count }} <span class="fs-6 fw-normal text-muted">Item</span></div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small fw-semibold d-block">Kontak Person (PIC):</span>
                    <span class="text-dark">{{ $supplier->contact_person ?: '-' }}</span>
                </div>

                <div class="mb-2">
                    <span class="text-muted small fw-semibold d-block">Nomor Telepon:</span>
                    <span class="text-dark">{{ $supplier->phone ?: '-' }}</span>
                </div>

                <div class="mb-2">
                    <span class="text-muted small fw-semibold d-block">Alamat Email:</span>
                    <span class="text-dark">{{ $supplier->email ?: '-' }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small fw-semibold d-block">Alamat Kantor / Gudang:</span>
                    <span class="text-secondary small">{{ $supplier->address ?: '-' }}</span>
                </div>

                <div class="pt-3 border-top small text-muted">
                    <div><i class="bi bi-clock me-1"></i> Terdaftar sejak: {{ $supplier->created_at->format('d M Y, H:i') }}</div>
                    <div><i class="bi bi-arrow-repeat me-1"></i> Pembaruan terakhir: {{ $supplier->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Tabel Produk Terkait dari Pemasok ini --}}
        <div class="col-12 col-lg-8">
            <div class="gk-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="gk-card-title">Daftar Produk yang Dipasok</h3>
                        <span class="text-muted small">Katalog produk dari rekanan {{ $supplier->name }}</span>
                    </div>
                </div>

                <div class="table-gk-wrapper">
                    <table class="table-gk">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Nama Produk</th>
                                <th>Stok</th>
                                <th>Harga Beli</th>
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
                                        <div class="text-muted small">{{ $prod->category ? $prod->category->name : '-' }}</div>
                                    </td>
                                    <td class="fw-bold">{{ $prod->stock }} {{ $prod->unit }}</td>
                                    <td>Rp {{ number_format($prod->purchase_price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($prod->selling_price, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$prod->stock_status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        Belum ada produk yang dipasok oleh rekanan ini.
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
