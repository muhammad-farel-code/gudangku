@extends('layouts.guest')

@section('title', 'Masuk ke Sistem')

@section('content')
    <div class="mb-4 text-center">
        <h2 class="h5 fw-bold text-dark mb-1">Selamat Datang</h2>
        <p class="text-muted small">Silakan masuk menggunakan kredensial akun Anda</p>
    </div>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Alamat Email --}}
        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       class="form-control border-start-0 @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="nama@gudangku.test"
                       required
                       autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback d-block text-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        {{-- Kata Sandi --}}
        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control border-start-0 @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="••••••••"
                       required>
            </div>
            @error('password')
                <div class="invalid-feedback d-block text-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 mb-3 shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
        </button>

        <div class="text-center pt-2 border-top">
            <p class="small text-muted mb-0">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-primary">Daftar Akun Baru</a>
            </p>
        </div>
    </form>

    {{-- Bantuan Akun Demo untuk Pengujian & Demo Video --}}
    <div class="mt-4 p-3 bg-light rounded-3 border">
        <p class="small fw-bold text-secondary mb-2 text-center"><i class="bi bi-info-circle me-1"></i> Akun Demo Cepat (Klik untuk isi):</p>
        <div class="d-flex justify-content-center gap-1 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-dark badge-role" onclick="fillDemo('admin@gudangku.test', 'Admin@12345')">
                <i class="bi bi-shield-lock me-1"></i>Admin
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary badge-role" onclick="fillDemo('manager@gudangku.test', 'Manager@12345')">
                <i class="bi bi-briefcase me-1"></i>Manager
            </button>
            <button type="button" class="btn btn-sm btn-outline-success badge-role" onclick="fillDemo('staff@gudangku.test', 'Staff@12345')">
                <i class="bi bi-person me-1"></i>Staff
            </button>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function fillDemo(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
