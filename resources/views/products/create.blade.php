@extends('layouts.app')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-secondary">Produk</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah Baru</li>
        </ol>
    </nav>
    <h1 class="h3 fw-bold mb-1">Tambah Produk Baru</h1>
    <p class="text-secondary mb-0">Lengkapi formulir di bawah ini untuk menambahkan barang baru ke katalog inventaris gudang.</p>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <h5 class="fw-bold mb-3 text-dark">Informasi Utama Produk</h5>
                    <div class="row g-3 mb-4">
                        {{-- SKU --}}
                        <div class="col-12 col-md-4">
                            <label for="sku" class="form-label fw-semibold">Kode SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-pill @error('sku') is-invalid @enderror" 
                                   id="sku" name="sku" value="{{ old('sku') }}" 
                                   placeholder="Contoh: ELK-001" required style="text-transform: uppercase;">
                            <div class="form-text small">Kode unik pengenal produk (maks 50 karakter).</div>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nama Produk --}}
                        <div class="col-12 col-md-8">
                            <label for="name" class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-pill @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="Contoh: Mouse Wireless Logitech M330" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select rounded-pill @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
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

                    <h5 class="fw-bold mb-3 text-dark">Harga & Stok</h5>
                    <div class="row g-3 mb-4">
                        {{-- Satuan / Unit --}}
                        <div class="col-12 col-md-4">
                            <label for="unit" class="form-label fw-semibold">Satuan Unit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-pill @error('unit') is-invalid @enderror" 
                                   id="unit" name="unit" value="{{ old('unit', 'pcs') }}" 
                                   placeholder="Contoh: pcs, box, roll, kg, unit" required>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Harga Beli --}}
                        <div class="col-12 col-md-4">
                            <label for="purchase_price" class="form-label fw-semibold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light border-end-0">Rp</span>
                                <input type="number" step="0.01" min="0" class="form-control rounded-end-pill @error('purchase_price') is-invalid @enderror" 
                                       id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" 
                                       placeholder="0" required>
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Harga Jual --}}
                        <div class="col-12 col-md-4">
                            <label for="selling_price" class="form-label fw-semibold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light border-end-0">Rp</span>
                                <input type="number" step="0.01" min="0" class="form-control rounded-end-pill @error('selling_price') is-invalid @enderror" 
                                       id="selling_price" name="selling_price" value="{{ old('selling_price') }}" 
                                       placeholder="0" required>
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Harus &ge; harga beli (Aturan BR10).</div>
                        </div>

                        {{-- Stok Awal --}}
                        <div class="col-12 col-md-4">
                            <label for="initial_stock" class="form-label fw-semibold">Stok Awal</label>
                            <input type="number" min="0" class="form-control rounded-pill @error('initial_stock') is-invalid @enderror" 
                                   id="initial_stock" name="initial_stock" value="{{ old('initial_stock', 0) }}">
                            <div class="form-text small">Hanya diinput sekali saat pembuatan produk (Fase 5 akan tercatat sebagai transaksi awal).</div>
                            @error('initial_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Minimum Stok --}}
                        <div class="col-12 col-md-4">
                            <label for="min_stock" class="form-label fw-semibold">Batas Minimum Stok <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control rounded-pill @error('min_stock') is-invalid @enderror" 
                                   id="min_stock" name="min_stock" value="{{ old('min_stock', 5) }}" required>
                            <div class="form-text small">Peringatan stok menipis jika stok &le; angka ini.</div>
                            @error('min_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Lokasi Rak --}}
                        <div class="col-12 col-md-4">
                            <label for="rack_location" class="form-label fw-semibold">Lokasi Rak Gudang</label>
                            <input type="text" class="form-control rounded-pill @error('rack_location') is-invalid @enderror" 
                                   id="rack_location" name="rack_location" value="{{ old('rack_location') }}" 
                                   placeholder="Contoh: RAK-A1, BLOK-3" style="text-transform: uppercase;">
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
                                      id="description" name="description" rows="3" 
                                      placeholder="Spesifikasi teknis atau catatan tambahan terkait produk...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status Aktif --}}
                        <div class="col-12">
                            <div class="form-check form-switch pt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold ms-2" for="is_active">
                                    Aktifkan Produk untuk Transaksi
                                </label>
                                <div class="form-text small ms-4">Produk nonaktif tidak akan muncul di daftar pilihan transaksi stok.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-save me-1"></i> Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
