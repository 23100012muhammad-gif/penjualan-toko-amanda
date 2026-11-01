@extends('layouts.app')

@section('title', 'Dashboard | Toko Amanda')

@php $bodyClass = 'nav-md page-index4'; @endphp

@section('content')
<h1 class="visually-hidden">Dashboard Toko Amanda</h1>

{{-- ═══════════════════════════════════════════════════════
     BARIS 1 — KARTU STATISTIK (5 kartu)
═══════════════════════════════════════════════════════ --}}
<style>
/* ── Dash info card ───────────────────────────────────────────────── */
.dash-card {
  border-radius: 10px;
  transition: transform .2s ease, box-shadow .2s ease;
  overflow: hidden;
}
.dash-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,.15) !important;
}
.dash-card .card-body {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  min-width: 0;
}
.dash-icon {
  flex-shrink: 0;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
}
.dash-info {
  flex: 1;
  min-width: 0;
}
.dash-info .dash-label {
  font-size: 10.5px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
}
.dash-info .dash-value {
  font-size: 1rem;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.25;
}
.dash-info .dash-sub {
  font-size: 10.5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 1px;
}
/* Warna per card — border seragam semua sisi, tidak ada aksen sisi kiri */
.dash-card-blue  { background: #e8f0fe; border: 1px solid #a8c4f8; }
.dash-card-blue  .dash-icon { background: #c2d6fd; color: #1a73e8; }
.dash-card-blue  .dash-label, .dash-card-blue  .dash-sub { color: #5a7bbf; }
.dash-card-blue  .dash-value { color: #1a56c4; }

.dash-card-green { background: #e6f9ee; border: 1px solid #9adbb8; }
.dash-card-green .dash-icon { background: #b7ebc8; color: #1e8c45; }
.dash-card-green .dash-label, .dash-card-green .dash-sub { color: #4a9065; }
.dash-card-green .dash-value { color: #166534; }

.dash-card-red   { background: #fde8e8; border: 1px solid #f5a5a5; }
.dash-card-red   .dash-icon { background: #fbb9b9; color: #e53935; }
.dash-card-red   .dash-label, .dash-card-red   .dash-sub { color: #b05a5a; }
.dash-card-red   .dash-value { color: #b91c1c; }

.dash-card-teal  { background: #e0f5f3; border: 1px solid #80d0c9; }
.dash-card-teal  .dash-icon { background: #b2e4df; color: #00897b; }
.dash-card-teal  .dash-label, .dash-card-teal  .dash-sub { color: #3a8a83; }
.dash-card-teal  .dash-value { color: #00695c; }

.dash-card-amber { background: #fff8e1; border: 1px solid #f5d07a; }
.dash-card-amber .dash-icon { background: #fde68a; color: #b45309; }
.dash-card-amber .dash-label, .dash-card-amber .dash-sub { color: #926920; }
.dash-card-amber .dash-value { color: #92400e; }

/* Nilai Persediaan — ungu, beda dari biru omzet */
.dash-card-violet { background: #f3e8ff; border: 1px solid #d8b4fe; }
.dash-card-violet .dash-icon { background: #e9d5ff; color: #7c3aed; }
.dash-card-violet .dash-label, .dash-card-violet .dash-sub { color: #7e5a9a; }
.dash-card-violet .dash-value { color: #5b21b6; }

/* Card grafik & tabel — border agar tidak menyatu dengan background */
.card.border-0 {
  border: 1px solid #d8e2ef !important;
  box-shadow: 0 2px 8px rgba(0,0,0,.07) !important;
}
.card.border-0:hover {
  box-shadow: 0 4px 14px rgba(0,0,0,.11) !important;
}
</style>

{{-- Baris atas: 4 card sejajar --}}
<div class="row g-3 mb-3">

  {{-- 1. Omzet Penjualan --}}
  <div class="col-xl-3 col-md-6">
    <div class="card dash-card dash-card-blue shadow-sm">
      <div class="card-body">
        <div class="dash-icon"><i class="fas fa-cash-register"></i></div>
        <div class="dash-info">
          <div class="dash-label">Omzet Penjualan</div>
          <div class="dash-value">Rp {{ number_format($omzet, 0, ',', '.') }}</div>
          <div class="dash-sub">Seluruh periode</div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2. Laba Bersih — hijau/merah --}}
  <div class="col-xl-3 col-md-6">
    @php $labaPositif = $labaBersih >= 0; @endphp
    <div class="card dash-card {{ $labaPositif ? 'dash-card-green' : 'dash-card-red' }} shadow-sm">
      <div class="card-body">
        <div class="dash-icon"><i class="fas fa-chart-line"></i></div>
        <div class="dash-info">
          <div class="dash-label">Laba Bersih</div>
          <div class="dash-value">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
          <div class="dash-sub">Omzet &minus; HPP &minus; Pengeluaran</div>
        </div>
      </div>
    </div>
  </div>

  {{-- 3. Total Transaksi --}}
  <div class="col-xl-3 col-md-6">
    <div class="card dash-card dash-card-teal shadow-sm">
      <div class="card-body">
        <div class="dash-icon"><i class="fas fa-receipt"></i></div>
        <div class="dash-info">
          <div class="dash-label">Total Transaksi</div>
          <div class="dash-value">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
          <div class="dash-sub">Transaksi tercatat</div>
        </div>
      </div>
    </div>
  </div>

  {{-- 4. Stok Menipis --}}
  <div class="col-xl-3 col-md-6">
    <div class="card dash-card dash-card-amber shadow-sm">
      <div class="card-body">
        <div class="dash-icon"><i class="fas fa-box-open"></i></div>
        <div class="dash-info">
          <div class="dash-label">Stok Menipis</div>
          <div class="dash-value">{{ $stokMenipis }} barang</div>
          <div class="dash-sub">Stok &le; 10 pcs</div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Baris bawah: card ke-5 di tengah --}}
<div class="row g-3 mb-4 justify-content-center">
  <div class="col-xl-3 col-md-5 col-sm-8">
    <div class="card dash-card dash-card-violet shadow-sm">
      <div class="card-body">
        <div class="dash-icon"><i class="fas fa-warehouse"></i></div>
        <div class="dash-info">
          <div class="dash-label">Nilai Persediaan</div>
          <div class="dash-value">Rp {{ number_format($nilaiPersediaan, 0, ',', '.') }}</div>
          <div class="dash-sub">Stok &times; harga beli</div>
        </div>
      </div>
    </div>
  </div>
</div>
{{-- /BARIS 1 --}}

{{-- ═══════════════════════════════════════════════════════
     BARIS 2 — GRAFIK
═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

  {{-- Grafik Penjualan Bulanan (Bar) --}}
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <h6 class="fw-bold mb-0">
          <i class="fas fa-chart-bar text-primary me-2"></i>
          Penjualan Bulanan
          <small class="text-muted fw-normal">{{ now()->year }}</small>
        </h6>
      </div>
      <div class="card-body">
        <canvas id="chartPenjualan" height="110"></canvas>
      </div>
    </div>
  </div>

  {{-- Grafik Laba Bersih Bulanan (Line) --}}
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <h6 class="fw-bold mb-0">
          <i class="fas fa-chart-line text-success me-2"></i>
          Laba Bersih Bulanan
          <small class="text-muted fw-normal">{{ now()->year }}</small>
        </h6>
      </div>
      <div class="card-body">
        <canvas id="chartLaba" height="200"></canvas>
      </div>
    </div>
  </div>

</div>
{{-- /BARIS 2 --}}

{{-- ═══════════════════════════════════════════════════════
     BARIS 3 — BARANG TERLARIS & STOK MENIPIS
═══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

  {{-- Barang Terlaris (SUM jumlah dari detail_penjualan, satuan dari barang.satuan_jual) --}}
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <h6 class="fw-bold mb-0">
          <i class="fas fa-fire text-danger me-2"></i>Barang Terlaris
        </h6>
      </div>
      <div class="card-body p-0">
        @if($barangTerlaris->isEmpty())
          <div class="text-center text-muted py-4">Belum ada data penjualan.</div>
        @else
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3 text-center" style="width:40px">#</th>
                <th>Nama Barang</th>
                <th class="text-end pe-3">Terjual</th>
              </tr>
            </thead>
            <tbody>
              @foreach($barangTerlaris as $i => $item)
              <tr>
                <td class="ps-3 text-center">
                  @if($i === 0)
                    <span class="badge bg-warning text-dark">1</span>
                  @elseif($i === 1)
                    <span class="badge bg-secondary">2</span>
                  @elseif($i === 2)
                    <span class="badge text-white" style="background:#cd7f32">3</span>
                  @else
                    <span class="text-muted small">{{ $i + 1 }}</span>
                  @endif
                </td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-end pe-3 fw-bold text-primary">
                  {{ number_format($item->total_terjual, 0, ',', '.') }}
                  {{ $item->satuan_jual ?? 'Pcs' }}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>

  {{-- Stok Menipis — badge 3-tier, satuan dari barang.satuan_jual --}}
  <div class="col-md-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">
          <i class="fas fa-exclamation-triangle text-warning me-2"></i>Stok Menipis
        </h6>
        @if(!Auth::user()->isPemilik())
        <a href="{{ route('pembelian.create') }}" class="btn-ta btn-ta-blue">
          <i class="fas fa-plus"></i>Tambah Pembelian
        </a>
        @endif
      </div>
      <div class="card-body p-0">
        @if($daftarStokMenipis->isEmpty())
          <div class="text-center text-muted py-4">
            <i class="fas fa-check-circle text-success fs-4 mb-2 d-block"></i>
            Semua stok barang aman.
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">Nama Barang</th>
                  <th class="text-center">Stok</th>
                  <th class="text-center pe-3">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($daftarStokMenipis as $b)
                <tr>
                  <td class="ps-3">{{ $b->nama_barang }}</td>
                  <td class="text-center fw-bold">
                    {{ $b->stok }}
                    <span class="text-muted small">{{ $b->satuan_jual ?? 'Pcs' }}</span>
                  </td>
                  <td class="text-center pe-3">
                    @if($b->stok == 0)
                      <span class="badge bg-danger">Habis</span>
                    @elseif($b->stok <= 5)
                      <span class="badge text-white" style="background:#c0392b">Kritis</span>
                    @else
                      <span class="badge bg-warning text-dark">Menipis</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>

</div>
{{-- /BARIS 3 --}}

{{-- ═══════════════════════════════════════════════════════
     BARIS 4 — TRANSAKSI TERBARU
═══════════════════════════════════════════════════════ --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">
          <i class="fas fa-list-alt text-info me-2"></i>Transaksi Penjualan Terbaru
        </h6>
        <a href="{{ route('penjualan.histori') }}" class="btn btn-sm btn-outline-primary">
          Lihat Semua
        </a>
      </div>
      <div class="card-body p-0">
        @if($transaksiTerbaru->isEmpty())
          <div class="text-center text-muted py-4">Belum ada transaksi penjualan.</div>
        @else
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">No. Faktur</th>
                  <th>Tanggal</th>
                  <th>Kasir</th>
                  <th class="text-end">Total Penjualan</th>
                  <th class="text-center pe-3">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($transaksiTerbaru as $trx)
                <tr>
                  <td class="ps-3 fw-bold text-primary">{{ $trx->kode_transaksi }}</td>
                  <td>{{ $trx->tanggal_transaksi?->format('d/m/Y') ?? '-' }}</td>
                  <td>{{ $trx->user?->name ?? '-' }}</td>
                  <td class="text-end fw-bold">
                    Rp {{ number_format($trx->total, 0, ',', '.') }}
                  </td>
                  <td class="text-center pe-3">
                    @if($trx->status === 'selesai')
                      <span class="badge bg-success">Selesai</span>
                    @elseif($trx->status === 'pending')
                      <span class="badge bg-warning text-dark">Pending</span>
                    @else
                      <span class="badge bg-secondary">{{ ucfirst($trx->status ?? '-') }}</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
{{-- /BARIS 4 --}}

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const labelBulan    = @json($labelBulan);
  const dataPenjualan = @json($dataPenjualan);
  const dataLaba      = @json($dataLaba);

  // Helper: format Rupiah penuh (tanpa "k"), e.g. 1200000 → "Rp 1.200.000"
  function rupiah(val) {
    return 'Rp ' + Math.abs(val).toLocaleString('id-ID', { maximumFractionDigits: 0 });
  }

  // ── Grafik Penjualan Bulanan (Bar) ───────────────────────────────────
  new Chart(document.getElementById('chartPenjualan'), {
    type: 'bar',
    data: {
      labels: labelBulan,
      datasets: [{
        label: 'Omzet (Rp)',
        data: dataPenjualan,
        backgroundColor: 'rgba(13,110,253,0.75)',
        borderColor: 'rgba(13,110,253,1)',
        borderWidth: 1,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => rupiah(ctx.parsed.y)
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            // Poin 2: format penuh tanpa "k"
            callback: val => rupiah(val)
          }
        }
      }
    }
  });

  // ── Grafik Laba Bersih Bulanan (Line) ────────────────────────────────
  // Poin 5: titik & segment warna sesuai positif/negatif
  const ptColors = dataLaba.map(v => v >= 0 ? 'rgba(25,135,84,1)' : 'rgba(220,53,69,1)');
  const ptBgColors = dataLaba.map(v => v >= 0 ? 'rgba(25,135,84,0.9)' : 'rgba(220,53,69,0.9)');

  // Segment plugin: warna garis per segmen
  const segmentColor = {
    borderColor: ctx => {
      const v = ctx.p1.parsed.y;
      return v >= 0 ? 'rgba(25,135,84,1)' : 'rgba(220,53,69,1)';
    },
    backgroundColor: ctx => {
      const v = ctx.p1.parsed.y;
      return v >= 0 ? 'rgba(25,135,84,0.12)' : 'rgba(220,53,69,0.12)';
    }
  };

  new Chart(document.getElementById('chartLaba'), {
    type: 'line',
    data: {
      labels: labelBulan,
      datasets: [{
        label: 'Laba Bersih (Rp)',
        data: dataLaba,
        fill: true,
        segment: segmentColor,
        borderColor: 'rgba(25,135,84,1)',
        borderWidth: 2,
        pointBackgroundColor: ptBgColors,
        pointBorderColor: ptColors,
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: 0.3,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => {
              const v = ctx.parsed.y;
              const prefix = v < 0 ? '- ' : '';
              return prefix + rupiah(v);
            }
          }
        }
      },
      scales: {
        y: {
          ticks: {
            // Poin 2: format penuh tanpa "k"
            callback: val => {
              const prefix = val < 0 ? '- ' : '';
              return prefix + rupiah(val);
            }
          }
        }
      }
    }
  });

});
</script>
@endsection
