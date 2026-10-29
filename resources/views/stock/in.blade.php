@extends('layouts.app')

@section('title', 'Pencatatan Stok Masuk (IN)')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}" class="text-decoration-none text-secondary">Riwayat Transaksi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Stok Masuk</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Pencatatan Stok Masuk</h1>
            <p class="text-secondary mb-0">Catat penerimaan barang baru dari pemasok atau mutasi masuk ke gudang penyimpanan.</p>
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
                <form method="POST" action="{{ route('stock.in.store') }}">
                    @csrf

                    <div class="row g-4">
                        {{-- Kolom Kiri: Input Utama --}}
                        <div class="col-12 col-lg-7">
                            <h5 class="fw-bold mb-3 text-dark">Data Barang Masuk</h5>

                            {{-- Pilihan Produk --}}
                            <div class="mb-3">
                                <label for="product_id" class="form-label fw-semibold">Pilih Produk <span class="text-danger">*</span></label>
                                <select class="form-select rounded-pill @error('product_id') is-invalid @enderror" 
                                        id="product_id" name="product_id" required onchange="updateProductDetails(this)">
                                    <option value="">-- Pilih Produk Aktif --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-sku="{{ $product->sku }}"
                                                data-unit="{{ $product->unit }}"
                                                data-stock="{{ $product->stock }}"
                                                data-supplier="{{ $product->supplier_id }}"
                                                data-category="{{ $product->category->name ?? '-' }}"
                                                {{ (old('product_id', $selectedProduct?->id) == $product->id) ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $product->stock }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Jumlah Masuk --}}
                            <div class="mb-3">
                                <label for="quantity" class="form-label fw-semibold">Jumlah Masuk <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" min="1" max="1000000" 
                                           class="form-control rounded-start-pill @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity') }}" 
                                           placeholder="Jumlah barang masuk..." required>
                                    <span class="input-group-text rounded-end-pill bg-light text-secondary" id="unit-badge">
                                        {{ $selectedProduct?->unit ?? 'unit' }}
                                    </span>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Pemasok --}}
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label fw-semibold">Pemasok (Supplier)</label>
                                <select class="form-select rounded-pill @error('supplier_id') is-invalid @enderror" 
                                        id="supplier_id" name="supplier_id">
                                    <option value="">-- Tanpa Pemasok Spesifik --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                {{ (old('supplier_id', $selectedProduct?->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small">Dapat menggunakan pemasok utama produk atau pemasok pengirim.</div>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tanggal Transaksi --}}
                            <div class="mb-3">
                                <label for="transaction_date" class="form-label fw-semibold">Tanggal Penerimaan <span class="text-danger">*</span></label>
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
                                <label for="notes" class="form-label fw-semibold">Catatan / No. Surat Jalan</label>
                                <textarea class="form-control rounded-4 @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Keterangan tambahan, nomor delivery order, atau kondisi barang saat diterima...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Kolom Kanan: Info Box Produk Terpilih --}}
                        <div class="col-12 col-lg-5">
                            <div class="card border-0 bg-light rounded-4 p-4 h-100">
                                <h6 class="fw-bold text-dark mb-3">Ringkasan Status Produk</h6>
                                
                                <div id="product-info-box">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="rounded-3 bg-white p-3 shadow-sm text-success">
                                            <i class="bi bi-box-arrow-in-down fs-3"></i>
                                        </div>
                                        <div>
                                            <div class="text-secondary small">Stok Saat Ini</div>
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

                                <div class="alert alert-info border-0 rounded-4 mt-auto small mb-0">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    <strong>Aturan Mutasi:</strong> Transaksi masuk akan langsung menambah jumlah stok fisik produk secara atomik dan menghasilkan nomor referensi otomatis unik (IN-YYYYMMDD-XXXX).
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
                            <i class="bi bi-check-lg me-1"></i> Simpan Stok Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateProductDetails(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) {
        document.getElementById('current-stock-display').innerText = '-';
        document.getElementById('current-unit-display').innerText = '';
        document.getElementById('unit-badge').innerText = 'unit';
        document.getElementById('sku-display').innerText = '-';
        document.getElementById('category-display').innerText = '-';
        return;
    }

    const sku = option.getAttribute('data-sku');
    const unit = option.getAttribute('data-unit');
    const stock = option.getAttribute('data-stock');
    const category = option.getAttribute('data-category');
    const supplierId = option.getAttribute('data-supplier');

    document.getElementById('current-stock-display').innerText = stock;
    document.getElementById('current-unit-display').innerText = unit;
    document.getElementById('unit-badge').innerText = unit;
    document.getElementById('sku-display').innerText = sku;
    document.getElementById('category-display').innerText = category;

    // Auto-select supplier jika belum dipilih
    const supplierSelect = document.getElementById('supplier_id');
    if (supplierId && !supplierSelect.value) {
        supplierSelect.value = supplierId;
    }
}
</script>
@endsection
