@extends('layouts.app')

@section('title', 'Bukti Transaksi: ' . $stockTransaction->reference_no)
@section('page_title', 'Detail Transaksi')

@section('page_actions')
    <div class="d-flex gap-2">
        <x-back-button :route="route('transactions.index')" />
        <button type="button" class="btn btn-gk-dark" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Bukti
        </button>
    </div>
@endsection

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}" class="text-decoration-none text-secondary">Riwayat Transaksi</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $stockTransaction->reference_no }}</li>
        </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold mb-0 font-monospace">{{ $stockTransaction->reference_no }}</h1>
                @if($stockTransaction->type === 'in')
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5">
                        <i class="bi bi-arrow-down-left me-1"></i> Stok Masuk
                    </span>
                @elseif($stockTransaction->type === 'out')
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5">
                        <i class="bi bi-arrow-up-right me-1"></i> Stok Keluar
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1.5">
                        <i class="bi bi-sliders me-1"></i> Penyesuaian Opname
                    </span>
                @endif
            </div>
            <p class="text-secondary mb-0">
                Tanggal Transaksi: <strong class="text-dark">{{ $stockTransaction->transaction_date->format('d F Y') }}</strong> &bull; 
                Dicatat Pada: <span class="text-dark">{{ $stockTransaction->created_at->format('d/m/Y H:i:s') }} WIB</span>
            </p>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        {{-- Card Faktur Bukti Mutasi --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-body p-4 p-md-5">
                {{-- Header Nota --}}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 bg-dark text-white p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-box-seam-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">GudangKu</h5>
                            <span class="small text-secondary">Sistem Manajemen Inventaris Gudang</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-secondary border font-monospace px-3 py-1.5">
                            LEDGER DOKUMEN MUTASI
                        </span>
                    </div>
                </div>

                {{-- Ringkasan Mutasi --}}
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded-4">
                            <span class="text-secondary small fw-medium">Jumlah Mutasi</span>
                            <div class="fs-2 fw-bold mt-1 {{ $stockTransaction->type === 'in' ? 'text-success' : ($stockTransaction->type === 'out' ? 'text-danger' : 'text-warning-emphasis') }}">
                                {{ $stockTransaction->type === 'in' ? '+' : ($stockTransaction->type === 'out' ? '-' : '±') }}
                                {{ number_format($stockTransaction->quantity) }}
                                <span class="fs-6 fw-normal text-secondary">{{ $stockTransaction->product->unit ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded-4">
                            <span class="text-secondary small fw-medium">Posisi Stok</span>
                            <div class="fs-4 fw-bold mt-1 text-dark">
                                {{ number_format($stockTransaction->stock_before) }}
                                <i class="bi bi-arrow-right text-secondary mx-1 fs-5"></i>
                                <span class="text-primary">{{ number_format($stockTransaction->stock_after) }}</span>
                                <span class="fs-6 fw-normal text-secondary">{{ $stockTransaction->product->unit ?? '' }}</span>
                            </div>
                            <div class="small text-muted mt-1">Stok sebelum &rarr; setelah transaksi</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded-4">
                            <span class="text-secondary small fw-medium">Petugas Pencatat</span>
                            <div class="fs-5 fw-bold mt-1 text-dark">
                                {{ $stockTransaction->user->name ?? 'Sistem' }}
                            </div>
                            <div class="small text-secondary">
                                Role: <span class="badge bg-secondary-subtle text-secondary">{{ $stockTransaction->user->role ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Rincian --}}
                <h6 class="fw-bold text-dark mb-3">Detail Informasi Barang & Pihak Terkait</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <tbody class="small">
                            <tr>
                                <th class="bg-light text-secondary" style="width: 25%;">Nama Produk</th>
                                <td class="fw-semibold text-dark">
                                    <a href="{{ route('products.show', $stockTransaction->product) }}" class="text-decoration-none text-dark">
                                        {{ $stockTransaction->product->name ?? 'Produk Dihapus' }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Kode SKU</th>
                                <td class="font-monospace fw-semibold">{{ $stockTransaction->product->sku ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Kategori Produk</th>
                                <td>{{ $stockTransaction->product->category->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Lokasi Rak Gudang</th>
                                <td>{{ $stockTransaction->product->rack_location ?: 'Belum Diatur' }}</td>
                            </tr>
                            @if($stockTransaction->type === 'in')
                                <tr>
                                    <th class="bg-light text-secondary">Pemasok (Supplier)</th>
                                    <td class="fw-semibold text-dark">{{ $stockTransaction->supplier->name ?? 'Tanpa Pemasok' }}</td>
                                </tr>
                            @elseif($stockTransaction->type === 'out')
                                <tr>
                                    <th class="bg-light text-secondary">Penerima / Tujuan</th>
                                    <td class="fw-semibold text-dark">{{ $stockTransaction->recipient ?: '—' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light text-secondary">Catatan / Keterangan</th>
                                <td class="text-dark">{{ $stockTransaction->notes ?: 'Tidak ada catatan tambahan.' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Ledger Immutable Notice --}}
                <div class="alert alert-secondary border-0 rounded-4 d-flex align-items-center gap-3 mb-0 small">
                    <i class="bi bi-shield-check fs-3 text-secondary"></i>
                    <div>
                        <strong>Catatan Integritas Data (Immutable Ledger):</strong> Dokumen transaksi ini telah dicatat secara permanen di dalam basis data persediaan dan tidak dapat diubah atau dihapus. Jika terjadi kesalahan input, koreksi dilakukan melalui modul <em>Penyesuaian Stok (Stock Opname)</em> oleh Manager atau Admin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
