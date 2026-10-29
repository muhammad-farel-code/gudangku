@extends('layouts.app')

@section('title', 'Laporan Mutasi Transaksi')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Laporan Mutasi Transaksi</h1>
        <p class="text-secondary mb-0">Laporan historis seluruh pergerakan barang masuk, keluar, dan penyesuaian opname persediaan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.transactions.print', request()->all()) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-3">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
        <a href="{{ route('reports.transactions.export', request()->all()) }}" class="btn btn-dark gk-btn-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reports.transactions') }}" class="row g-2 align-items-center">
            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ request('start_date') }}">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control bg-light border-0" value="{{ request('end_date') }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-secondary mb-1">Tipe Mutasi</label>
                <select name="type" class="form-select bg-light border-0">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk (IN)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar (OUT)</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian (ADJ)</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small text-secondary mb-1">Pilih Produk</label>
                <select name="product_id" class="form-select bg-light border-0">
                    <option value="">Semua Produk</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-dark gk-btn-pill w-100" title="Terapkan Filter">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                @if(request()->hasAny(['start_date', 'end_date', 'type', 'product_id']))
                    <a href="{{ route('reports.transactions') }}" class="btn btn-light rounded-pill px-2.5" title="Reset">
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
            <span class="small text-secondary fw-semibold">Total Mutasi Transaksi</span>
            <div class="fs-3 fw-bold text-dark mt-1">{{ number_format($transactions->count()) }}</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="small text-secondary fw-semibold">Total Barang Masuk (IN)</span>
            <div class="fs-3 fw-bold text-success mt-1">+{{ number_format($totalIn) }} <span class="fs-6 fw-normal text-secondary">unit</span></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="small text-secondary fw-semibold">Total Barang Keluar (OUT)</span>
            <div class="fs-3 fw-bold text-danger mt-1">-{{ number_format($totalOut) }} <span class="fs-6 fw-normal text-secondary">unit</span></div>
        </div>
    </div>
</div>

{{-- Tabel Laporan Transaksi --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th scope="col" class="ps-4">No</th>
                    <th scope="col">No. Referensi</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Tipe</th>
                    <th scope="col">Produk</th>
                    <th scope="col" class="text-end">Jumlah</th>
                    <th scope="col" class="text-center">Perubahan Stok</th>
                    <th scope="col">Pihak Terkait</th>
                    <th scope="col">Petugas</th>
                    <th scope="col" class="pe-4">Catatan</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($transactions as $index => $tx)
                    <tr>
                        <td class="ps-4 text-secondary">{{ $index + 1 }}</td>
                        <td class="font-monospace fw-semibold">{{ $tx->reference_no }}</td>
                        <td class="text-secondary">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td>
                            @if($tx->type === 'in')
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">IN</span>
                            @elseif($tx->type === 'out')
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1">OUT</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1">ADJ</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $tx->product->name ?? '-' }}</div>
                            <span class="text-secondary" style="font-size: 0.72rem;">{{ $tx->product->sku ?? '-' }}</span>
                        </td>
                        <td class="text-end fw-bold {{ $tx->type === 'in' ? 'text-success' : ($tx->type === 'out' ? 'text-danger' : 'text-warning-emphasis') }}">
                            {{ $tx->type === 'in' ? '+' : ($tx->type === 'out' ? '-' : '±') }}{{ number_format($tx->quantity) }}
                        </td>
                        <td class="text-center text-secondary">
                            {{ $tx->stock_before }} &rarr; <strong class="text-dark">{{ $tx->stock_after }}</strong>
                        </td>
                        <td>
                            @if($tx->type === 'in')
                                {{ $tx->supplier->name ?? 'Tanpa Pemasok' }}
                            @elseif($tx->type === 'out')
                                {{ $tx->recipient ?: '-' }}
                            @else
                                <span class="text-muted">Stock Opname</span>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $tx->user->name ?? '-' }}</td>
                        <td class="pe-4 text-muted text-truncate" style="max-width: 200px;">{{ $tx->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-secondary">
                            Tidak ada data transaksi yang sesuai dengan filter laporan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
