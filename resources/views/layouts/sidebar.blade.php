<aside class="col-md-3 left_col" aria-label="Sidebar navigation">
  <div class="left_col scroll-view">
    <div class="navbar nav_title border-0" style="padding:14px 8px 12px;">
      <a href="{{ route('dashboard') }}" class="site_title" style="display:flex;align-items:center;gap:11px;text-decoration:none;">
        <span style="display:flex;align-items:center;justify-content:center;width:38px;height:38px;background:linear-gradient(135deg,#ff9a2e,#f06000);border-radius:10px;flex-shrink:0;overflow:hidden;">
          <i class="fas fa-store" style="font-size:17px;color:#fff;"></i>
        </span>
        <span style="display:flex;flex-direction:column;line-height:1.2;">
          <span style="font-size:18px;font-weight:800;color:#fff;letter-spacing:.4px;font-family:'Segoe UI',system-ui,sans-serif;">Toko Amanda</span>
          <span style="font-size:11px;color:rgba(255,255,255,.55);font-weight:400;letter-spacing:.2px;margin-top:1px;">Sistem Informasi Penjualan</span>
        </span>
      </a>
    </div>

    <div class="clearfix"></div>

    <!-- Profil singkat -->
    <div class="profile clearfix">
      <div class="profile_pic">
        <img src="{{ Auth::user()->avatarUrl() }}"
             alt="Foto profil {{ Auth::user()->name }}"
             class="img-circle profile_img"
             loading="lazy"
             style="width:60px;height:60px;object-fit:cover;">
      </div>
      <div class="profile_info">
        <span>Selamat datang,</span>
        <h4>{{ Auth::user()->name }}</h4>
      </div>
    </div>

    <br />

    <!-- Sidebar menu -->
    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
      <div class="menu_section">
        <ul class="nav side-menu">

          {{-- Dashboard --}}
          @if(Auth::user()->isPemilik() || Auth::user()->isAdmin())
          <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
              <i class="bi bi-speedometer2"></i> Dashboard
            </a>
          </li>
          @endif

          {{-- Master Data --}}
          @if(Auth::user()->isAdmin())
          <li class="{{ request()->routeIs('kategori.*', 'barang.*', 'kategori-pengeluaran.*') ? 'active' : '' }}">
            <a>
              <i class="bi bi-box-seam"></i> Master Data
              <span class="bi bi-chevron-down"></span>
            </a>
            <ul class="nav child_menu">
              <li class="{{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                <a href="{{ route('kategori.index') }}">
                  <i class="bi bi-tags"></i> Kategori Barang
                </a>
              </li>
              <li class="{{ request()->routeIs('barang.*') ? 'active' : '' }}">
                <a href="{{ route('barang.index') }}">
                  <i class="bi bi-archive"></i> Master Barang (Stok Awal)
                </a>
              </li>
              <li class="{{ request()->routeIs('kategori-pengeluaran.*') ? 'active' : '' }}">
                <a href="{{ route('kategori-pengeluaran.index') }}">
                  <i class="bi bi-wallet2"></i> Kategori Pengeluaran
                </a>
              </li>
            </ul>
          </li>
          @endif

          {{-- Transaksi --}}
          <li class="{{ request()->routeIs('penjualan.*', 'pembelian.*') ? 'active' : '' }}">
            <a>
              <i class="bi bi-cart-check"></i> {{ Auth::user()->isPemilik() ? 'Riwayat Transaksi' : 'Transaksi' }}
              <span class="bi bi-chevron-down"></span>
            </a>
            <ul class="nav child_menu">
              @if(Auth::user()->isAdmin() || Auth::user()->isKasir())
              <li class="{{ request()->routeIs('penjualan.create', 'penjualan.store') ? 'active' : '' }}">
                <a href="{{ route('penjualan.create') }}">
                  <i class="bi bi-plus-circle"></i> Penjualan Baru
                </a>
              </li>
              @endif
              
              <li class="{{ request()->routeIs('penjualan.histori') ? 'active' : '' }}">
                <a href="{{ route('penjualan.histori') }}">
                  <i class="bi bi-clock-history"></i> Riwayat Penjualan
                </a>
              </li>
              
              @if(Auth::user()->isAdmin())
              <li class="{{ request()->routeIs('pembelian.create', 'pembelian.store') ? 'active' : '' }}">
                <a href="{{ route('pembelian.create') }}">
                  <i class="bi bi-box-arrow-in-down"></i> Pembelian / Restock
                </a>
              </li>
              @endif
              
              @if(Auth::user()->isPemilik() || Auth::user()->isAdmin())
              <li class="{{ request()->routeIs('pembelian.histori') ? 'active' : '' }}">
                <a href="{{ route('pembelian.histori') }}">
                  <i class="bi bi-clock-history"></i> Riwayat Pembelian
                </a>
              </li>
              @endif
            </ul>
          </li>

          {{-- Pengeluaran --}}
          @if(Auth::user()->isAdmin())
          <li class="{{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">
            <a href="{{ route('pengeluaran.index') }}">
              <i class="bi bi-cash-stack"></i> Pengeluaran
            </a>
          </li>
          @endif

          {{-- Laporan --}}
          @if(Auth::user()->isPemilik())
          <li class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <a>
              <i class="bi bi-bar-chart-line"></i> Laporan
              <span class="bi bi-chevron-down"></span>
            </a>
            <ul class="nav child_menu">
              <li class="{{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">
                <a href="{{ route('laporan.penjualan') }}">
                  <i class="bi bi-receipt"></i> Laporan Penjualan
                </a>
              </li>
              <li class="{{ request()->routeIs('laporan.pembelian') ? 'active' : '' }}">
                <a href="{{ route('laporan.pembelian') }}">
                  <i class="bi bi-box-arrow-in-down"></i> Laporan Pembelian
                </a>
              </li>
              <li class="{{ request()->routeIs('laporan.persediaan', 'laporan.persediaan.*') ? 'active' : '' }}">
                <a href="{{ route('laporan.persediaan') }}">
                  <i class="bi bi-boxes"></i> Laporan Persediaan
                </a>
              </li>
              <li class="{{ request()->routeIs('laporan.penjualan-pembelian', 'laporan.penjualan-pembelian.*') ? 'active' : '' }}">
                <a href="{{ route('laporan.penjualan-pembelian') }}">
                  <i class="bi bi-arrow-left-right"></i> Laporan jual&ndash;beli
                </a>
              </li>
              <li class="{{ request()->routeIs('laporan.laba-rugi') ? 'active' : '' }}">
                <a href="{{ route('laporan.laba-rugi') }}">
                  <i class="bi bi-graph-up-arrow"></i> Laporan Laba Rugi
                </a>
              </li>
            </ul>
          </li>
          @endif

          {{-- Kelola User --}}
          @if(Auth::user()->isPemilik())
          <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <a href="{{ route('users.index') }}">
              <i class="bi bi-people"></i> Kelola User
            </a>
          </li>
          @endif

        </ul>
      </div>
    </div>
    <!-- /sidebar menu -->



  </div>
</aside>
