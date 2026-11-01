@extends('layouts.app')

@section('title', 'Laporan Pembelian | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-0 tx-heading">
        <i class="bi bi-box-arrow-in-down me-2"></i>Laporan Pembelian
      </h3>
      <small class="text-muted">Laporan &rsaquo; Laporan Pembelian</small>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="x_panel">

      <div class="x_title">
        <h4><i class="bi bi-table me-2"></i>Data Pembelian per Barang</h4>
        <div class="ms-auto d-flex gap-1 align-items-center">
          <a href="{{ route('laporan.pembelian.cetak') }}?{{ http_build_query(request()->query()) }}"
             target="_blank" class="btn-ta btn-ta-dark" title="Cetak / Simpan PDF">
            <i class="bi bi-printer me-1"></i>Cetak
          </a>
          <a href="{{ route('laporan.pembelian.export') }}?{{ http_build_query(request()->query()) }}"
             class="btn-ta btn-ta-green" title="Export Excel">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
          </a>
        </div>
        <div class="clearfix"></div>
      </div>

      <div class="x_content">

        {{-- ===== SUMMARY CARD ===== --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0f4ff;border:1px solid #d0dcff;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#2a3f54;line-height:1.3;white-space:nowrap;">&#128230; {{ number_format($totalData, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Data</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0fff4;border:1px solid #b7ebc8;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#1e8c45;line-height:1.3;white-space:nowrap;">&#128722; {{ number_format($totalBarang, 0, ',', '.') }} <span style="font-size:10px;font-weight:400;">item</span></div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Barang Dibeli</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#fffbf0;border:1px solid #ffe0a0;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#b45309;line-height:1.3;white-space:nowrap;">&#128176; Rp {{ number_format($totalPembelian, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Pembelian</div>
          </div>
        </div>

        {{-- ===== FILTER ===== --}}
        @php
          $mapBulanSingkat = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
          $labelBulanAwal  = ($periode==='bulan' && $bulan) ? ($mapBulanSingkat[$bulan] ?? 'Bulan') : 'Bulan';
          $labelTahunAwal  = ($periode==='tahun' && $tahun) ? $tahun : 'Tahun';
        @endphp
        <form method="GET" action="{{ route('laporan.pembelian') }}" id="formLaporan">
          <input type="hidden" name="periode" id="inputPeriode" value="{{ $periode }}">
          <input type="hidden" name="bulan"   id="inputBulan"   value="{{ $bulan }}">
          <input type="hidden" name="tahun"   id="inputTahun"   value="{{ $tahun }}">

          {{-- BARIS 1: Filter Periode --}}
          <div style="margin-bottom:8px;">
            <div class="lp-label">Periode</div>
            <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">

              <button type="button" id="pill_semua"
                      class="lp-pill {{ $periode==='semua' ? 'lp-pill-active' : '' }}"
                      onclick="setPeriode('semua')">Semua</button>

              <button type="button" id="pill_hari"
                      class="lp-pill {{ $periode==='hari' ? 'lp-pill-active' : '' }}"
                      onclick="setPeriode('hari')">Hari Ini</button>

              <div class="lp-drop-wrap">
                <button type="button" id="pill_bulan"
                        class="lp-pill {{ $periode==='bulan' ? 'lp-pill-active' : '' }}"
                        onclick="toggleDrop('dropBulan')" style="gap:5px;">
                  <span id="labelBulan">{{ $labelBulanAwal }}</span>
                  <i class="bi bi-chevron-down" style="font-size:9px;"></i>
                </button>
                <div class="lp-drop" id="dropBulan">
                  @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $n=>$nm)
                  <div class="lp-drop-item {{ $periode==='bulan' && (int)$bulan===$n ? 'lp-drop-active' : '' }}"
                       onclick="pilihBulan({{ $n }},'{{ $nm }}','{{ substr($nm,0,3) }}')">{{ $nm }}</div>
                  @endforeach
                </div>
              </div>

              <div class="lp-drop-wrap">
                <button type="button" id="pill_tahun"
                        class="lp-pill {{ $periode==='tahun' ? 'lp-pill-active' : '' }}"
                        onclick="toggleDrop('dropTahun')" style="gap:5px;">
                  <span id="labelTahun">{{ $labelTahunAwal }}</span>
                  <i class="bi bi-chevron-down" style="font-size:9px;"></i>
                </button>
                <div class="lp-drop" id="dropTahun">
                  @foreach($tahunList as $t)
                  <div class="lp-drop-item {{ $periode==='tahun' && (int)$tahun===(int)$t ? 'lp-drop-active' : '' }}"
                       onclick="pilihTahun({{ $t }})">{{ $t }}</div>
                  @endforeach
                </div>
              </div>

              <button type="button" id="pill_custom"
                      class="lp-pill {{ $periode==='custom' ? 'lp-pill-active' : '' }}"
                      onclick="setPeriode('custom')">Custom</button>

            </div>
          </div>

          {{-- BARIS 2: Custom tanggal --}}
          <div id="fieldCustom" style="margin-bottom:8px;{{ $periode==='custom' ? '' : 'display:none;' }}">
            <div style="display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap;">
              <div>
                <div class="lp-label">Dari Tanggal</div>
                <input type="date" name="tanggal_dari" class="lp-input"
                       value="{{ request('tanggal_dari') }}">
              </div>
              <div>
                <div class="lp-label">Sampai Tanggal</div>
                <input type="date" name="tanggal_sampai" class="lp-input"
                       value="{{ request('tanggal_sampai') }}">
              </div>
            </div>
          </div>

          {{-- BARIS 3: Kategori + Keyword + Tombol --}}
          <div style="margin-bottom:10px;">
            <div class="lp-label">Cari &amp; Filter</div>
            <div style="display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap;">
              <div>
                <div class="lp-label" style="margin-bottom:3px;">Kategori</div>
                <select name="kategori_id" style="width:180px;height:30px;padding:0 8px;font-size:12px;border:1.5px solid #ced4da;border-radius:4px;color:#495057;background:#fff;outline:none;">
                  <option value="">Semua Kategori</option>
                  @foreach($daftarKategori as $kat)
                  <option value="{{ $kat->id }}" {{ (int)$kategoriId === $kat->id ? 'selected' : '' }}>
                    {{ $kat->nama_kategori }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div>
                <div class="lp-label" style="margin-bottom:3px;">Cari Barang</div>
                <div id="wrapKeyword" style="position:relative;display:inline-block;">
                  <input type="text" name="keyword" id="inputKeyword"
                         placeholder="Kode / nama barang..."
                         value="{{ request('keyword') }}"
                         autocomplete="off"
                         style="width:240px;height:30px;padding:0 8px;font-size:12px;border:1.5px solid #ced4da;border-radius:4px;color:#495057;background:#fff;outline:none;box-sizing:border-box;">
                </div>
              </div>
              <div>
                <button type="submit" class="btn-ta btn-ta-dark">
                  <i class="fas fa-filter me-1"></i>Filter
                </button>
                @if($periode !== 'semua' || request('keyword') || $kategoriId)
                <a href="{{ route('laporan.pembelian') }}" class="btn-ta btn-ta-gray ms-1">
                  <i class="fas fa-times me-1"></i>Reset
                </a>
                @endif
              </div>
            </div>
          </div>

        </form>

        {{-- ===== PERIODE AKTIF ===== --}}
        <div class="mb-2" style="font-size:12px;color:#6c757d;">
          <span>Periode aktif: <strong class="text-dark">{{ $labelPeriode }}</strong></span>
          @if($kategoriId)
          <span class="mx-1">&bull;</span>
          <span>Kategori: <strong class="text-dark">{{ $daftarKategori->firstWhere('id', $kategoriId)?->nama_kategori }}</strong></span>
          @endif
          <span class="mx-2">&bull;</span>
          <span>Menampilkan <strong>{{ $totalData }}</strong> data</span>
        </div>

        {{-- ===== TABEL ===== --}}
        <div class="table-responsive">
          <table class="table table-hover align-middle tx-table">
            <thead>
              <tr>
                <th width="40">No</th>
                <th width="110">Tanggal</th>
                <th width="90">Kode Barang</th>
                <th>Nama Barang</th>
                <th class="text-center" width="80">Satuan Barang</th>
                <th class="text-end" width="120">Harga</th>
                <th class="text-center" width="90">Sat. Jual / Unit</th>
                <th class="text-end" width="130">Harga Beli/Unit</th>
                <th class="text-center" width="80">Jml Beli</th>
                <th class="text-end" width="130">Total Harga</th>
              </tr>
            </thead>
            <tbody>
              @php $prevKat = null; $no = $detail->firstItem(); @endphp
              @forelse ($detail as $row)
              @php $namaKat = $row->barang->kategori->nama_kategori ?? 'Tanpa Kategori'; @endphp
              @if($namaKat !== $prevKat)
                @php $prevKat = $namaKat; @endphp
                <tr class="tx-group-row">
                  <td colspan="10"><i class="bi bi-tag me-1"></i>{{ strtoupper($prevKat) }}</td>
                </tr>
              @endif
              <tr>
                <td class="text-muted small">{{ $no++ }}</td>
                <td style="white-space:nowrap;">{{ $row->pembelian->tanggal->format('d-m-Y') }}</td>
                <td><code class="text-primary fw-semibold" style="font-size:11px;">{{ $row->barang->kode_barang ?? '-' }}</code></td>
                <td class="fw-semibold">{{ $row->barang->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ $row->barang->satuan_beli ?? '-' }}</td>
                <td class="text-end">Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                <td class="text-center">{{ $row->barang->satuan_jual ?? '-' }}</td>
                <td class="text-end">Rp {{ number_format($row->barang->harga_satuan_beli ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $row->jumlah }}</td>
                <td class="text-end fw-semibold text-success">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                  {{ request('keyword') || $periode !== 'semua' ? 'Tidak ada data ditemukan' : 'Belum ada data pembelian' }}
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
          {{ $detail->links() }}
        </div>

      </div>
    </div>
  </div>
</div>

<div id="dropdownKeyword"
     style="display:none;position:fixed;z-index:99999;
            background:#fff;border:1px solid #ced4da;
            border-radius:4px;max-height:220px;overflow-y:auto;
            box-shadow:0 4px 12px rgba(0,0,0,.15);min-width:260px;">
</div>

@endsection

@section('scripts')
<style>
.lp-label { font-size:11px;color:#6c757d;font-weight:600;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px; }
.lp-input { height:28px;padding:0 8px;font-size:12px;border:1.5px solid #ced4da;border-radius:4px;outline:none;color:#495057;background:#fff;box-sizing:border-box;line-height:1.5; }
.lp-input:focus { border-color:#2a3f54; }
.lp-pill { display:inline-flex;align-items:center;justify-content:center;height:30px;padding:0 12px;font-size:12px;font-weight:500;border:1.5px solid #ced4da;border-radius:6px;cursor:pointer;background:#fff;color:#495057;white-space:nowrap;transition:border-color .15s,background .15s,color .15s;text-decoration:none;box-sizing:border-box;line-height:1; }
.lp-pill:hover  { border-color:#2a3f54;color:#2a3f54;background:#f0f4ff; }
.lp-pill-active { background:#2a3f54 !important;color:#fff !important;border-color:#2a3f54 !important; }
.lp-drop-wrap { position:relative;display:inline-block; }
.lp-drop { display:none;position:absolute;top:calc(100% + 4px);left:0;min-width:130px;background:#fff;border:1.5px solid #ced4da;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.12);z-index:9999;max-height:220px;overflow-y:auto; }
.lp-drop.open { display:block; }
.lp-drop-item { padding:7px 14px;font-size:12px;cursor:pointer;color:#495057;white-space:nowrap; }
.lp-drop-item:hover  { background:#f0f4ff;color:#2a3f54; }
.lp-drop-active      { background:#e8f0fe;color:#2a3f54;font-weight:600; }
</style>

<script>
document.addEventListener('click', function(e) {
  if (!e.target.closest('.lp-drop-wrap')) {
    document.querySelectorAll('.lp-drop.open').forEach(function(d){ d.classList.remove('open'); });
  }
});

function toggleDrop(id) {
  var el = document.getElementById(id);
  var isOpen = el.classList.contains('open');
  document.querySelectorAll('.lp-drop.open').forEach(function(d){ d.classList.remove('open'); });
  if (!isOpen) el.classList.add('open');
}

function setPeriode(val) {
  document.getElementById('inputPeriode').value = val;
  document.querySelectorAll('.lp-drop.open').forEach(function(d){ d.classList.remove('open'); });
  ['pill_semua','pill_hari','pill_bulan','pill_tahun','pill_custom'].forEach(function(id){
    var el = document.getElementById(id);
    if (el) el.classList.remove('lp-pill-active');
  });
  var target = document.getElementById('pill_' + val);
  if (target) target.classList.add('lp-pill-active');
  document.getElementById('fieldCustom').style.display = val === 'custom' ? 'block' : 'none';
}

function pilihBulan(n, nama, singkat) {
  document.getElementById('inputBulan').value = n;
  document.getElementById('labelBulan').textContent = singkat;
  document.getElementById('dropBulan').classList.remove('open');
  document.querySelectorAll('#dropBulan .lp-drop-item').forEach(function(el){
    el.classList.toggle('lp-drop-active', el.textContent.trim() === nama);
  });
  setPeriode('bulan');
}

function pilihTahun(t) {
  document.getElementById('inputTahun').value = t;
  document.getElementById('labelTahun').textContent = t;
  document.getElementById('dropTahun').classList.remove('open');
  document.querySelectorAll('#dropTahun .lp-drop-item').forEach(function(el){
    el.classList.toggle('lp-drop-active', el.textContent.trim() == t);
  });
  var p = document.getElementById('inputPeriode').value;
  setPeriode(p === 'bulan' ? 'bulan' : 'tahun');
}

// ── Autocomplete keyword ─────────────────────────────────────────────────
var daftarBarangLaporan = {!! json_encode(
    $daftarBarang->map(fn($b) => ['kode' => $b->kode_barang, 'nama' => $b->nama_barang])->values()
) !!};

document.addEventListener('DOMContentLoaded', function() {
  var inputKw  = document.getElementById('inputKeyword');
  var dropKw   = document.getElementById('dropdownKeyword');
  var wrapKw   = document.getElementById('wrapKeyword');

  if (!inputKw || !dropKw) return;

  function posisi() {
    var r = wrapKw.getBoundingClientRect();
    dropKw.style.top   = (r.bottom + window.scrollY) + 'px';
    dropKw.style.left  = (r.left + window.scrollX) + 'px';
    dropKw.style.width = r.width + 'px';
  }

  function render(list) {
    posisi();
    if (!list.length) {
      dropKw.innerHTML = '<div style="padding:8px 12px;font-size:12px;color:#6c757d;">Tidak ditemukan</div>';
    } else {
      dropKw.innerHTML = list.slice(0, 12).map(function(b) {
        return '<div data-kode="' + b.kode + '" data-nama="' + b.nama.replace(/"/g, '&quot;') + '"' +
               ' style="padding:8px 12px;font-size:12px;border-bottom:1px solid #f0f0f0;cursor:pointer;">' +
               '<strong style="color:#2a3f54;">' + b.kode + '</strong>' +
               '<span style="color:#6c757d;"> &mdash; </span>' + b.nama +
               '</div>';
      }).join('');
    }
    dropKw.style.display = 'block';
  }

  function tutup() { dropKw.style.display = 'none'; }

  dropKw.addEventListener('mousedown', function(e) {
    var item = e.target.closest('[data-nama]');
    if (!item) return;
    e.preventDefault();
    inputKw.value = item.getAttribute('data-nama');
    tutup();
    document.getElementById('formLaporan').submit();
  });

  inputKw.addEventListener('input', function() {
    var q = this.value.trim().toLowerCase();
    if (q.length < 1) { tutup(); return; }
    render(daftarBarangLaporan.filter(function(b) {
      return b.nama.toLowerCase().includes(q) || b.kode.toLowerCase().includes(q);
    }));
  });

  inputKw.addEventListener('focus', function() {
    var q = this.value.trim().toLowerCase();
    if (q.length >= 1) {
      render(daftarBarangLaporan.filter(function(b) {
        return b.nama.toLowerCase().includes(q) || b.kode.toLowerCase().includes(q);
      }));
    }
  });

  inputKw.addEventListener('blur', function() { setTimeout(tutup, 200); });
  inputKw.addEventListener('keydown', function(e) { if (e.key === 'Escape') tutup(); });

  window.addEventListener('scroll', function() {
    if (dropKw.style.display === 'block') posisi();
  }, true);

  setPeriode(document.getElementById('inputPeriode').value || 'semua');
});
</script>
@endsection
