@extends('layouts.app')

@section('title', 'Pengeluaran | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-0 tx-heading">
        <i class="bi bi-cash-stack me-2"></i>Pengeluaran
      </h3>
      <small class="text-muted">Biaya Operasional Toko</small>
    </div>
    <a href="{{ route('pengeluaran.create') }}" class="btn-ta btn-ta-blue">
      <i class="fas fa-plus me-1"></i> Catat Pengeluaran
    </a>
  </div>
</div>

<x-alert />

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-cash-stack me-2"></i>Daftar Pengeluaran</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        {{-- SUMMARY CARD --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0f4ff;border:1px solid #d0dcff;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#2a3f54;line-height:1.3;white-space:nowrap;">&#128230; {{ $totalTransaksi }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Transaksi</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#fff5f5;border:1px solid #fecaca;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#b91c1c;line-height:1.3;white-space:nowrap;">&#128176; Rp {{ number_format($totalNominalPengeluaran, 0, ',', '.') }}</div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Nominal Pengeluaran</div>
          </div>
        </div>

        @php
          $mapBulanSingkat = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
          $labelBulanAwal  = ($periode==='bulan' && $bulan) ? ($mapBulanSingkat[$bulan] ?? 'Bulan') : 'Bulan';
          $labelTahunAwal  = ($periode==='tahun' && $tahun) ? $tahun : 'Tahun';
        @endphp

        <form method="GET" action="{{ route('pengeluaran.index') }}" id="formPengeluaran">
          <input type="hidden" name="periode" id="inputPeriode" value="{{ $periode }}">
          <input type="hidden" name="bulan"   id="inputBulan"   value="{{ $bulan }}">
          <input type="hidden" name="tahun"   id="inputTahun"   value="{{ $tahun }}">

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

          <div style="margin-bottom:10px;">
            <div style="display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap;">
              <div>
                <div class="lp-label">Cari Kategori / Keterangan</div>
                <input type="text" name="keyword"
                       placeholder="Nama kategori atau keterangan..."
                       value="{{ request('keyword') }}"
                       style="width:240px;height:28px;padding:0 8px;font-size:12px;border:1.5px solid #ced4da;border-radius:4px;color:#495057;background:#fff;outline:none;box-sizing:border-box;">
              </div>
              <div>
                <button type="submit" class="btn-ta btn-ta-dark">
                  <i class="fas fa-filter me-1"></i>Filter
                </button>
                @if($periode !== 'semua' || request('keyword'))
                <a href="{{ route('pengeluaran.index') }}" class="btn-ta btn-ta-gray ms-1">
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
                <th width="110">Tanggal</th>
                <th>Kategori</th>
                <th class="text-end" width="150">Nominal</th>
                <th>Keterangan</th>
                <th width="120">Dicatat Oleh</th>
                <th class="text-center" width="80">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($pengeluaran as $item)
              <tr>
                <td class="text-muted small">{{ $pengeluaran->firstItem() + $loop->index }}</td>
                <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                <td class="fw-semibold">{{ $item->kategoriPengeluaran->nama_kategori ?? '-' }}</td>
                <td class="text-end fw-semibold text-danger">
                  Rp {{ number_format($item->nominal, 0, ',', '.') }}
                </td>
                <td class="text-muted" style="font-size:13px;">{{ $item->keterangan ?: '-' }}</td>
                <td class="small">{{ $item->user->name ?? '-' }}</td>
                <td class="text-center">
                  <a href="{{ route('pengeluaran.edit', $item) }}"
                     class="btn-ta btn-ta-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                  {{ $periode !== 'semua' || request('keyword') ? 'Tidak ada data ditemukan' : 'Belum ada pengeluaran tercatat' }}
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
          {{ $pengeluaran->links() }}
        </div>

      </div>
    </div>
  </div>
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

document.addEventListener('DOMContentLoaded', function() {
  setPeriode(document.getElementById('inputPeriode').value || 'semua');
});
</script>
@endsection
