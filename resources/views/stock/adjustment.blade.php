@extends('layouts.app')

@section('title', 'Penyesuaian Stok (Stock Opname)')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}" class="text-decoration-none text-secondary">Riwayat Transaksi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Penyesuaian Stok</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Penyesuaian Stok (Opname)</h1>
            <p class="text-secondary mb-0">Sinkronkan catatan stok sistem dengan hasil penghitungan fisik nyata di gudang penyimpanan.</p>
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
                <form method="POST" action="{{ route('stock.adjustment.store') }}">
                    @csrf

                    <div class="row g-4">
                        {{-- Kolom Kiri: Input Form --}}
                        <div class="col-12 col-lg-7">
                            <h5 class="fw-bold mb-3 text-dark">Formulir Koreksi Stok Opname</h5>

                            {{-- Pilihan Produk --}}
                            <div class="mb-3">
                                <label for="product_id" class="form-label fw-semibold">Pilih Produk <span class="text-danger">*</span></label>
                                <select class="form-select rounded-pill @error('product_id') is-invalid @enderror" 
                                        id="product_id" name="product_id" required onchange="updateProductDetails(this)">
                                    <option value="">-- Pilih Produk yang Disesuaikan --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-sku="{{ $product->sku }}"
                                                data-unit="{{ $product->unit }}"
                                                data-stock="{{ $product->stock }}"
                                                data-category="{{ $product->category->name ?? '-' }}"
                                                {{ (old('product_id', $selectedProduct?->id) == $product->id) ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->sku }}) - Stok Sistem: {{ $product->stock }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Stok Fisik Aktual --}}
                            <div class="mb-3">
                                <label for="actual_stock" class="form-label fw-semibold">Jumlah Stok Fisik Nyata <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" min="0" max="1000000" 
                                           class="form-control rounded-start-pill @error('actual_stock') is-invalid @enderror" 
                                           id="actual_stock" name="actual_stock" value="{{ old('actual_stock') }}" 
                                           placeholder="Hasil hitung fisik..." required oninput="calculateDifference()">
                                    <span class="input-group-text rounded-end-pill bg-light text-secondary" id="unit-badge">
                                        {{ $selectedProduct?->unit ?? 'unit' }}
                                    </span>
                                    @error('actual_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Masukkan jumlah barang riil yang ada di rak saat ini.</div>
                            </div>

                            {{-- Tanggal Penyesuaian --}}
                            <div class="mb-3">
                                <label for="transaction_date" class="form-label fw-semibold">Tanggal Pelaksanaan Opname <span class="text-danger">*</span></label>
                                <input type="date" max="{{ date('Y-m-d') }}" 
                                       class="form-control rounded-pill @error('transaction_date') is-invalid @enderror" 
                                       id="transaction_date" name="transaction_date" 
                                       value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Alasan Penyesuaian (Wajib min 5 karakter) --}}
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-semibold">Alasan / Catatan Penyesuaian <span class="text-danger">*</span></label>
                                <textarea class="form-control rounded-4 @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Wajib diisi: Alasan selisih stok (misal: barang rusak saat pemindahan, hasil audit fisik akhir bulan, barang hilang, dll.)..." required>{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Kolom Kanan: Kalkulator Selisih --}}
                        <div class="col-12 col-lg-5">
                            <div class="card border-0 bg-light rounded-4 p-4 h-100">
                                <h6 class="fw-bold text-dark mb-3">Analisis Selisih Inventaris</h6>
                                
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-3 bg-white p-3 shadow-sm text-warning-emphasis">
                                        <i class="bi bi-sliders fs-3"></i>
                                    </div>
                                    <div>
                                        <div class="text-secondary small">Stok Sistem Saat Ini</div>
                                        <div class="fs-4 fw-bold text-dark" id="current-stock-display">
                                            {{ $selectedProduct ? number_format($selectedProduct->stock) : '-' }}
                                            <span class="fs-6 fw-normal text-secondary" id="current-unit-display">{{ $selectedProduct?->unit ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 bg-white rounded-3 shadow-sm mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary small">Kalkulasi Selisih</span>
                                        <span class="badge bg-light text-dark border" id="diff-badge">Belum Dihitung</span>
                                    </div>
                                    <div class="fs-3 fw-bold mt-1" id="diff-display">-</div>
                                    <div class="small text-muted" id="diff-desc">Masukkan stok fisik aktual untuk melihat selisih mutasi koreksi.</div>
                                </div>

                                <div class="alert alert-secondary border-0 rounded-4 mt-auto small mb-0">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    <strong>Otoritas Khusus:</strong> Fitur penyesuaian stok hanya dapat dilakukan oleh Manager dan Admin. Transaksi ini akan tercatat dalam buku besar ledger dengan tipe <code>adjustment</code>.
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
                            <i class="bi bi-check-lg me-1"></i> Simpan Penyesuaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let systemStock = {{ $selectedProduct ? (int) $selectedProduct->stock : 0 }};

function updateProductDetails(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) {
        document.getElementById('current-stock-display').innerText = '-';
        document.getElementById('current-unit-display').innerText = '';
        document.getElementById('unit-badge').innerText = 'unit';
        systemStock = 0;
        calculateDifference();
        return;
    }

    const unit = option.getAttribute('data-unit');
    const stock = parseInt(option.getAttribute('data-stock'), 10);

    systemStock = stock;
    document.getElementById('current-stock-display').innerText = stock;
    document.getElementById('current-unit-display').innerText = unit;
    document.getElementById('unit-badge').innerText = unit;

    calculateDifference();
}

function calculateDifference() {
    const actualInput = document.getElementById('actual_stock');
    const actual = parseInt(actualInput.value, 10);
    const diffDisplay = document.getElementById('diff-display');
    const diffBadge = document.getElementById('diff-badge');
    const diffDesc = document.getElementById('diff-desc');

    if (isNaN(actual)) {
        diffDisplay.innerText = '-';
        diffBadge.innerText = 'Belum Dihitung';
        diffBadge.className = 'badge bg-light text-dark border';
        diffDesc.innerText = 'Masukkan stok fisik aktual untuk melihat selisih mutasi koreksi.';
        return;
    }

    const diff = actual - systemStock;

    if (diff > 0) {
        diffDisplay.innerText = `+${diff}`;
        diffDisplay.className = 'fs-3 fw-bold mt-1 text-success';
        diffBadge.innerText = 'Kelebihan Fisik (Penambahan)';
        diffBadge.className = 'badge bg-success-subtle text-success';
        diffDesc.innerText = `Stok sistem akan dinaikkan sebanyak ${diff} unit.`;
    } else if (diff < 0) {
        diffDisplay.innerText = `${diff}`;
        diffDisplay.className = 'fs-3 fw-bold mt-1 text-danger';
        diffBadge.innerText = 'Kekurangan Fisik (Pengurangan)';
        diffBadge.className = 'badge bg-danger-subtle text-danger';
        diffDesc.innerText = `Stok sistem akan diturunkan sebanyak ${Math.abs(diff)} unit.`;
    } else {
        diffDisplay.innerText = '0';
        diffDisplay.className = 'fs-3 fw-bold mt-1 text-secondary';
        diffBadge.innerText = 'Sesuai (Tanpa Selisih)';
        diffBadge.className = 'badge bg-light text-secondary border';
        diffDesc.innerText = 'Stok fisik persis sama dengan stok sistem.';
    }
}
</script>
@endsection
