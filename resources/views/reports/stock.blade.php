@extends('layouts.app')

@section('title', 'Laporan Stok Inventaris')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Laporan Stok Inventaris</h1>
        <p class="text-secondary mb-0">Laporan posisi kuantitas stok fisik, nilai aset gudang, dan status ketersediaan barang.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.stock.print', request()->all()) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-3">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
        <a href="{{ route('reports.stock.export', request()->all()) }}" class="btn btn-dark gk-btn-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.stock') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <select name="category_id" class="form-select bg-light border-0">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-5">
                <select name="status" class="form-select bg-light border-0">
                    <option value="">Semua Status Stok</option>
                    <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Stok Menipis (&le; Batas Minimum)</option>
                    <option value="out" {{ request('status') === 'out' ? 'selected' : '' }}>Stok Habis (0 unit)</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark gk-btn-pill w-100">
                    Filter
                </button>
                @if(request()->hasAny(['category_id', 'status']))
                    <a href="{{ route('reports.stock') }}" class="btn btn-light rounded-pill px-3" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Stat Cards Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="small text-secondary fw-semibold">Jumlah Varian Produk</span>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($products->count()) }}</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="small text-secondary fw-semibold">Total Unit Fisik Tersedia</span>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($totalUnits) }} <span class="fs-6 fw-normal text-secondary">unit</span></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="small text-secondary fw-semibold">Estimasi Total Nilai Persediaan</span>
            <div class="fs-3 fw-bold text-success mt-1">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- Tabel Laporan Stok --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">Kode SKU</th>
                    <th scope="col">Nama Produk</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Lokasi Rak</th>
                    <th scope="col" class="text-end">Stok Fisik</th>
                    <th scope="col" class="text-end">Min. Stok</th>
                    <th scope="col" class="text-end">Harga Beli</th>
                    <th scope="col" class="text-end">Harga Jual</th>
                    <th scope="col" class="text-end">Total Nilai</th>
                    <th scope="col" class="text-center pe-4">Status</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($products as $index => $p)
                    <tr>
                        <td class="ps-4 text-secondary">{{ $index + 1 }}</td>
                        <td class="font-monospace fw-semibold">{{ $p->sku }}</td>
                        <td class="fw-semibold text-dark">{{ $p->name }}</td>
                        <td>{{ $p->category->name ?? '-' }}</td>
                        <td><span class="badge bg-light text-secondary border">{{ $p->rack_location ?: '-' }}</span></td>
                        <td class="text-end fw-bold {{ $p->stock <= $p->min_stock ? 'text-danger' : 'text-dark' }}">
                            {{ number_format($p->stock) }} {{ $p->unit }}
                        </td>
                        <td class="text-end text-secondary">{{ $p->min_stock }}</td>
                        <td class="text-end text-secondary">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-end text-secondary">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold text-dark">Rp {{ number_format($p->stock * $p->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-center pe-4">
                            @if($p->stock_status === 'Habis')
                                <span class="badge bg-danger-subtle text-danger rounded-pill">Habis</span>
                            @elseif($p->stock_status === 'Menipis')
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Menipis</span>
                            @else
                                <span class="badge bg-success-subtle text-success rounded-pill">Aman</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-secondary">
                            Tidak ada produk yang memenuhi kriteria filter laporan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($products->isNotEmpty())
                <tfoot class="table-light fw-bold small">
                    <tr>
                        <td colspan="5" class="ps-4">TOTAL KESELURUHAN</td>
                        <td class="text-end text-dark">{{ number_format($totalUnits) }} unit</td>
                        <td colspan="3"></td>
                        <td class="text-end text-success">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                        <td class="pe-4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
