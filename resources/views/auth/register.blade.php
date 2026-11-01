<x-auth-layout>
 
  <h5 class="mb-4 fw-bold text-center" style="color: #2a3f54;">Daftar Akun Pemilik</h5>
 
  <form method="POST" action="{{ route('register') }}">
    @csrf
 
    {{-- Name --}}
    <div class="mb-3">
      <label for="name" class="form-label fw-semibold" style="font-size: 13.5px;"><i class="fas fa-user me-1 text-muted"></i> Nama Lengkap</label>
      <input id="name" type="text" name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}" required autofocus autocomplete="name">
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
 
    {{-- Email --}}
    <div class="mb-3">
      <label for="email" class="form-label fw-semibold" style="font-size: 13.5px;"><i class="fas fa-envelope me-1 text-muted"></i> Email</label>
      <input id="email" type="email" name="email"
             class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email') }}" required autocomplete="username">
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
 
    {{-- Password --}}
    <div class="mb-3">
      <label for="password" class="form-label fw-semibold" style="font-size: 13.5px;"><i class="fas fa-key me-1 text-muted"></i> Password</label>
      <input id="password" type="password" name="password"
             class="form-control @error('password') is-invalid @enderror"
             required autocomplete="new-password">
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
 
    {{-- Confirm Password --}}
    <div class="mb-3">
      <label for="password_confirmation" class="form-label fw-semibold" style="font-size: 13.5px;"><i class="fas fa-lock me-1 text-muted"></i> Konfirmasi Password</label>
      <input id="password_confirmation" type="password" name="password_confirmation"
             class="form-control @error('password_confirmation') is-invalid @enderror"
             required autocomplete="new-password">
      @error('password_confirmation')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
 
    {{-- Submit --}}
    <div class="d-grid mb-3">
      <button type="submit" class="btn fw-semibold text-white" style="background: linear-gradient(135deg, #2a3f54, #1a2a3a); border: none; padding: 10px; font-size: 14.5px; border-radius: 8px;">
        <i class="fas fa-user-plus me-1"></i> Daftar
      </button>
    </div>
 
    <div class="text-center">
      <a href="{{ route('login') }}" class="text-muted small" style="font-size: 12.5px; text-decoration: none;">
        Sudah punya akun? Masuk
      </a>
    </div>
 
  </form>
 
</x-auth-layout>
