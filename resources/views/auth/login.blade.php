@extends('layouts.guest')

@section('title', 'Masuk ke Sistem')

@section('content')
    <div class="mb-4 text-center">
        <h2 class="h5 fw-bold text-dark mb-1">Masuk ke Sistem</h2>
        <p class="text-muted small">Kelola inventaris dan stok gudang dengan efisien</p>
    </div>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Alamat Email --}}
        <div class="mb-3">
            <label for="email" class="form-label-gk">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: var(--gk-radius-sm) 0 0 var(--gk-radius-sm); border-color: var(--gk-border);"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       class="form-control form-control-gk border-start-0 @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="nama@gudangku.test"
                       style="border-radius: 0 var(--gk-radius-sm) var(--gk-radius-sm) 0;"
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
        <div class="mb-4">
            <label for="password" class="form-label-gk">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: var(--gk-radius-sm) 0 0 var(--gk-radius-sm); border-color: var(--gk-border);"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control form-control-gk border-start-0 @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="••••••••"
                       style="border-radius: 0 var(--gk-radius-sm) var(--gk-radius-sm) 0;"
                       required>
            </div>
            @error('password')
                <div class="invalid-feedback d-block text-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-gk-dark w-100 justify-content-center py-2 mb-3 shadow-sm">
            <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
        </button>

        <div class="text-center pt-2 border-top">
            <p class="small text-muted mb-0">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-dark">Daftar Akun Baru</a>
            </p>
        </div>
    </form>

    {{-- Bantuan Akun Demo Cepat --}}
    <div class="mt-4 p-3 rounded-4" style="background-color: var(--gk-surface); border: 1px solid var(--gk-border);">
        <p class="small fw-semibold text-secondary mb-2 text-center">
            <i class="bi bi-key-fill text-muted me-1"></i> Akun Demo Cepat (Klik untuk mengisi):
        </p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-gk-outline" onclick="fillDemo('admin@gudangku.test', 'Admin@12345')">
                <i class="bi bi-shield-lock-fill text-dark me-1"></i> Admin
            </button>
            <button type="button" class="btn btn-sm btn-gk-outline" onclick="fillDemo('manager@gudangku.test', 'Manager@12345')">
                <i class="bi bi-briefcase-fill text-dark me-1"></i> Manager
            </button>
            <button type="button" class="btn btn-sm btn-gk-outline" onclick="fillDemo('staff@gudangku.test', 'Staff@12345')">
                <i class="bi bi-person-fill text-dark me-1"></i> Staff
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
