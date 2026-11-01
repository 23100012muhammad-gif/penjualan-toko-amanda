@extends('layouts.app')

@section('title', 'Riwayat Pembelian | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-clock-history me-2"></i>Riwayat Pembelian
    </h3>
    <small class="text-muted">Transaksi &rsaquo; Riwayat Pembelian</small>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-box-arrow-in-down me-2"></i>Daftar Transaksi Pembelian</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        {{-- ===== SUMMARY CARD ===== --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0f4ff;border:1px solid #d0dcff;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#2a3f54;line-height:1.3;white-space:nowrap;">&#128230; {{ $totalTransaksi }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Transaksi</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0fff4;border:1px solid #b7ebc8;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#1e8c45;line-height:1.3;white-space:nowrap;">&#128230; {{ number_format($totalItem, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Item Dibeli</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#fffbf0;border:1px solid #ffe0a0;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#b45309;line-height:1.3;white-space:nowrap;">&#128176; Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Pengeluaran</div>
          </div>
        </div>

        {{-- ===== FILTER ===== --}}
        @php
          $mapBulanSingkat = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
          $labelBulanAwal  = ($periode==='bulan' && $bulan) ? ($mapBulanSingkat[$bulan] ?? 'Bulan') : 'Bulan';
          $labelTahunAwal  = ($periode==='tahun' && $tahun) ? $tahun : 'Tahun';
        @endphp
        <form method="GET" action="{{ route('pembelian.histori') }}" id="formHistori">
          <input type="hidden" name="periode" id="inputPeriode" value="{{ $periode }}">
          <input type="hidden" name="bulan"   id="inputBulan"   value="{{ $bulan }}">
          <input type="hidden" name="tahun"   id="inputTahun"   value="{{ $tahun }}">

          {{-- BARIS 1: Periode --}}
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

          {{-- BARIS 3: Keyword + Tombol --}}
          <div style="margin-bottom:10px;">
            <div style="display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap;">
              <div>
                <div class="lp-label">Cari Kode Pembelian</div>
                <div id="wrapKeywordHistori" style="position:relative;display:inline-block;">
                  <input type="text" name="keyword" id="inputKeywordHistori"
                         placeholder="Cari kode pembelian..."
                         value="{{ request('keyword') }}"
                         autocomplete="off"
                         style="width:220px;height:28px;padding:0 8px;font-size:12px;border:1.5px solid #ced4da;border-radius:4px;color:#495057;background:#fff;outline:none;box-sizing:border-box;">
                </div>
              </div>
              <div>
                <button type="submit" class="btn-ta btn-ta-dark">
                  <i class="fas fa-filter me-1"></i>Filter
                </button>
                @if($periode !== 'semua' || request('keyword'))
                <a href="{{ route('pembelian.histori') }}" class="btn-ta btn-ta-gray ms-1">
                  <i class="fas fa-times me-1"></i>Reset
                </a>
                @endif
              </div>
            </div>
          </div>

        </form>

        <div class="mb-2" style="font-size:12px;color:#6c757d;">
          Periode aktif: <strong class="text-dark">{{ $labelPeriode }}</strong>
          <span class="mx-2">&bull;</span>
          Menampilkan <strong>{{ $totalTransaksi }}</strong> transaksi
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle tx-table">
            <thead>
              <tr>
                <th width="50">No</th>
                <th width="130">No. Faktur</th>
                <th width="120">Tanggal</th>
                <th class="text-center" width="80">Total Item</th>
                <th class="text-end" width="160">Total Pembelian</th>
                <th>Catatan</th>
                <th class="text-center" width="110">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($pembelian as $item)
              <tr>
                <td class="text-muted small">{{ $loop->iteration + ($pembelian->currentPage() - 1) * $pembelian->perPage() }}</td>
                <td><code class="text-primary fw-semibold">{{ $item->kode_pembelian }}</code></td>
                <td style="white-space:nowrap;">{{ $item->tanggal->format('d-m-Y') }}</td>
                <td class="text-center">
                  <span class="badge bg-secondary">{{ $item->detailPembelian->count() }} item</span>
                </td>
                <td class="text-end fw-semibold" style="color:#b45309;">
                  Rp {{ number_format($item->total, 0, ',', '.') }}
                </td>
                <td class="text-muted" style="font-size:12px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                  {{ $item->catatan ?: '-' }}
                </td>
                <td class="text-center" style="white-space:nowrap;">
                  <button type="button" class="btn-ta btn-ta-blue tx-btn-aksi"
                          data-bs-toggle="modal"
                          data-bs-target="#modalDetail{{ $item->id }}"
                          title="Lihat Detail">
                    <i class="bi bi-eye"></i>
                  </button>
                  <a href="{{ route('pembelian.cetak', $item) }}" target="_blank"
                     class="btn-ta btn-ta-dark tx-btn-aksi" title="Cetak">
                    <i class="bi bi-printer"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                  {{ $periode !== 'semua' || request('keyword') ? 'Tidak ada data ditemukan' : 'Belum ada transaksi pembelian' }}
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
          {{ $pembelian->links() }}
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ===== MODAL DETAIL — di luar tabel ===== --}}
@foreach ($pembelian as $item)
<div class="modal fade" id="modalDetail{{ $item->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-box-arrow-in-down me-2 text-primary"></i>
          Detail — {{ $item->kode_pembelian }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <div class="row mb-3">
          <div class="col-6">
            <small class="text-muted d-block">Tanggal Pembelian</small>
            <strong>{{ $item->tanggal->format('d F Y') }}</strong>
          </div>
          <div class="col-6">
            <small class="text-muted d-block">Total Pengeluaran</small>
            <strong class="text-warning">Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
          </div>
        </div>

        @if ($item->catatan)
        <div class="mb-3">
          <small class="text-muted d-block">Catatan</small>
          <p class="mb-0">{{ $item->catatan }}</p>
        </div>
        @endif

        <hr class="my-2">

        <table class="table table-sm table-bordered mb-0">
          <thead style="background:#f8f9fa;">
            <tr>
              <th>Barang</th>
              <th class="text-center" width="100">Qty Beli</th>
              <th class="text-center" width="80">Satuan</th>
              <th class="text-end" width="130">Harga</th>
              <th class="text-end" width="140">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($item->detailPembelian as $detail)
            <tr>
              <td>
                <span class="fw-semibold">{{ $detail->barang->nama_barang ?? '-' }}</span>
                <br><small class="text-muted">{{ $detail->barang->kode_barang ?? '-' }}</small>
              </td>
              <td class="text-center">{{ $detail->jumlah }}</td>
              <td class="text-center">{{ strtoupper($detail->barang->satuan_beli ?? '-') }}</td>
              <td class="text-end">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
              <td class="text-end fw-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot style="background:#f8f9fa;">
            <tr>
              <td colspan="4" class="text-end fw-bold">Total</td>
              <td class="text-end fw-bold" style="color:#b45309;">
                Rp {{ number_format($item->total, 0, ',', '.') }}
              </td>
            </tr>
          </tfoot>
        </table>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endforeach

<div id="dropdownKeywordHistori"
     style="display:none;position:fixed;z-index:99999;
            background:#fff;border:1px solid #ced4da;
            border-radius:4px;max-height:220px;overflow-y:auto;
            box-shadow:0 4px 12px rgba(0,0,0,.15);min-width:200px;"></div>

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

// ── Autocomplete keyword ──────────────────────────────────────────────────
var daftarKodePembelian = {!! json_encode(
    $daftarKodePembelian->map(fn($t) => [
        'kode'    => $t->kode_pembelian,
        'tanggal' => $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d-m-Y') : '',
    ])->values()
) !!};

document.addEventListener('DOMContentLoaded', function() {
  var input = document.getElementById('inputKeywordHistori');
  var drop  = document.getElementById('dropdownKeywordHistori');
  var wrap  = document.getElementById('wrapKeywordHistori');
  var form  = input ? input.closest('form') : null;

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
      drop.innerHTML = list.slice(0, 12).map(function(t) {
        return '<div data-nama="' + t.kode + '"' +
               ' style="padding:8px 12px;font-size:12px;border-bottom:1px solid #f0f0f0;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">' +
               '<strong style="color:#2a3f54;">' + t.kode + '</strong>' +
               '<span style="color:#6c757d;font-size:11px;">' + t.tanggal + '</span>' +
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
    var hasil = daftarKodePembelian.filter(function(t) {
      return t.kode.toLowerCase().includes(q) || t.tanggal.toLowerCase().includes(q);
    });
    render(hasil);
  });

  input.addEventListener('focus', function() {
    var q = this.value.trim().toLowerCase();
    if (q.length >= 1) {
      var hasil = daftarKodePembelian.filter(function(t) {
        return t.kode.toLowerCase().includes(q) || t.tanggal.toLowerCase().includes(q);
      });
      render(hasil);
    }
  });

  input.addEventListener('blur', function() { setTimeout(tutup, 200); });
  input.addEventListener('keydown', function(e) { if (e.key === 'Escape') tutup(); });

  window.addEventListener('scroll', function() {
    if (drop.style.display === 'block') posisi();
  }, true);

  setPeriode(document.getElementById('inputPeriode').value || 'semua');
});
</script>
@endsection
