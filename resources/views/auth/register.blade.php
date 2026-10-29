@extends('layouts.guest')

@section('title', 'Pendaftaran Akun Baru')

@section('content')
    <div class="mb-4 text-center">
        <h2 class="h5 fw-bold text-dark mb-1">Daftar Akun Baru</h2>
        <p class="text-muted small">Registrasi akun staf operasional inventaris</p>
    </div>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        {{-- Nama Lengkap --}}
        <div class="mb-3">
            <label for="name" class="form-label-gk">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: var(--gk-radius-sm) 0 0 var(--gk-radius-sm); border-color: var(--gk-border);"><i class="bi bi-person"></i></span>
                <input type="text"
                       class="form-control form-control-gk border-start-0 @error('name') is-invalid @enderror"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Muhammad Farel"
                       style="border-radius: 0 var(--gk-radius-sm) var(--gk-radius-sm) 0;"
                       required
                       autofocus>
            </div>
            @error('name')
                <div class="invalid-feedback d-block text-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

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
                       required>
            </div>
            @error('email')
                <div class="invalid-feedback d-block text-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        {{-- Kata Sandi --}}
        <div class="mb-3">
            <label for="password" class="form-label-gk">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: var(--gk-radius-sm) 0 0 var(--gk-radius-sm); border-color: var(--gk-border);"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control form-control-gk border-start-0 @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="Min. 8 karakter (huruf besar, kecil, angka)"
                       style="border-radius: 0 var(--gk-radius-sm) var(--gk-radius-sm) 0;"
                       required>
            </div>
            @error('password')
                <div class="invalid-feedback d-block text-danger small mt-1">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        {{-- Konfirmasi Kata Sandi --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label-gk">Konfirmasi Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: var(--gk-radius-sm) 0 0 var(--gk-radius-sm); border-color: var(--gk-border);"><i class="bi bi-lock-fill"></i></span>
                <input type="password"
                       class="form-control form-control-gk border-start-0"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Ulangi kata sandi"
                       style="border-radius: 0 var(--gk-radius-sm) var(--gk-radius-sm) 0;"
                       required>
            </div>
        </div>

        <div class="p-2 px-3 rounded-3 small text-muted mb-4" style="background-color: var(--gk-surface); border: 1px solid var(--gk-border);">
            <i class="bi bi-info-circle me-1 text-primary"></i> Pendaftaran baru otomatis mendapatkan peran <strong>Staff</strong>.
        </div>

        <button type="submit" class="btn btn-gk-dark w-100 justify-content-center py-2 mb-3 shadow-sm">
            <i class="bi bi-person-plus"></i> Daftar Akun
        </button>

        <div class="text-center pt-2 border-top">
            <p class="small text-muted mb-0">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-dark">Masuk di Sini</a>
            </p>
        </div>
    </form>
@endsection
