{{-- Layout Halaman Akses (Login & Register) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Toko Amanda — Login</title>

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('admin/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('admin/images/favicon.ico') }}" type="image/x-icon">

    <!-- Gentelella CSS (Bootstrap + Font Awesome + custom) -->
    <link rel="stylesheet" crossorigin href="{{ asset('admin/assets/init-CWCoO_32.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('admin/assets/vendor-forms-BAKshgBM.css') }}">
  </head>

  <body style="background: linear-gradient(135deg, #2a3f54 0%, #1a2a3a 100%); min-height: 100vh;">

    <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 20px;">
      <div style="width: 100%; max-width: 420px;">

        {{-- Logo & Nama Aplikasi --}}
        <div class="text-center mb-4">
          <img src="{{ asset('admin/images/logobaru.png') }}" alt="Toko Amanda" style="height: 72px; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4));">
          <h4 class="mt-3 mb-0 fw-bold" style="color: #fff; letter-spacing: 0.5px;">Toko Amanda</h4>
          <p class="mb-0" style="color: rgba(255,255,255,0.65); font-size: 12.5px;">Sistem Informasi Penjualan &amp; Stock</p>
        </div>

        {{-- Card Halaman Akses --}}
        <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
          <div style="background: linear-gradient(135deg, #2a3f54, #1a2a3a); padding: 18px 24px 14px;">
            <h6 class="mb-0 fw-bold text-white text-center" style="font-size: 15px; letter-spacing: 0.3px;">
              <i class="fas fa-lock me-2" style="color: #f0a500;"></i>Masuk ke Sistem
            </h6>
          </div>
          <div class="card-body p-4" style="background: #fff;">
            {{ $slot }}
          </div>
        </div>

        <p class="text-center mt-3 mb-0" style="color: rgba(255,255,255,0.4); font-size: 11px;">
          &copy; {{ date('Y') }} Toko Amanda &middot; Versi 1.0
        </p>

      </div>
    </div>

    <!-- Gentelella JS (Bootstrap bundle) -->
    <script type="module" crossorigin src="{{ asset('admin/js/rolldown-runtime-DCIKVfrU.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-core-MNZ0Vt-F.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/main-minimal-1s-d-BF6.js') }}"></script>
  </body>
</html>
