@extends('layouts.app')

@section('title', 'Pencatatan Stok Keluar (OUT)')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}" class="text-decoration-none text-secondary">Riwayat Transaksi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Stok Keluar</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Pencatatan Stok Keluar</h1>
            <p class="text-secondary mb-0">Catat pengeluaran barang untuk penjualan, distribusi divisi, atau keperluan operasional.</p>
        </div>
        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light rounded-pill px-3">
            <i class="bi bi-clock-history me-1"></i> Lihat Riwayat
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('stock.out.store') }}">
                    @csrf

                    <div class="row g-4">
                        {{-- Kolom Kiri: Input Utama --}}
                        <div class="col-12 col-lg-7">
                            <h5 class="fw-bold mb-3 text-dark">Data Pengeluaran Barang</h5>

                            {{-- Pilihan Produk --}}
                            <div class="mb-3">
                                <label for="product_id" class="form-label fw-semibold">Pilih Produk <span class="text-danger">*</span></label>
                                <select class="form-select rounded-pill @error('product_id') is-invalid @enderror" 
                                        id="product_id" name="product_id" required onchange="updateProductDetails(this)">
                                    <option value="">-- Pilih Produk Tersedia --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-sku="{{ $product->sku }}"
                                                data-unit="{{ $product->unit }}"
                                                data-stock="{{ $product->stock }}"
                                                data-category="{{ $product->category->name ?? '-' }}"
                                                {{ (old('product_id', $selectedProduct?->id) == $product->id) ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->sku }}) - Tersedia: {{ $product->stock }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Jumlah Keluar --}}
                            <div class="mb-3">
                                <label for="quantity" class="form-label fw-semibold">Jumlah Keluar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" min="1" max="1000000" 
                                           class="form-control rounded-start-pill @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity') }}" 
                                           placeholder="Jumlah barang keluar..." required oninput="validateQuantityStock(this)">
                                    <span class="input-group-text rounded-end-pill bg-light text-secondary" id="unit-badge">
                                        {{ $selectedProduct?->unit ?? 'unit' }}
                                    </span>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="stock-warning" class="text-danger small mt-1 d-none">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Jumlah keluar melebihi stok yang tersedia saat ini!
                                </div>
                            </div>

                            {{-- Penerima / Divisi Tujuan --}}
                            <div class="mb-3">
                                <label for="recipient" class="form-label fw-semibold">Penerima / Tujuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-pill @error('recipient') is-invalid @enderror" 
                                       id="recipient" name="recipient" value="{{ old('recipient') }}" 
                                       placeholder="Contoh: Toko Cabang Bandung / Divisi Pemasaran / Bpk. Rudi" required>
                                <div class="form-text small">Nama penerima barang, divisi internal, atau tujuan pengiriman.</div>
                                @error('recipient')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tanggal Transaksi --}}
                            <div class="mb-3">
                                <label for="transaction_date" class="form-label fw-semibold">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                <input type="date" max="{{ date('Y-m-d') }}" 
                                       class="form-control rounded-pill @error('transaction_date') is-invalid @enderror" 
                                       id="transaction_date" name="transaction_date" 
                                       value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Catatan --}}
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-semibold">Catatan / Keperluan</label>
                                <textarea class="form-control rounded-4 @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Keterangan nomor faktur penjualan, nomor surat jalan keluar, atau alasan pengeluaran..."></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Kolom Kanan: Info Box Stok Tersedia --}}
                        <div class="col-12 col-lg-5">
                            <div class="card border-0 bg-light rounded-4 p-4 h-100">
                                <h6 class="fw-bold text-dark mb-3">Informasi Stok Tersedia</h6>
                                
                                <div id="product-info-box">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="rounded-3 bg-white p-3 shadow-sm text-danger">
                                            <i class="bi bi-box-arrow-up fs-3"></i>
                                        </div>
                                        <div>
                                            <div class="text-secondary small">Stok Fisik Tersedia</div>
                                            <div class="fs-4 fw-bold text-dark" id="current-stock-display">
                                                {{ $selectedProduct ? number_format($selectedProduct->stock) : '-' }}
                                                <span class="fs-6 fw-normal text-secondary" id="current-unit-display">{{ $selectedProduct?->unit ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                            <span class="text-secondary">Kode SKU</span>
                                            <span class="fw-semibold text-dark font-monospace" id="sku-display">{{ $selectedProduct?->sku ?? '-' }}</span>
                                        </li>
                                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                            <span class="text-secondary">Kategori</span>
                                            <span class="fw-semibold text-dark" id="category-display">{{ $selectedProduct?->category->name ?? '-' }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="alert alert-warning border-0 rounded-4 mt-auto small mb-0">
                                    <i class="bi bi-shield-lock-fill me-1"></i>
                                    <strong>Aturan BR1 & BR3:</strong> Stok tidak boleh bernilai negatif. Transaksi diproses dengan <em>pessimistic locking</em> (`lockForUpdate`) untuk memastikan tidak terjadi <em>oversell</em> saat pengeluaran bersamaan.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
                        <a href="{{ route('transactions.index') }}" class="btn btn-light rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Stok Keluar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let availableStock = {{ $selectedProduct ? (int) $selectedProduct->stock : 0 }};

function updateProductDetails(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) {
        document.getElementById('current-stock-display').innerText = '-';
        document.getElementById('current-unit-display').innerText = '';
        document.getElementById('unit-badge').innerText = 'unit';
        document.getElementById('sku-display').innerText = '-';
        document.getElementById('category-display').innerText = '-';
        availableStock = 0;
        return;
    }

    const sku = option.getAttribute('data-sku');
    const unit = option.getAttribute('data-unit');
    const stock = parseInt(option.getAttribute('data-stock'), 10);
    const category = option.getAttribute('data-category');

    availableStock = stock;
    document.getElementById('current-stock-display').innerText = stock;
    document.getElementById('current-unit-display').innerText = unit;
    document.getElementById('unit-badge').innerText = unit;
    document.getElementById('sku-display').innerText = sku;
    document.getElementById('category-display').innerText = category;

    validateQuantityStock(document.getElementById('quantity'));
}

function validateQuantityStock(input) {
    const qty = parseInt(input.value, 10);
    const warning = document.getElementById('stock-warning');
    if (!isNaN(qty) && qty > availableStock && availableStock > 0) {
        warning.classList.remove('d-none');
    } else {
        warning.classList.add('d-none');
    }
}
</script>
@endsection
