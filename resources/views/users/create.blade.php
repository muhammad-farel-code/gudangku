@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_title', 'Tambah Pengguna')

@section('page_actions')
    <x-back-button :route="route('users.index')" />
@endsection

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-secondary">Pengguna</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah Baru</li>
        </ol>
    </nav>
    <h1 class="h3 fw-bold mb-1">Tambah Pengguna Baru</h1>
    <p class="text-secondary mb-0">Daftarkan akun staf, manajer, atau administrator baru untuk mengelola sistem inventaris.</p>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <h5 class="fw-bold mb-3 text-dark">Informasi Akun</h5>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Nama staf atau manajer..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-pill @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="contoh@gudangku.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">Peran Akun (Role) <span class="text-danger">*</span></label>
                        <select class="form-select rounded-pill @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">-- Pilih Peran Akses --</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff Gudang (Pencatatan Masuk & Keluar)</option>
                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager (Master Data, Penyesuaian, Laporan)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh & Kelola Pengguna)</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <h5 class="fw-bold mb-3 text-dark">Kredensial Keamanan</h5>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control rounded-pill @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <div class="form-text small">Min. 8 karakter, kombinasi huruf besar-kecil dan angka.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control rounded-pill" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch pt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold ms-2" for="is_active">
                                Aktifkan Akun Pengguna
                            </label>
                            <div class="form-text small ms-4">Akun nonaktif tidak dapat masuk ke sistem GudangKu.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-save me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
