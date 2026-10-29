@extends('layouts.app')

@section('title', 'Edit Pengguna: ' . $user->name)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-secondary">Pengguna</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit: {{ $user->name }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Pengguna</h1>
            <p class="text-secondary mb-0">Perbarui profil, peran otorisasi, atau status akses operasional pengguna.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        @if($user->id === Auth::id())
            <div class="alert alert-info border-0 rounded-4 mb-4 small d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>
                    <strong>Pemberitahuan Akun Sendiri (Aturan BR9):</strong> Anda sedang menyunting akun Anda sendiri. Demi keamanan, Anda tidak dapat mengubah peran (role) atau menonaktifkan status akun Anda sendiri dari halaman ini.
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold mb-3 text-dark">Informasi Akun</h5>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-pill @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">Peran Akun (Role) <span class="text-danger">*</span></label>
                        @if($user->id === Auth::id())
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <input type="text" class="form-control rounded-pill bg-light" value="{{ strtoupper($user->role) }} (Terkunci)" readonly disabled>
                            <div class="form-text small text-muted">Peran akun sendiri tidak dapat diubah (Aturan BR9).</div>
                        @else
                            <select class="form-select rounded-pill @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff Gudang (Pencatatan Masuk & Keluar)</option>
                                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager (Master Data, Penyesuaian, Laporan)</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh & Kelola Pengguna)</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <h5 class="fw-bold mb-3 text-dark">Ubah Kata Sandi (Opsional)</h5>
                    <p class="text-secondary small mb-3">Kosongkan kedua bidang kata sandi di bawah jika Anda tidak ingin memperbarui kata sandi akun ini.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label fw-semibold">Kata Sandi Baru</label>
                            <input type="password" class="form-control rounded-pill @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" class="form-control rounded-pill" 
                                   id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>

                    <div class="mb-4">
                        @if($user->id === Auth::id())
                            <input type="hidden" name="is_active" value="1">
                            <div class="text-success small fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Akun Anda aktif
                            </div>
                        @else
                            <div class="form-check form-switch pt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold ms-2" for="is_active">
                                    Status Akun Aktif
                                </label>
                                <div class="form-text small ms-4">Akun nonaktif tidak dapat melakukan login.</div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark gk-btn-pill px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
