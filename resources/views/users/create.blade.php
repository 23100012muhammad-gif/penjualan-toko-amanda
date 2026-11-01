@extends('layouts.app')
 
@section('title', 'Tambah User | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp
 
@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-plus-circle me-2"></i>Tambah User Baru
    </h3>
    <small class="text-muted">Pengaturan › Kelola User › Tambah</small>
  </div>
</div>
 
<div class="row">
  <div class="col-md-6">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-person-plus me-2"></i>Form Tambah User</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{ route('users.store') }}" method="POST">
          @csrf
 
          <div class="mb-3">
            <label for="name" class="form-label fw-semibold">
              Nama Lengkap <span class="text-danger">*</span>
            </label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}"
                   placeholder="Contoh: Muhammad Aditya"
                   autofocus>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
 
          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">
              Alamat Email <span class="text-danger">*</span>
            </label>
            <input type="email" id="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   placeholder="Contoh: rafli@example.com">
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
 
          <div class="mb-3">
            <label for="role" class="form-label fw-semibold">
              Role Hak Akses <span class="text-danger">*</span>
            </label>
            <select id="role" name="role"
                    class="form-select @error('role') is-invalid @enderror">
              <option value="" disabled selected>-- Pilih Role --</option>
              <option value="pemilik" {{ old('role') === 'pemilik' ? 'selected' : '' }}>Pemilik Toko</option>
              <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
              <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
            </select>
            @error('role')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
 
          <div class="mb-3">
            <label for="password" class="form-label fw-semibold">
              Password <span class="text-danger">*</span>
            </label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Minimal 8 karakter">
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
 
          <div class="mb-3">
            <label for="password_confirmation" class="form-label fw-semibold">
              Konfirmasi Password <span class="text-danger">*</span>
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control"
                   placeholder="Ulangi password">
          </div>
 
          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Simpan
            </button>
            <a href="{{ route('users.index') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
