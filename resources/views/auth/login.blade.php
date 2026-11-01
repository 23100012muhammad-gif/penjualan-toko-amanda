<x-auth-layout>

  {{-- Session Status (misal: password reset link sent) --}}
  @if (session('status'))
    <div class="alert alert-success mb-3" role="alert">
      {{ session('status') }}
    </div>
  @endif

  <h5 class="mb-4 fw-bold text-center" style="color: #2a3f54;">Masuk Akun</h5>

  <form method="POST" action="{{ route('login') }}">
    @csrf

    {{-- Email --}}
    <div class="mb-3">
      <label for="email" class="form-label fw-semibold" style="font-size: 13.5px;"><i class="fas fa-envelope me-1 text-muted"></i> Email</label>
      <input id="email" type="email" name="email"
             class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email') }}" required autofocus autocomplete="username">
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Password --}}
    <div class="mb-3">
      <label for="password" class="form-label fw-semibold" style="font-size: 13.5px;"><i class="fas fa-key me-1 text-muted"></i> Password</label>
      <input id="password" type="password" name="password"
             class="form-control @error('password') is-invalid @enderror"
             required autocomplete="current-password">
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Remember Me --}}
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
      <label class="form-check-label text-muted" for="remember_me" style="font-size: 13px;">Ingat saya</label>
    </div>

    {{-- Actions --}}
    <div class="d-grid mb-3">
      <button type="submit" class="btn fw-semibold text-white" style="background: linear-gradient(135deg, #2a3f54, #1a2a3a); border: none; padding: 10px; font-size: 14.5px; border-radius: 8px;">
        <i class="fas fa-sign-in-alt me-1"></i> Masuk
      </button>
    </div>

    <div class="d-flex justify-content-between">
      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="text-muted small" style="font-size: 12.5px; text-decoration: none;">
          <i class="fas fa-question-circle me-1"></i>Lupa password?
        </a>
      @endif
 
      @if (Route::has('register'))
        <a href="{{ route('register') }}" class="text-muted small" style="font-size: 12.5px; text-decoration: none;">
          <i class="fas fa-user-plus me-1"></i>Buat akun
        </a>
      @endif
    </div>

  </form>

</x-auth-layout>
