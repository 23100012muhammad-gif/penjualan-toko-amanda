@extends('layouts.app')
 
@section('title', 'Edit User | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp
 
@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-pencil-square me-2"></i>Edit User
    </h3>
    <small class="text-muted">Pengaturan › Kelola User › Edit › {{ $user->name }}</small>
  </div>
</div>
 
<div class="row">
  <div class="col-md-6">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-person-gear me-2"></i>Form Edit User</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{ route('users.update', $user) }}" method="POST">
          @csrf
          @method('PUT')
 
          <div class="mb-3">
            <label for="name" class="form-label fw-semibold">
              Nama Lengkap <span class="text-danger">*</span>
            </label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}"
                   placeholder="Nama lengkap">
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
                   value="{{ old('email', $user->email) }}"
                   placeholder="Alamat email">
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
 
          <div class="mb-3">
            <label for="role" class="form-label fw-semibold">
              Role Hak Akses <span class="text-danger">*</span>
            </label>
            @if(Auth::id() === $user->id)
              {{-- Prevent the logged-in owner from accidentally changing their own role --}}
              <input type="hidden" name="role" value="{{ $user->role }}">
              <select class="form-select" disabled>
                <option value="pemilik" selected>Pemilik Toko (Akun Anda)</option>
              </select>
              <div class="form-text text-muted">Anda tidak dapat merubah role akun Anda sendiri yang sedang aktif.</div>
            @else
              <select id="role" name="role"
                      class="form-select @error('role') is-invalid @enderror">
                <option value="pemilik" {{ old('role', $user->role) === 'pemilik' ? 'selected' : '' }}>Pemilik Toko</option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="kasir" {{ old('role', $user->role) === 'kasir' ? 'selected' : '' }}>Kasir</option>
              </select>
            @endif
            @error('role')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
 
          <hr class="my-4">
          <div class="mb-2">
            <h5 class="fw-bold" style="font-size: 14px; color: #2a3f54;">Ubah Password (Opsional)</h5>
            <small class="text-muted d-block mb-3">Kosongkan kolom di bawah ini jika tidak ingin mengubah password user.</small>
          </div>
 
          <div class="mb-3">
            <label for="password" class="form-label fw-semibold">
              Password Baru
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
              Konfirmasi Password Baru
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control"
                   placeholder="Ulangi password baru">
          </div>
 
          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Simpan Perubahan
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
