@extends('layouts.app')

@section('title', 'Edit Kategori: ' . $category->name)
@section('page_title', 'Edit Kategori')

@section('page_actions')
    <x-back-button :route="route('categories.index')" />
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="gk-card">
                <div class="mb-4">
                    <h2 class="gk-card-title mb-1">Perbarui Data Kategori</h2>
                    <p class="text-muted small mb-0">Perbarui nama atau deskripsi kategori: <strong>{{ $category->name }}</strong></p>
                </div>

                <form method="POST" action="{{ route('categories.update', $category) }}" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Nama Kategori --}}
                    <div class="mb-3">
                        <label for="name" class="form-label-gk">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control form-control-gk @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $category->name) }}"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback d-block text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label for="description" class="form-label-gk">Deskripsi Kategori</label>
                        <textarea class="form-control form-control-gk @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('categories.index') }}" class="btn btn-gk-outline px-4">Batal</a>
                        <button type="submit" class="btn btn-gk-dark px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
