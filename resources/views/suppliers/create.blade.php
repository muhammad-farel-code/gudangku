@extends('layouts.app')

@section('title', 'Tambah Pemasok')
@section('page_title', 'Tambah Pemasok')

@section('page_actions')
    <a href="{{ route('suppliers.index') }}" class="btn btn-gk-outline">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="gk-card">
                <div class="mb-4">
                    <h2 class="gk-card-title mb-1">Formulir Pemasok Baru</h2>
                    <p class="text-muted small mb-0">Daftarkan rekanan distributor atau pabrik pemasok barang gudang</p>
                </div>

                <form method="POST" action="{{ route('suppliers.store') }}" novalidate>
                    @csrf

                    {{-- Nama Pemasok --}}
                    <div class="mb-3">
                        <label for="name" class="form-label-gk">Nama Perusahaan / Pemasok <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control form-control-gk @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Contoh: PT Surya Abadi Nusantara"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback d-block text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Kontak Person --}}
                        <div class="col-md-6">
                            <label for="contact_person" class="form-label-gk">Nama Kontak Person (PIC)</label>
                            <input type="text"
                                   class="form-control form-control-gk @error('contact_person') is-invalid @enderror"
                                   id="contact_person"
                                   name="contact_person"
                                   value="{{ old('contact_person') }}"
                                   placeholder="Contoh: Hendra Wijaya">
                            @error('contact_person')
                                <div class="invalid-feedback d-block text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Nomor Telepon --}}
                        <div class="col-md-6">
                            <label for="phone" class="form-label-gk">Nomor Telepon / WhatsApp</label>
                            <input type="text"
                                   class="form-control form-control-gk @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="Contoh: 081234567890">
                            @error('phone')
                                <div class="invalid-feedback d-block text-danger small mt-1">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label-gk">Alamat Email Pemasok</label>
                        <input type="email"
                               class="form-control form-control-gk @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="Contoh: sales@suryaabadi.co.id">
                        @error('email')
                            <div class="invalid-feedback d-block text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="mb-4">
                        <label for="address" class="form-label-gk">Alamat Kantor / Gudang Pemasok</label>
                        <textarea class="form-control form-control-gk @error('address') is-invalid @enderror"
                                  id="address"
                                  name="address"
                                  rows="3"
                                  placeholder="Alamat lengkap, kota, atau kawasan industri...">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback d-block text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-gk-outline px-4">Batal</a>
                        <button type="submit" class="btn btn-gk-dark px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Pemasok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
