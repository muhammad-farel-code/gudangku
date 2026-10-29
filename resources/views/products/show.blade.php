@extends('layouts.app')

@section('title', 'Detail Produk: ' . $product->name)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-secondary">Produk</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold mb-0">{{ $product->name }}</h1>
                <span class="badge bg-light text-dark border">{{ $product->sku }}</span>
                @if($product->is_active)
                    <span class="badge bg-success-subtle text-success rounded-pill">Aktif</span>
                @else
                    <span class="badge bg-danger-subtle text-danger rounded-pill">Nonaktif</span>
                @endif
            </div>
            <p class="text-secondary mb-0">
                Kategori: <strong class="text-dark">{{ $product->category->name ?? '—' }}</strong> &bull; 
                Pemasok: <strong class="text-dark">{{ $product->supplier->name ?? 'Tanpa Pemasok' }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            @if(Auth::user()->hasRole('admin', 'manager'))
                <a href="{{ route('products.edit', $product) }}" class="btn btn-dark gk-btn-pill px-3">
                    <i class="bi bi-pencil me-1"></i> Edit Produk
                </a>
                <button type="button" class="btn btn-outline-danger rounded-pill px-3" 
                        onclick="confirmDelete('{{ route('products.destroy', $product) }}', 'produk {{ $product->name }} (SKU: {{ $product->sku }})')">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            @endif
        </div>
    </div>
</div>

{{-- 4 Stat Cards --}}
<div class="row g-3 mb-4">
    {{-- Stok Saat Ini --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-medium">Stok Saat Ini</span>
                    @if($product->stock_status === 'Habis')
                        <span class="badge bg-danger-subtle text-danger rounded-pill">Habis</span>
                    @elseif($product->stock_status === 'Menipis')
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Menipis</span>
                    @else
                        <span class="badge bg-success-subtle text-success rounded-pill">Aman</span>
                    @endif
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-2 fw-bold text-dark">{{ number_format($product->stock) }}</span>
                    <span class="text-secondary fw-medium">{{ $product->unit }}</span>
                </div>
                <div class="small text-muted mt-1">Min. stok: {{ $product->min_stock }} {{ $product->unit }}</div>
            </div>
        </div>
    </div>

    {{-- Harga Jual --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-medium">Harga Jual</span>
                    <i class="bi bi-tag text-secondary"></i>
                </div>
                <div class="fs-3 fw-bold text-dark">
                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">Per {{ $product->unit }}</div>
            </div>
        </div>
    </div>

    {{-- Harga Beli / Informasi Khusus Manager & Admin --}}
    @if(Auth::user()->hasRole('admin', 'manager'))
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-medium">Harga Beli</span>
                        <i class="bi bi-wallet2 text-secondary"></i>
                    </div>
                    <div class="fs-3 fw-bold text-dark">
                        Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                    </div>
                    <div class="small text-success mt-1">
                        Margin: Rp {{ number_format($product->selling_price - $product->purchase_price, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Estimasi Nilai Inventaris Produk Ini --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-medium">Total Nilai Aset</span>
                        <i class="bi bi-cash-stack text-secondary"></i>
                    </div>
                    <div class="fs-3 fw-bold text-dark">
                        Rp {{ number_format($product->stock * $product->purchase_price, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted mt-1">Stok &times; Harga Beli</div>
                </div>
            </div>
        </div>
    @else
        {{-- Tampilan alternatif untuk Staff (tidak melihat harga beli) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-medium">Lokasi Rak</span>
                        <i class="bi bi-geo-alt text-secondary"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark">
                        {{ $product->rack_location ?: 'Belum Diatur' }}
                    </div>
                    <div class="small text-muted mt-1">Penempatan gudang</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-medium">Status Operasional</span>
                        <i class="bi bi-shield-check text-secondary"></i>
                    </div>
                    <div class="fs-5 fw-bold {{ $product->is_active ? 'text-success' : 'text-danger' }}">
                        {{ $product->is_active ? 'Siap Ditransaksikan' : 'Dinonaktifkan' }}
                    </div>
                    <div class="small text-muted mt-1">Katalog sistem</div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Detail Spesifikasi & Transaksi Terakhir --}}
<div class="row g-4">
    {{-- Kolom Kiri: Detail Spesifikasi --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 text-dark">Spesifikasi & Informasi</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Kode SKU</span>
                        <span class="fw-semibold text-dark font-monospace">{{ $product->sku }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Kategori</span>
                        <span class="fw-semibold text-dark">{{ $product->category->name ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Pemasok Utama</span>
                        <span class="fw-semibold text-dark">{{ $product->supplier->name ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Lokasi Rak</span>
                        <span class="badge bg-light text-secondary border">{{ $product->rack_location ?: '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Satuan</span>
                        <span class="fw-semibold text-dark">{{ $product->unit }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Dibuat Pada</span>
                        <span class="text-dark small">{{ $product->created_at->format('d M Y, H:i') }}</span>
                    </li>
                    <li class="list-group-item px-0 py-2.5 d-flex justify-content-between border-light">
                        <span class="text-secondary">Terakhir Diperbarui</span>
                        <span class="text-dark small">{{ $product->updated_at->format('d M Y, H:i') }}</span>
                    </li>
                </ul>

                @if($product->description)
                    <div class="mt-4 pt-3 border-top">
                        <div class="small fw-semibold text-secondary text-uppercase mb-2">Deskripsi Produk</div>
                        <p class="text-dark small mb-0 lh-base">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Riwayat Transaksi Terakhir --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Riwayat Transaksi Terakhir</h5>
                    <span class="badge bg-light text-secondary">Maks 10 data</span>
                </div>

                @if($product->stockTransactions->isEmpty())
                    <div class="text-center py-5 text-secondary">
                        <i class="bi bi-clock-history fs-1 mb-2"></i>
                        <p class="mb-0 small">Belum ada catatan mutasi transaksi stok untuk produk ini.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Kode</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Waktu</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->stockTransactions as $tx)
                                    <tr>
                                        <td class="font-monospace small">{{ $tx->reference_no }}</td>
                                        <td>
                                            @if($tx->type === 'in')
                                                <span class="badge bg-success-subtle text-success">Masuk</span>
                                            @elseif($tx->type === 'out')
                                                <span class="badge bg-danger-subtle text-danger">Keluar</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Penyesuaian</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">
                                            {{ $tx->type === 'in' ? '+' : ($tx->type === 'out' ? '-' : '') }}{{ $tx->quantity }} {{ $product->unit }}
                                        </td>
                                        <td class="small text-secondary">{{ $tx->created_at->format('d/m/y H:i') }}</td>
                                        <td class="small text-secondary">{{ $tx->user->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.confirm-delete')
@endsection
