@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page_title', 'Pengaturan Profil')

@section('page_actions')
    <x-back-button :route="route('dashboard')" />
@endsection

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profil Saya</li>
        </ol>
    </nav>
    <h1 class="h3 fw-bold mb-1">Pengaturan Profil</h1>
    <p class="text-secondary mb-0">Kelola informasi pribadi, kredensial akun, dan keamanan kata sandi Anda.</p>
</div>

<div class="row g-4">
    {{-- Kolom Kiri: Informasi Pribadi --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 56px; height: 56px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $user->name }}</h5>
                        <span class="text-secondary small">{{ $user->email }}</span>
                        <div class="mt-1">
                            <span class="badge bg-dark rounded-pill">{{ strtoupper($user->role) }}</span>
                            <span class="badge bg-success-subtle text-success rounded-pill ms-1">Aktif</span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold text-dark mb-3">Informasi Akun</h6>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" class="form-control rounded-pill bg-light text-secondary" 
                               id="email" value="{{ $user->email }}" readonly disabled>
                        <div class="form-text small">Alamat email dikunci oleh administrator sistem untuk keperluan autentikasi.</div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Ubah Kata Sandi --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-3 bg-light text-dark p-3 shadow-sm">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Keamanan Kata Sandi</h5>
                        <span class="text-secondary small">Perbarui kata sandi secara berkala</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-pill @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Kata Sandi Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-pill @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        <div class="form-text small">Minimal 8 karakter, kombinasi huruf besar-kecil dan angka.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-pill" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-key-fill me-1"></i> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
