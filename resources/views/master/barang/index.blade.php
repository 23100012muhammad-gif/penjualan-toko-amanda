@extends('layouts.app')

@section('title', 'Master Barang (Stok Awal) | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-0 tx-heading">
        <i class="bi bi-archive me-2"></i>Master Barang (Stok Awal)
      </h3>
      <small class="text-muted">Master Data › Master Barang (Stok Awal)</small>
    </div>
    <a href="{{ route('barang.create') }}" class="btn-ta btn-ta-blue">
      <i class="fas fa-plus me-1"></i>Tambah Barang
    </a>
  </div>
</div>

<x-alert />

@if (session('import_errors') && count(session('import_errors')) > 0)
<div class="alert alert-warning alert-dismissible fade show">
  <strong><i class="bi bi-exclamation-triangle me-1"></i>Beberapa baris dilewati:</strong>
  <ul class="mb-0 mt-1">
    @foreach (session('import_errors') as $err)
      <li><small>{{ $err }}</small></li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Modal Import --}}
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Import Data Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('barang.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Format kolom:</strong> <code>nama_barang, kategori, tanggal_stok_awal, jumlah_beli, isi_per_satuan, total_harga_beli, harga_jual</code><br>
            <div class="mt-2">
              <span class="text-muted">• <strong>tanggal_stok_awal</strong> — format YYYY-MM-DD, contoh: 2025-01-15 (kosongkan = hari ini)</span><br>
              <span class="text-muted">• <strong>jumlah_beli</strong> — jumlah kemasan yang dibeli dari supplier (contoh: 2 KARDUS)</span><br>
              <span class="text-muted">• <strong>isi_per_satuan</strong> — isi dalam 1 kemasan (contoh: 1 KARDUS = 24 PCS)</span><br>
              <span class="text-muted">• <strong>total_harga_beli</strong> — total bayar ke supplier untuk seluruh pembelian</span><br>
              <span class="text-muted">• <strong>harga_jual</strong> — harga jual per satuan jual (persentase keuntungan dihitung otomatis)</span>
            </div>
            <div class="mt-2">
              Harga satuan beli dan persentase keuntungan dihitung <strong>otomatis</strong> oleh sistem.
            </div>
            <div class="mt-2">
              Belum punya template? <a href="{{ route('barang.template') }}" class="alert-link">Download di sini</a>.
            </div>
          </div>
          <div class="mb-3">
            <label for="file" class="form-label fw-semibold">Pilih File Excel / CSV</label>
            <input type="file" id="file" name="file"
                   class="form-control @error('file') is-invalid @enderror"
                   accept=".xlsx,.xls,.csv">
            @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Format: .xlsx, .xls, .csv — Maks. 2MB</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> Import</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-box-seam me-2"></i>Daftar Master Barang (Stok Awal)</h4>
        <div class="d-flex gap-2 align-items-center">
          <a href="{{ route('barang.template') }}" class="btn-ta btn-ta-gray" title="Download Template">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Template
          </a>
          <button type="button" class="btn-ta btn-ta-green" data-bs-toggle="modal" data-bs-target="#modalImport">
            <i class="bi bi-file-earmark-excel me-1"></i>Import
          </button>
          <a href="{{ route('barang.export') }}" class="btn-ta btn-ta-purple">
            <i class="bi bi-download me-1"></i>Export
          </a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        {{-- INFO CARD --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0f4ff;border:1px solid #d0dcff;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#2a3f54;line-height:1.3;white-space:nowrap;">&#128230; {{ number_format($totalSemuaBarang, 0, ',', '.') }} <span style="font-size:10px;font-weight:400;">barang</span></div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Barang</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#fffbf0;border:1px solid #ffe0a0;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#b45309;line-height:1.3;white-space:nowrap;">&#128176; Rp {{ number_format($totalModalAwal, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Modal Awal Barang</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0fff4;border:1px solid #b7ebc8;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#1e8c45;line-height:1.3;white-space:nowrap;">&#128230; {{ number_format($totalStokAwal, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Stok Awal</div>
          </div>
        </div>

        {{-- Keterangan Stok --}}
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
          <small class="text-muted fw-semibold me-1">Keterangan Stok:</small>
          <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Tersedia (&gt;5)</span>
          <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Menipis (1&ndash;5)</span>
          <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Habis</span>
        </div>

        {{-- Filter Toolbar --}}
        @php
          $adaFilter = request('search') || request('kategori_id') || request('kondisi_stok')
                    || request('filter_profit') || (request('sorting') && request('sorting') !== 'default');
        @endphp
        <form method="GET" action="{{ route('barang.index') }}" class="mb-3">
          <div class="tx-filter-toolbar">

            <div id="wrapSearchBarang" style="position:relative;" class="input-group tx-filter-search">
              <span class="input-group-text"><i class="fas fa-search" style="font-size:9px;"></i></span>
              <input type="text" name="search" id="inputSearchBarang" class="form-control"
                     placeholder="Cari nama / kode..."
                     value="{{ request('search') }}"
                     autocomplete="off">
            </div>

            <select name="kategori_id" class="form-select" style="width:140px;">
              <option value="">Semua Kategori</option>
              @foreach ($semuaKategori as $kat)
                <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                  {{ $kat->nama_kategori }}
                </option>
              @endforeach
            </select>

            <select name="kondisi_stok" class="form-select" style="width:110px;">
              <option value="">Semua Stok</option>
              <option value="aman"    {{ request('kondisi_stok') === 'aman'    ? 'selected' : '' }}>Aman</option>
              <option value="menipis" {{ request('kondisi_stok') === 'menipis' ? 'selected' : '' }}>Menipis</option>
              <option value="habis"   {{ request('kondisi_stok') === 'habis'   ? 'selected' : '' }}>Habis</option>
            </select>

            <select name="filter_profit" class="form-select" style="width:110px;">
              <option value="">Semua Profit</option>
              <option value="rendah" {{ request('filter_profit') === 'rendah' ? 'selected' : '' }}>&lt; 15%</option>
              <option value="sedang" {{ request('filter_profit') === 'sedang' ? 'selected' : '' }}>15–25%</option>
              <option value="tinggi" {{ request('filter_profit') === 'tinggi' ? 'selected' : '' }}>&gt; 25%</option>
            </select>

            <select name="sorting" class="form-select" style="width:150px;">
              <option value="default"          {{ request('sorting','default') === 'default'          ? 'selected' : '' }}>Urutan Default</option>
              <option value="nama_az"          {{ request('sorting') === 'nama_az'          ? 'selected' : '' }}>Nama A–Z</option>
              <option value="harga_tertinggi"  {{ request('sorting') === 'harga_tertinggi'  ? 'selected' : '' }}>Harga Tertinggi</option>
              <option value="harga_terendah"   {{ request('sorting') === 'harga_terendah'   ? 'selected' : '' }}>Harga Terendah</option>
              <option value="profit_tertinggi" {{ request('sorting') === 'profit_tertinggi' ? 'selected' : '' }}>Profit Tertinggi</option>
              <option value="stok_terbanyak"   {{ request('sorting') === 'stok_terbanyak'   ? 'selected' : '' }}>Stok Terbanyak</option>
              <option value="terbaru"          {{ request('sorting') === 'terbaru'          ? 'selected' : '' }}>Terbaru</option>
            </select>

            <button type="submit" class="btn-ta btn-ta-dark tx-filter-btn">
              <i class="fas fa-filter me-1"></i>Filter
            </button>

            @if ($adaFilter)
              <a href="{{ route('barang.index') }}" class="btn-ta btn-ta-gray tx-filter-reset">
                <i class="fas fa-times me-1"></i>Reset
              </a>
            @endif

          </div>
        </form>

        <div style="overflow-x:auto;">
          <table class="table table-hover align-middle mb-0 tx-table" style="font-size:11.5px;">
            <thead>
              <tr>
                <th class="text-center" style="width:32px;">No</th>
                <th style="width:85px;">Kode</th>
                <th>Nama Barang</th>
                <th class="text-center" style="width:80px;">Kategori</th>
                <th class="text-center" style="width:90px;">Tgl. Stok Awal</th>
                <th class="text-center" style="width:55px;">Jml Beli Awal</th>
                <th class="text-center" style="width:65px;">Sat. Beli</th>
                <th class="text-center" style="width:65px;">Isi/Satuan</th>
                <th class="text-end" style="width:120px;">Total Bayar Supplier</th>
                <th class="text-center" style="width:65px;">Sat. Jual</th>
                <th class="text-end" style="width:105px;">Harga Satuan</th>
                <th class="text-center" style="width:52px;">Profit</th>
                <th class="text-end" style="width:105px;">Harga Jual</th>
                <th class="text-center" style="width:60px;">Stok</th>
                <th class="text-center" style="width:80px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @php
                $prevKategori = null;
                $showGrouping = !request('sorting') || request('sorting') === 'default';
              @endphp
              @forelse ($barang as $item)
              @if ($showGrouping && $item->kategori?->nama_kategori !== $prevKategori)
                @php $prevKategori = $item->kategori?->nama_kategori; @endphp
                <tr class="tx-group-row">
                  <td colspan="15"><i class="bi bi-tag me-1"></i>{{ $prevKategori ?? 'Tanpa Kategori' }}</td>
                </tr>
              @endif
              @php
                $isi        = max((int)($item->isi_per_satuan_beli ?? 1), 1);
                $totalBayar = (float)($item->total_harga_beli ?? $item->harga_beli ?? 0);
                $stokItem   = (int)($item->stok ?? 0);
                $jumlahBeli = (int)($item->jumlah_beli_awal ?? 0);
              @endphp
              <tr>
                <td class="text-center text-muted">{{ $barang->firstItem() + $loop->index }}</td>
                <td><code class="text-primary fw-semibold" style="font-size:11px;">{{ $item->kode_barang }}</code></td>
                <td class="tx-col-nama">
                  <span title="{{ $item->nama_barang }}" class="fw-semibold">{{ $item->nama_barang }}</span>
                </td>
                <td class="text-center">
                  <span class="badge tx-badge-soft tx-badge-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                </td>
                <td class="text-center" style="white-space:nowrap;">
                  {{ $item->tanggal_stok_awal ? $item->tanggal_stok_awal->format('d-m-Y') : '-' }}
                </td>
                <td class="text-center">{{ $jumlahBeli }}</td>
                <td class="text-center">{{ $item->satuan_beli }}</td>
                <td class="text-center">{{ $isi }}</td>
                <td class="text-end">Rp {{ number_format($totalBayar, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->satuan_jual }}</td>
                <td class="text-end">Rp {{ number_format($item->harga_satuan_beli ?? ($item->harga_beli / $isi), 0, ',', '.') }}</td>
                <td class="text-center">
                  @php
                    $profit = (float)($item->persentase_keuntungan ?? 0);
                    $profitFmt = ($profit == floor($profit))
                        ? number_format($profit, 0, ',', '.')
                        : number_format($profit, 2, ',', '.');
                  @endphp
                  <span class="badge bg-warning text-dark tx-badge-soft">{{ $profitFmt }}%</span>
                </td>
                <td class="text-end fw-semibold text-success">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                <td class="text-center">
                  @if ($item->stok <= 0)
                    <span class="badge bg-danger tx-badge-soft">Habis</span>
                  @elseif ($item->stok <= 5)
                    <span class="badge bg-warning text-dark tx-badge-soft">{{ $item->stok }}</span>
                  @else
                    <span class="badge bg-success tx-badge-soft">{{ $item->stok }}</span>
                  @endif
                </td>
                <td class="text-center" style="white-space:nowrap;">
                  <a href="{{ route('barang.edit', $item) }}" class="btn-ta btn-ta-edit tx-btn-aksi" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('barang.destroy', $item) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus barang \'{{ $item->nama_barang }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ta btn-ta-delete tx-btn-aksi" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="15" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                  @if ($adaFilter)
                    Tidak ada data yang cocok dengan filter.<br>
                    <a href="{{ route('barang.index') }}" class="btn btn-sm btn-secondary mt-2">
                      <i class="fas fa-times me-1"></i> Reset Filter
                    </a>
                  @else
                    Belum ada data barang.
                  @endif
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
          {{ $barang->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<div id="dropdownSearchBarang"
     style="display:none;position:fixed;z-index:99999;
            background:#fff;border:1px solid #ced4da;
            border-radius:4px;max-height:220px;overflow-y:auto;
            box-shadow:0 4px 12px rgba(0,0,0,.15);"></div>

@endsection

@section('scripts')
<script>
var daftarMasterBarang = {!! json_encode(
    $semuaBarang->map(fn($b) => [
        'kode'      => $b->kode_barang,
        'nama'      => $b->nama_barang,
        'kategori'  => $b->nama_kategori ?? '',
    ])->values()
) !!};

document.addEventListener('DOMContentLoaded', function() {
  var input  = document.getElementById('inputSearchBarang');
  var drop   = document.getElementById('dropdownSearchBarang');
  var wrap   = document.getElementById('wrapSearchBarang');
  var form   = input ? input.closest('form') : null;

  if (!input || !drop) return;

  function posisi() {
    var r = wrap.getBoundingClientRect();
    drop.style.top   = (r.bottom + window.scrollY) + 'px';
    drop.style.left  = (r.left + window.scrollX) + 'px';
    drop.style.width = r.width + 'px';
  }

  function render(list) {
    posisi();
    if (!list.length) {
      drop.innerHTML = '<div style="padding:8px 12px;font-size:12px;color:#6c757d;">Tidak ditemukan</div>';
    } else {
      drop.innerHTML = list.slice(0, 12).map(function(b) {
        return '<div data-nama="' + b.nama.replace(/"/g, '&quot;') + '"' +
               ' style="padding:8px 12px;font-size:12px;border-bottom:1px solid #f0f0f0;cursor:pointer;">' +
               '<strong style="color:#2a3f54;">' + b.kode + '</strong>' +
               '<span style="color:#6c757d;"> &mdash; </span>' + b.nama +
               '</div>';
      }).join('');
    }
    drop.style.display = 'block';
  }

  function tutup() { drop.style.display = 'none'; }

  drop.addEventListener('mousedown', function(e) {
    var item = e.target.closest('[data-nama]');
    if (!item) return;
    e.preventDefault();
    input.value = item.getAttribute('data-nama');
    tutup();
    if (form) form.submit();
  });

  input.addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    if (q.length < 1) { tutup(); return; }
    var hasil = daftarMasterBarang.filter(function(b) {
      return b.nama.toLowerCase().includes(q) || b.kode.toLowerCase().includes(q) || b.kategori.toLowerCase().includes(q);
    });
    render(hasil);
  });

  input.addEventListener('focus', function() {
    var q = this.value.trim().toLowerCase();
    if (q.length >= 1) {
      var hasil = daftarMasterBarang.filter(function(b) {
        return b.nama.toLowerCase().includes(q) || b.kode.toLowerCase().includes(q) || b.kategori.toLowerCase().includes(q);
      });
      render(hasil);
    }
  });

  input.addEventListener('blur', function() { setTimeout(tutup, 200); });
  input.addEventListener('keydown', function(e) { if (e.key === 'Escape') tutup(); });

  window.addEventListener('scroll', function() {
    if (drop.style.display === 'block') posisi();
  }, true);
});
</script>
@endsection
