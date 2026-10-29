@extends('layouts.app')

@section('title', 'Edit Produk: ' . $product->name)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-secondary">Produk</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit: {{ $product->name }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Produk</h1>
            <p class="text-secondary mb-0">Perbarui spesifikasi, kategori, harga, atau status aktif produk.</p>
        </div>
        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light rounded-pill">
            <i class="bi bi-eye me-1"></i> Lihat Detail
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        {{-- Card Informasi Stok (Aturan BR2: Stok tidak dapat diedit manual) --}}
        <div class="card border-0 bg-light rounded-4 mb-4">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white p-3 shadow-sm text-primary">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <div class="small text-secondary text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Stok Fisik Saat Ini</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-3 fw-bold text-dark">{{ number_format($product->stock) }}</span>
                            <span class="text-secondary">{{ $product->unit }}</span>
                            @if($product->stock_status === 'Habis')
                                <span class="badge bg-danger-subtle text-danger rounded-pill">Habis</span>
                            @elseif($product->stock_status === 'Menipis')
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Menipis (Min: {{ $product->min_stock }})</span>
                            @else
                                <span class="badge bg-success-subtle text-success rounded-pill">Aman</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-secondary small max-w-sm">
                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                    <strong>Aturan BR2:</strong> Stok tidak dapat diedit secara langsung di halaman ini. Perubahan stok hanya dapat dilakukan melalui menu <em>Stok Masuk</em>, <em>Stok Keluar</em>, atau <em>Penyesuaian Stok</em>.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold mb-3 text-dark">Informasi Utama Produk</h5>
                    <div class="row g-3 mb-4">
                        {{-- SKU --}}
                        <div class="col-12 col-md-4">
                            <label for="sku" class="form-label fw-semibold">Kode SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-pill @error('sku') is-invalid @enderror" 
                                   id="sku" name="sku" value="{{ old('sku', $product->sku) }}" 
                                   required style="text-transform: uppercase;">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nama Produk --}}
                        <div class="col-12 col-md-8">
                            <label for="name" class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-pill @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select rounded-pill @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Pemasok --}}
                        <div class="col-12 col-md-6">
                            <label for="supplier_id" class="form-label fw-semibold">Pemasok (Supplier)</label>
                            <select class="form-select rounded-pill @error('supplier_id') is-invalid @enderror" 
                                    id="supplier_id" name="supplier_id">
                                <option value="">-- Tanpa Pemasok Tetap --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <h5 class="fw-bold mb-3 text-dark">Harga & Batas Stok</h5>
                    <div class="row g-3 mb-4">
                        {{-- Satuan / Unit --}}
                        <div class="col-12 col-md-3">
                            <label for="unit" class="form-label fw-semibold">Satuan Unit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-pill @error('unit') is-invalid @enderror" 
                                   id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Harga Beli --}}
                        <div class="col-12 col-md-3">
                            <label for="purchase_price" class="form-label fw-semibold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light border-end-0">Rp</span>
                                <input type="number" step="0.01" min="0" class="form-control rounded-end-pill @error('purchase_price') is-invalid @enderror" 
                                       id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Harga Jual --}}
                        <div class="col-12 col-md-3">
                            <label for="selling_price" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light border-end-0">Rp</span>
                                <input type="number" step="0.01" min="0" class="form-control rounded-end-pill @error('selling_price') is-invalid @enderror" 
                                       id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required>
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Harus &ge; harga beli (BR10).</div>
                        </div>

                        {{-- Minimum Stok --}}
                        <div class="col-12 col-md-3">
                            <label for="min_stock" class="form-label fw-semibold">Batas Min. Stok <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control rounded-pill @error('min_stock') is-invalid @enderror" 
                                   id="min_stock" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" required>
                            @error('min_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Lokasi Rak --}}
                        <div class="col-12 col-md-6">
                            <label for="rack_location" class="form-label fw-semibold">Lokasi Rak Gudang</label>
                            <input type="text" class="form-control rounded-pill @error('rack_location') is-invalid @enderror" 
                                   id="rack_location" name="rack_location" value="{{ old('rack_location', $product->rack_location) }}" 
                                   style="text-transform: uppercase;">
                            @error('rack_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <h5 class="fw-bold mb-3 text-dark">Detail Tambahan</h5>
                    <div class="row g-3 mb-4">
                        {{-- Deskripsi --}}
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Deskripsi Produk</label>
                            <textarea class="form-control rounded-4 @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-12">
                            <div class="form-check form-switch pt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold ms-2" for="is_active">
                                    Aktifkan Produk untuk Transaksi
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-check-lg me-1"></i> Perbarui Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
