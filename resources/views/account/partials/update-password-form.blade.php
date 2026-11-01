@if (session('status') === 'password-updated')
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-1"></i> Password berhasil diperbarui.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
  @csrf
  @method('PUT')

  {{-- Password Saat Ini --}}
  <div class="mb-3">
    <label for="current_password" class="form-label fw-semibold">Password Saat Ini</label>
    <input id="current_password" type="password" name="current_password"
           class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
           autocomplete="current-password">
    @error('current_password', 'updatePassword')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- Password Baru --}}
  <div class="mb-3">
    <label for="password" class="form-label fw-semibold">Password Baru</label>
    <input id="password" type="password" name="password"
           class="form-control @error('password', 'updatePassword') is-invalid @enderror"
           autocomplete="new-password">
    @error('password', 'updatePassword')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  {{-- Konfirmasi Password --}}
  <div class="mb-3">
    <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
    <input id="password_confirmation" type="password" name="password_confirmation"
           class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
           autocomplete="new-password">
    @error('password_confirmation', 'updatePassword')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <button type="submit" class="btn-ta btn-ta-blue">
    <i class="fas fa-key me-1"></i> Perbarui Password
  </button>
</form>
