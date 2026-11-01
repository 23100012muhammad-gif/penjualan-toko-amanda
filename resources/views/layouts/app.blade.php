<!DOCTYPE html>
<html lang="en">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Security headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src 'self' data: https: blob:; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; connect-src 'self' ws: wss: http://localhost:* https://api.example.com https://*.googleapis.com; frame-src 'self' https://www.youtube.com https://player.vimeo.com; media-src 'self' https: blob:; object-src 'none'; base-uri 'self'; form-action 'self'; upgrade-insecure-requests;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="camera=(), microphone=(), geolocation=()">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/images/logobaru.png') }}">
    <link rel="shortcut icon" href="{{ asset('admin/images/logobaru.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('admin/images/logobaru.png') }}">
    <link rel="manifest" href="{{ asset('admin/site.webmanifest') }}">

    <!-- Theme color -->
    <meta name="theme-color" content="#2a3f54">
    <meta name="msapplication-TileColor" content="#2a3f54">

    <title>@yield('title', 'Dashboard | Gentelella')</title>

    <!-- Vite Build Assets: JS -->
    <script type="module" crossorigin src="{{ asset('admin/js/dom-CQgPirlD.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/logger-DvTFZCmm.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/init-CLaZovBE.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/rolldown-runtime-DCIKVfrU.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-forms-DAl8uJWB.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-echarts-BR0YpTnG.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/cropper.esm.raw-CwbD74UF.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-chartjs-BMnHYBQu.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-core-MNZ0Vt-F.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-tables-Dugqs6iD.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-tables-ext-CS-z1T4z.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-utils-CJuYIZLr.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/vendor-maps-Bm1ilmn3.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/echarts-DdREsc1J.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/maps-dPK1P_-u.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/weather-BDY7Y-qR.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/purify.es-BXwCToHK.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/security-1ZVWYXPy.js') }}"></script>
    <script type="module" crossorigin src="{{ asset('admin/js/main-minimal-1s-d-BF6.js') }}"></script>

    <!-- Vite Build Assets: CSS -->
    <link rel="stylesheet" crossorigin href="{{ asset('admin/assets/init-CWCoO_32.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('admin/assets/vendor-forms-BAKshgBM.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('admin/assets/cropper.esm-Bkap2va_.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('admin/assets/vendor-maps-Ds4Udj8m.css') }}">

    <!-- TomSelect — searchable dropdown tanpa jQuery -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <style>
    /* ── Sidebar title icon fix ─────────────────────────────────── */
    .navbar.nav_title .site_title i,
    .navbar.nav_title .site_title .fas {
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      text-shadow: none !important;
      -webkit-text-stroke: 0 !important;
    }
    /* ── Sidebar title: geser sedikit ke kiri ─────────────────── */
    .navbar.nav_title {
      padding: 14px 8px 12px !important;
    }
    /* ── Sidebar collapsed: icon tetap pas, teks disembunyikan ──── */
    .nav-sm .navbar.nav_title {
      padding: 10px 0 !important;
      display: flex !important;
      justify-content: center !important;
    }
    .nav-sm .navbar.nav_title .site_title {
      justify-content: center !important;
      gap: 0 !important;
    }
    .nav-sm .navbar.nav_title .site_title > span:last-child {
      display: none !important;
    }
    .nav-sm .navbar.nav_title .site_title > span:first-child {
      width: 36px !important;
      height: 36px !important;
      flex-shrink: 0 !important;
    }
    /* ── Sidebar collapsed: child_menu flyout — teks center dengan icon ── */
    .nav-sm .child_menu li a {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
      padding: 8px 14px !important;
    }
    .nav-sm .child_menu li a i {
      width: 16px !important;
      text-align: center !important;
      flex-shrink: 0 !important;
    }
    /* ── Sidebar collapsed: flyout tidak terpotong di bawah layar ── */
    .nav-sm ul.nav.child_menu {
      position: fixed !important;
      left: 70px !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
      /* top & max-height diatur dinamis oleh script reposition() */
    }
    /* Scrollbar submenu collapsed — lebih kontras & mudah dikenali */
    .nav-sm ul.nav.child_menu::-webkit-scrollbar { width: 6px; }
    .nav-sm ul.nav.child_menu::-webkit-scrollbar-track { background: rgba(0,0,0,.2); border-radius: 3px; }
    .nav-sm ul.nav.child_menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,.45); border-radius: 3px; }
    .nav-sm ul.nav.child_menu::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.75); }
    /* Firefox fallback */
    .nav-sm ul.nav.child_menu { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.45) rgba(0,0,0,.2); }

    /* ── Custom Button Global (btn-ta) ─────────────────────────────── */
    .btn-ta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 5px 12px;
      font-size: 12px;
      font-weight: 500;
      border-radius: 4px;
      border: 1.5px solid transparent;
      cursor: pointer;
      text-decoration: none;
      white-space: nowrap;
      line-height: 1.5;
      transition: all 0.2s ease-in-out;
    }
    .btn-ta:hover  { transform: translateY(-1px); }
    .btn-ta:active { transform: translateY(0); }
    .btn-ta:focus  { outline: none; }
    .btn-ta i      { font-size: 11px; }
    /* Biru — Simpan / Tambah */
    .btn-ta-blue   { background:#1a73e8; color:#fff; border-color:#1a73e8; }
    .btn-ta-blue:hover  { background:#fff; border-color:#1a73e8; color:#1a73e8; box-shadow:0 3px 8px rgba(26,115,232,.25); }
    /* Hijau — Import */
    .btn-ta-green  { background:#1e8c45; color:#fff; border-color:#1e8c45; }
    .btn-ta-green:hover { background:#fff; border-color:#1e8c45; color:#1e8c45; box-shadow:0 3px 8px rgba(30,140,69,.25); }
    /* Ungu — Export */
    .btn-ta-purple { background:#5a4fcf; color:#fff; border-color:#5a4fcf; }
    .btn-ta-purple:hover { background:#fff; border-color:#5a4fcf; color:#5a4fcf; box-shadow:0 3px 8px rgba(90,79,207,.25); }
    /* Abu — Template / Reset / Kembali */
    .btn-ta-gray   { background:#5f6368; color:#fff; border-color:#5f6368; }
    .btn-ta-gray:hover  { background:#fff; border-color:#5f6368; color:#5f6368; box-shadow:0 3px 8px rgba(95,99,104,.25); }
    /* Navy — Filter */
    .btn-ta-dark   { background:#2a3f54; color:#fff; border-color:#2a3f54; }
    .btn-ta-dark:hover  { background:#fff; border-color:#2a3f54; color:#2a3f54; box-shadow:0 3px 8px rgba(42,63,84,.25); }
    /* Kuning — Edit */
    .btn-ta-edit   { background:#f0a500; color:#fff; border-color:#f0a500; }
    .btn-ta-edit:hover  { background:#fff; border-color:#f0a500; color:#f0a500; box-shadow:0 3px 8px rgba(240,165,0,.3); }
    /* Merah — Hapus */
    .btn-ta-delete { background:#d93025; color:#fff; border-color:#d93025; }
    .btn-ta-delete:hover { background:#fff; border-color:#d93025; color:#d93025; box-shadow:0 3px 8px rgba(217,48,37,.3); }

    /* ── Transaksi (tx-*) ──────────────────────────────────────────── */
    /* tx-bar: visual wrapper (background + border) */
    .tx-bar         { background:#f8f9fa; border:1px solid #e9ecef; padding:8px; border-radius:6px; }
    /* tx-bar-row: layout row — nowrap agar semua item satu baris */
    .tx-bar-row     { display:flex; align-items:flex-end; gap:8px; flex-wrap:nowrap; }
    @media (max-width:600px) { .tx-bar-row { flex-wrap:wrap; } }
    /* Kontrol tinggi seragam di dalam bar */
    .tx-bar .form-label              { font-size:12px; color:#6c757d; margin-bottom:4px; }
    .tx-bar .form-select,
    .tx-bar .form-control,
    .tx-bar .btn-outline-secondary   { height:38px; }
    .tx-bar .btn-ta                  { height:38px; padding:0 14px; }
    .tx-bar .form-control.qty-input  { width:52px; text-align:center; }
    .tx-bar .tx-satuan               { font-size:12px; color:#6c757d; white-space:nowrap; min-width:0; }
    /* Info harga & stok */
    .tx-info        { display:inline-block; margin-top:6px; padding:4px 10px;
                      background:#f1f5f9; border:1px solid #e2e8f0;
                      border-radius:6px; font-size:12px; color:#475569; }
    /* Empty state tabel */
    .tx-empty-icon  { font-size:1.4rem; color:#ced4da; }
    .tx-empty-text  { font-size:12px; }
    /* Teks baris tabel (dipakai di JS renderKeranjang) */
    .tx-cell-name   { font-size:13px; }
    .tx-cell-num    { font-size:13px; }
    /* Tombol hapus compact */
    .btn-ta-delete.btn-ta-xs { padding:2px 7px; }
    .btn-ta-delete.btn-ta-xs i { font-size:11px; }
    /* Box total */
    .tx-total-box   { max-width:280px; margin:12px auto 8px; padding:12px;
                      text-align:center; border-radius:8px;
                      background:#f8fafc; border:1px solid #e5e7eb; }
    .tx-total-label { font-size:11px; text-transform:uppercase;
                      letter-spacing:1px; color:#9ca3af; margin-bottom:2px; }
    .tx-total-value { font-size:24px; font-weight:700; color:#16a34a; line-height:1.2; }
    /* Area tombol aksi */
    .tx-action      { display:flex; justify-content:center; gap:10px; margin-top:8px; flex-wrap:wrap; }
    /* Table compact */
    .tx-table       { font-size:13px; }
    .tx-table th,
    .tx-table td    { padding:5px 6px; vertical-align:middle; }
    .tx-table thead { background:#2a3f54; color:#fff; font-size:11.5px; }
    .tx-table thead th { white-space:nowrap; background:#2a3f54 !important; color:#fff !important; }
    /* Stripe manual — tidak pakai table-striped agar group row aman */
    .tx-table tbody tr:not(.tx-group-row):nth-child(even) td { background:#f8f9fa; }
    .tx-table tbody tr:not(.tx-group-row):hover td            { background:#eef3fb; }
    /* Page heading */
    .tx-heading     { color:#2a3f54; }
    /* Kolom teks terpotong */
    .tx-cell-truncate { max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

    /* ── Filter Toolbar (tx-filter-*) ──────────────────────────────── */
    .tx-filter-toolbar                        { display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
    .tx-filter-search                         { flex:1; min-width:150px; max-width:220px; }
    /* Samakan tinggi SEMUA elemen di dalam toolbar — override Gentelella */
    .tx-filter-toolbar .form-control,
    .tx-filter-toolbar .form-select,
    .tx-filter-toolbar .input-group-text,
    .tx-filter-toolbar .btn-ta               { height:28px !important;
                                               min-height:28px !important;
                                               max-height:28px !important;
                                               font-size:11.5px !important;
                                               padding-top:0 !important;
                                               padding-bottom:0 !important;
                                               line-height:26px !important;
                                               box-sizing:border-box !important; }
    .tx-filter-toolbar .form-control         { padding-left:8px !important; padding-right:8px !important; }
    .tx-filter-toolbar .form-select          { padding-left:8px !important; padding-right:20px !important; }
    .tx-filter-toolbar .input-group-text     { padding-left:8px !important; padding-right:8px !important;
                                               display:flex !important; align-items:center !important; }
    /* input-group wrapper juga harus 28px agar tidak stretch */
    .tx-filter-toolbar .input-group          { height:28px !important; flex-wrap:nowrap; }
    .tx-filter-btn                           { height:28px !important; padding:0 10px !important;
                                               font-size:11.5px !important; line-height:26px !important; }
    .tx-filter-reset                         { height:28px !important; padding:0 8px !important;
                                               font-size:11px !important; line-height:26px !important;
                                               opacity:.8; border-width:1px !important; }
    .tx-filter-reset:hover                   { opacity:1; }

    /* ── Inventory Table compact (tx-table-compact) ──────────────────── */
    .tx-table-compact              { font-size:12.5px; }
    .tx-table-compact thead th     { padding:7px 10px !important; vertical-align:middle !important;
                                     background:#2a3f54 !important; color:#fff !important;
                                     font-size:12px !important; font-weight:600 !important;
                                     white-space:normal !important; border:none !important; }
    .tx-table-compact tbody td     { padding:6px 10px; vertical-align:middle; }
    /* Stripe manual hanya pada data row, bukan group row */
    .tx-table-compact tbody tr:not(.tx-group-row):nth-child(even) td { background:#f8f9fa; }
    .tx-table-compact tbody tr:not(.tx-group-row):hover td            { background:#eef3fb; }
    /* Nama barang: max 2 baris, ellipsis */
    .tx-col-nama span              { display:-webkit-box; -webkit-line-clamp:2;
                                     -webkit-box-orient:vertical; overflow:hidden;
                                     line-height:1.35; }
    /* Badge soft compact */
    .tx-badge-soft                 { font-size:11px; padding:2px 7px; font-weight:500; }
    .tx-badge-kategori             { background:#e8f0fe; color:#1a56db; border:1px solid #c7d7f9; }
    /* Tombol aksi compact */
    .tx-btn-aksi                   { padding:3px 9px !important; font-size:11.5px !important; }
    .tx-btn-aksi i                 { font-size:10.5px !important; }
    /* Row group kategori */
    .tx-table tbody .tx-group-row td,
    .tx-table tbody .tx-group-row:nth-child(odd) td,
    .tx-table tbody .tx-group-row:nth-child(even) td,
    .tx-table-compact tbody .tx-group-row td,
    .tx-table-compact tbody .tx-group-row:nth-child(odd) td,
    .tx-table-compact tbody .tx-group-row:nth-child(even) td {
        padding: 5px 9px !important;
        background: #8d99a6 !important;
        color: #fff !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-top: none !important;
        border-bottom: none !important;
    }
    </style>
  </head>

  <body class="{{ $bodyClass ?? 'nav-md' }}">
    <div class="container body">
      <div class="main_container">

        @include('layouts.sidebar')

        @include('layouts.header')

        <!-- page content -->
        <main class="right_col" role="main" aria-label="Main content">
          @yield('content')
        </main>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="text-center" style="padding:4px 0; font-size:12px; color:#aab2bd;">
            <i class="fas fa-store me-1" style="color:#ff9a2e;"></i>
            <strong style="color:#7a8a99;">Toko Amanda</strong>
            <span style="margin:0 6px;color:#dde;">·</span>
            Sistem Informasi Penjualan dan Stock
            <span style="margin:0 6px;color:#dde;">·</span>
            &copy; {{ date('Y') }}
            <span style="margin:0 6px;color:#dde;">·</span>
            <span style="font-size:11px;color:#c8cfd8;">Versi 1.0</span>
          </div>
        </footer>
        <!-- /footer content -->

      </div>
    </div>

    @yield('scripts')

    <script>
    /* ── UI only: posisi & max-height flyout child_menu nav-sm ── */
    (function () {
      var GAP = 8;

      function reposition(li) {
        var menu = li.querySelector(':scope > ul.child_menu');
        if (!menu) return;

        var vpH      = window.innerHeight;
        var liRect   = li.getBoundingClientRect();
        var startTop = liRect.top;          /* selalu mulai sejajar parent */
        var roomDown = vpH - startTop - GAP; /* ruang tersisa ke bawah viewport */

        /* Ukur tinggi asli konten tanpa pembatas */
        menu.style.maxHeight = 'none';
        var naturalH = menu.scrollHeight;

        /* Selalu posisikan sejajar parent */
        menu.style.top = startTop + 'px';

        if (naturalH <= roomDown) {
          /* Konten muat penuh — tidak perlu scroll */
          menu.style.maxHeight = naturalH + 'px';
        } else {
          /* Konten tidak muat — potong sesuai ruang, aktifkan scroll */
          menu.style.maxHeight = (roomDown > 80 ? roomDown : 80) + 'px';
        }
      }

      function bindAll() {
        document.querySelectorAll('.nav.side-menu > li').forEach(function (li) {
          li.addEventListener('mouseenter', function () {
            if (document.body.classList.contains('nav-sm')) reposition(li);
          });
          li.addEventListener('click', function () {
            if (document.body.classList.contains('nav-sm')) {
              setTimeout(function () { reposition(li); }, 20);
            }
          });
        });
      }

      document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', bindAll)
        : bindAll();
    })();
    </script>
  </body>
</html>
