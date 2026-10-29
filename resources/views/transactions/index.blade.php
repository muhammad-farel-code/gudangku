@extends('layouts.app')

@section('title', 'Riwayat Transaksi Stok')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Riwayat Transaksi Stok</h1>
        <p class="text-secondary mb-0">Buku besar (ledger) mutasi barang masuk, keluar, dan penyesuaian stok yang tercatat secara permanen.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('stock.in') }}" class="btn btn-dark gk-btn-pill">
            <i class="bi bi-box-arrow-in-down me-1"></i> Stok Masuk
        </a>
        <a href="{{ route('stock.out') }}" class="btn btn-outline-dark rounded-pill">
            <i class="bi bi-box-arrow-up me-1"></i> Stok Keluar
        </a>
        @if(Auth::user()->hasRole('admin', 'manager'))
            <a href="{{ route('stock.adjustment') }}" class="btn btn-outline-warning rounded-pill text-dark">
                <i class="bi bi-sliders me-1"></i> Penyesuaian
            </a>
        @endif
    </div>
</div>

{{-- Bar Filter Komprehensif --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-center">
            {{-- Search Bar --}}
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0" 
                           placeholder="Cari No. Ref, produk, penerima..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filter Tipe --}}
            <div class="col-6 col-md-2">
                <select name="type" class="form-select bg-light border-0">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stok Masuk (IN)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stok Keluar (OUT)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian (ADJ)</option>
                </select>
            </div>

            {{-- Filter Produk --}}
            <div class="col-6 col-md-3">
                <select name="product_id" class="form-select bg-light border-0">
                    <option value="">Semua Produk</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }} ({{ $prod->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tanggal Mulai --}}
            <div class="col-6 col-md-2">
                <input type="date" name="start_date" class="form-control bg-light border-0" 
                       value="{{ request('start_date') }}" title="Tanggal Mulai">
            </div>

            {{-- Filter Tanggal Selesai & Aksi --}}
            <div class="col-6 col-md-2 d-flex gap-2">
                <input type="date" name="end_date" class="form-control bg-light border-0" 
                       value="{{ request('end_date') }}" title="Tanggal Akhir">
                <button type="submit" class="btn btn-dark rounded-pill px-3" title="Terapkan Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                @if(request()->hasAny(['search', 'type', 'product_id', 'start_date', 'end_date']))
                    <a href="{{ route('transactions.index') }}" class="btn btn-light rounded-pill px-3" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tabel Transaksi Ledger --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">No. Referensi</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Tipe</th>
                    <th scope="col">Produk</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Perubahan Stok</th>
                    <th scope="col">Pihak Terkait</th>
                    <th scope="col">Dicatat Oleh</th>
                    <th scope="col" class="text-end pe-4">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('transactions.show', $tx) }}" class="font-monospace fw-bold text-dark text-decoration-none">
                                {{ $tx->reference_no }}
                            </a>
                        </td>
                        <td class="small text-secondary">
                            {{ $tx->transaction_date->format('d M Y') }}
                        </td>
                        <td>
                            @if($tx->type === 'in')
                                <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-arrow-down-left me-1"></i> Masuk
                                </span>
                            @elseif($tx->type === 'out')
                                <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-arrow-up-right me-1"></i> Keluar
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1 rounded-pill">
                                    <i class="bi bi-sliders me-1"></i> Penyesuaian
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $tx->product->name ?? 'Produk Dihapus' }}</div>
                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.75rem;">
                                {{ $tx->product->sku ?? '-' }}
                            </span>
                        </td>
                        <td class="fw-bold">
                            @if($tx->type === 'in')
                                <span class="text-success">+{{ number_format($tx->quantity) }}</span>
                            @elseif($tx->type === 'out')
                                <span class="text-danger">-{{ number_format($tx->quantity) }}</span>
                            @else
                                <span class="text-warning-emphasis">&Delta; {{ number_format($tx->quantity) }}</span>
                            @endif
                            <span class="small text-secondary fw-normal">{{ $tx->product->unit ?? '' }}</span>
                        </td>
                        <td>
                            <div class="small text-secondary">
                                <span class="text-muted">{{ $tx->stock_before }}</span>
                                <i class="bi bi-arrow-right mx-1 text-secondary"></i>
                                <strong class="text-dark">{{ $tx->stock_after }}</strong>
                            </div>
                        </td>
                        <td class="small">
                            @if($tx->type === 'in')
                                <span class="text-secondary"><i class="bi bi-truck me-1"></i>{{ $tx->supplier->name ?? 'Tanpa Pemasok' }}</span>
                            @elseif($tx->type === 'out')
                                <span class="text-secondary"><i class="bi bi-person me-1"></i>{{ $tx->recipient ?: '—' }}</span>
                            @else
                                <span class="text-muted"><i class="bi bi-clipboard-check me-1"></i>Stock Opname</span>
                            @endif
                        </td>
                        <td class="small text-secondary">
                            {{ $tx->user->name ?? 'Sistem' }}
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('transactions.show', $tx) }}" class="btn btn-sm btn-light rounded-pill px-3" title="Lihat Tanda Bukti">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-secondary mb-2" style="font-size: 2.5rem;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <h6 class="fw-semibold text-dark">Tidak ada transaksi ditemukan</h6>
                            <p class="text-secondary small mb-3">Belum ada riwayat transaksi yang cocok dengan kriteria filter yang Anda tentukan.</p>
                            <a href="{{ route('stock.in') }}" class="btn btn-sm btn-dark gk-btn-pill">
                                <i class="bi bi-plus-lg me-1"></i> Catat Stok Masuk Baru
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-secondary">
                    Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi
                </span>
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
@endsection
