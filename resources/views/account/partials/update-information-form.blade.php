@if (session('status') === 'account-updated')
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-1"></i> Informasi akun berhasil diperbarui.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<form method="POST" action="{{ route('account.update') }}">
  @csrf
  @method('PATCH')

  {{-- Nama --}}
  <div class="mb-3">
    <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
    <input id="name" type="text" name="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- Email --}}
  <div class="mb-3">
    <label for="email" class="form-label fw-semibold">Alamat Email</label>
    <input id="email" type="email" name="email"
           class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $user->email) }}" required autocomplete="username">
    @error('email')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <button type="submit" class="btn-ta btn-ta-blue">
    <i class="fas fa-save me-1"></i> Simpan Perubahan
  </button>
</form>
