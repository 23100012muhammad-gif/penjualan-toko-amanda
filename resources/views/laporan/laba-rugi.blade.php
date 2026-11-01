@extends('layouts.app')

@section('title', 'Laporan Laba Rugi | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-graph-up-arrow me-2"></i>Laporan Laba Rugi
    </h3>
    <small class="text-muted">Laporan &rsaquo; Laba Rugi</small>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-calculator me-2"></i>Rekap Laba Rugi</h4>
        <div class="ms-auto d-flex gap-1 align-items-center">
          <a href="{{ route('laporan.laba-rugi.cetak') }}?{{ http_build_query(request()->query()) }}"
             target="_blank" class="btn-ta btn-ta-dark" title="Cetak / Simpan PDF">
            <i class="bi bi-printer me-1"></i>Cetak Laporan
          </a>
          <a href="{{ route('laporan.laba-rugi.export') }}?{{ http_build_query(request()->query()) }}"
             class="btn-ta btn-ta-excel" title="Export Excel">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
          </a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        @php
          $mapBulanSingkat = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
          $labelBulanAwal  = ($periode==='bulan' && $bulan) ? ($mapBulanSingkat[$bulan] ?? 'Bulan') : 'Bulan';
          $labelTahunAwal  = ($periode==='tahun' && $tahun) ? $tahun : 'Tahun';
        @endphp

        <form method="GET" action="{{ route('laporan.laba-rugi') }}" id="formLabaRugi">
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

          <div style="margin-bottom:14px;">
            <button type="submit" class="btn-ta btn-ta-dark">
              <i class="fas fa-filter me-1"></i>Filter
            </button>
            @if($periode !== 'semua')
            <a href="{{ route('laporan.laba-rugi') }}" class="btn-ta btn-ta-gray ms-1">
              <i class="fas fa-times me-1"></i>Reset
            </a>
            @endif
          </div>
        </form>

        <div class="mb-3" style="font-size:12px;color:#6c757d;">
          Periode aktif: <strong class="text-dark">{{ $labelPeriode }}</strong>
        </div>

        {{-- RINGKASAN UTAMA --}}
        <div class="row justify-content-center mb-4">
          <div class="col-md-10 col-lg-8">
            <h5 class="fw-bold mb-3 tx-heading">
              <i class="bi bi-speedometer2 me-2"></i>Ringkasan Utama
            </h5>
            <p class="text-muted small mb-3">Ringkasan hasil usaha pada periode yang dipilih.</p>

            <div class="row g-3">
              <div class="col-sm-6">
                <div class="p-3 rounded border lp-sum-card" style="background:#f0fff4;border-color:#b7ebc8 !important;">
                  <div class="small text-muted mb-1 d-flex align-items-center gap-2">
                    <span>Omzet Penjualan</span>
                    <i class="bi bi-question-circle lp-tooltip-icon text-muted"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="Omzet Penjualan&#10;&#10;Total nilai penjualan barang pada periode yang dipilih."></i>
                  </div>
                  <div class="fw-bold fs-5 text-success">Rp {{ number_format($penjualan, 0, ',', '.') }}</div>
                  <div class="small text-muted mt-1">Total penjualan barang pada periode ini</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-3 rounded border lp-sum-card" style="background:#fffbf0;border-color:#ffe0a0 !important;">
                  <div class="small text-muted mb-1 d-flex align-items-center gap-2">
                    <span>Modal Barang Terjual (HPP)</span>
                    <i class="bi bi-question-circle lp-tooltip-icon text-muted"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="HPP (Harga Pokok Penjualan)&#10;&#10;Total harga beli barang yang berhasil terjual pada periode ini.&#10;&#10;Contoh:&#10;3 Sarimi × Rp1.500&#10;Rp4.500&#10;&#10;Artinya modal yang keluar untuk barang yang terjual adalah Rp4.500."></i>
                  </div>
                  <div class="fw-bold fs-5" style="color:#b45309;">Rp {{ number_format($hpp, 0, ',', '.') }}</div>
                  <div class="small text-muted mt-1">Total harga beli barang yang terjual</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-3 rounded border lp-sum-card" style="background:#f0f4ff;border-color:#d0dcff !important;">
                  <div class="small text-muted mb-1 d-flex align-items-center gap-2">
                    <span>Laba Kotor</span>
                    <i class="bi bi-question-circle lp-tooltip-icon text-muted"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="Laba Kotor&#10;&#10;Keuntungan sebelum dikurangi pengeluaran operasional."></i>
                  </div>
                  <div class="fw-bold fs-5 {{ $labaKotor >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($labaKotor, 0, ',', '.') }}
                  </div>
                  <div class="small text-muted mt-1">Omzet dikurangi modal barang terjual</div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="p-3 rounded border lp-sum-card" style="background:#fff5f5;border-color:#fecaca !important;">
                  <div class="small text-muted mb-1 d-flex align-items-center gap-2">
                    <span>Pengeluaran Operasional</span>
                    <i class="bi bi-question-circle lp-tooltip-icon text-muted"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="Pengeluaran Operasional&#10;&#10;Biaya usaha seperti listrik, internet, BBM, transportasi, dan biaya operasional lainnya."></i>
                  </div>
                  <div class="fw-bold fs-5 text-danger">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</div>
                  <div class="small text-muted mt-1">BBM, listrik, transport, dan biaya operasional lain</div>
                </div>
              </div>
              <div class="col-12">
                <div class="p-4 rounded border text-center lp-sum-card-big"
                     style="background:{{ $labaPositif ? '#f0fff4' : '#fff5f5' }};border-color:{{ $labaPositif ? '#1e8c45' : '#b91c1c' }} !important;border-width:2px !important;"
                     >
                  @if($labaPositif)
                    <div class="small text-success fw-semibold mb-1 d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-check-circle me-1"></i>
                      <span>LABA BERSIH</span>
                      <i class="bi bi-question-circle lp-tooltip-icon text-muted"
                         data-bs-toggle="tooltip"
                         data-bs-placement="top"
                         title="Laba Bersih&#10;&#10;Keuntungan akhir setelah dikurangi seluruh pengeluaran usaha."></i>
                    </div>
                  @else
                    <div class="small text-danger fw-semibold mb-1 d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-x-circle me-1"></i>
                      <span>RUGI</span>
                      <i class="bi bi-question-circle lp-tooltip-icon text-muted"
                         data-bs-toggle="tooltip"
                         data-bs-placement="top"
                         title="Rugi&#10;&#10;Terjadi ketika modal dan pengeluaran lebih besar daripada penjualan."></i>
                    </div>
                  @endif
                  <div class="fw-bold fs-3 {{ $labaPositif ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}
                  </div>
                  <div class="small text-muted mt-1">Laba kotor dikurangi pengeluaran operasional</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- LAPORAN LABA RUGI FORMAT AKUNTANSI --}}
        <div class="row justify-content-center mb-4">
          <div class="col-md-10 col-lg-8">
            <h5 class="fw-bold mb-3 tx-heading">
              <i class="bi bi-journal-bookmark me-2"></i>Laporan Laba Rugi
            </h5>
            <p class="text-muted small mb-3">Tampilan formal laporan laba rugi untuk periode: <strong>{{ $labelPeriode }}</strong></p>

            <div class="lp-akuntansi-wrap">

              {{-- Omzet --}}
              <div class="lp-ak-line">
                <span class="lp-ak-label">Penjualan / Omzet</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($penjualan, 0, ',', '.') }}</span>
              </div>

              {{-- HPP --}}
              <div class="lp-ak-heading lp-ak-heading-result">Harga Pokok Penjualan</div>

              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Persediaan Awal (+)</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</span>
              </div>
              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Pembelian</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($pembelian, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule"></div>

              <div class="lp-ak-line lp-ak-subtotal">
                <span class="lp-ak-label">Barang Siap Dijual</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($barangSiapDijual, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Barang Siap Dijual (&minus;)</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($barangSiapDijual, 0, ',', '.') }}</span>
              </div>
              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Persediaan Akhir</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($persediaanAkhir, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule lp-ak-rule-short"></div>

              <div class="lp-ak-line lp-ak-result">
                <span class="lp-ak-label">HPP</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value lp-ak-hpp">Rp {{ number_format($hpp, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule lp-ak-rule-double"></div>

              {{-- Laba Kotor --}}
              <div class="lp-ak-heading lp-ak-heading-result">Laba Kotor</div>

              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Penjualan / Omzet (&minus;)</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($penjualan, 0, ',', '.') }}</span>
              </div>
              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">HPP</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value lp-ak-hpp">Rp {{ number_format($hpp, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule"></div>

              <div class="lp-ak-line lp-ak-result">
                <span class="lp-ak-label">Laba Kotor</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value {{ $labaKotor >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($labaKotor, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule lp-ak-rule-double"></div>

              {{-- Laba Bersih --}}
              <div class="lp-ak-heading lp-ak-heading-result {{ $labaPositif ? 'text-success' : 'text-danger' }}">{{ $labaPositif ? 'Laba Bersih' : 'Rugi' }}</div>

              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Laba Kotor (&minus;)</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value {{ $labaKotor >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($labaKotor, 0, ',', '.') }}</span>
              </div>
              <div class="lp-ak-line lp-ak-indent">
                <span class="lp-ak-label">Biaya Operasional</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule lp-ak-rule-short"></div>

              <div class="lp-ak-line lp-ak-result">
                <span class="lp-ak-label {{ $labaPositif ? 'text-success' : 'text-danger' }}">{{ $labaPositif ? 'Laba Bersih' : 'Rugi' }}</span>
                <span class="lp-ak-dots" aria-hidden="true"></span>
                <span class="lp-ak-value {{ $labaPositif ? 'text-success' : 'text-danger' }}">Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}</span>
              </div>

              <div class="lp-ak-rule lp-ak-rule-double"></div>

            </div>
          </div>
        </div>

        {{-- CARA PERHITUNGAN --}}
        <div class="row justify-content-center">
          <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm lp-formula-card">
              <div class="card-body p-3 p-md-4">
                <div class="fw-semibold mb-3 lp-formula-title">
                  <i class="bi bi-journal-text me-2"></i>Cara Perhitungan Laba Rugi
                </div>

                <div class="lp-step">
                  <div class="lp-step-heading">
                    <i class="bi bi-box-seam me-2"></i>
                    <span class="fw-bold">[1] Barang Siap Dijual</span>
                  </div>
                  <div class="lp-step-formula">Persediaan Awal + Pembelian</div>
                </div>

                <div class="lp-step-arrow"><i class="bi bi-arrow-down"></i></div>

                <div class="lp-step">
                  <div class="lp-step-heading">
                    <i class="bi bi-cart-check me-2"></i>
                    <span class="fw-bold">[2] HPP</span>
                  </div>
                  <div class="lp-step-formula">Barang Siap Dijual − Persediaan Akhir</div>
                </div>

                <div class="lp-step-arrow"><i class="bi bi-arrow-down"></i></div>

                <div class="lp-step">
                  <div class="lp-step-heading">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    <span class="fw-bold">[3] Laba Kotor</span>
                  </div>
                  <div class="lp-step-formula">Omzet Penjualan − HPP</div>
                </div>

                <div class="lp-step-arrow"><i class="bi bi-arrow-down"></i></div>

                <div class="lp-step mb-0">
                  <div class="lp-step-heading">
                    <i class="bi bi-trophy me-2"></i>
                    <span class="fw-bold">[4] Laba Bersih</span>
                  </div>
                  <div class="lp-step-formula">Laba Kotor − Pengeluaran Operasional</div>
                </div>
              </div>
            </div>
          </div>
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

.lp-sum-card { min-height: 138px; display:flex; flex-direction:column; justify-content:space-between; }
.lp-sum-card-big { min-height: 138px; }
.lp-tooltip-icon { font-size: 12px; cursor: pointer; flex: 0 0 auto; }

.lp-formula-card {
  background: #f8fafc;
  border: 1px solid #e9eef5 !important;
  color: #4b5563;
}
.lp-formula-title {
  color: #334155;
  font-size: 13px;
}
.lp-step {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px 12px;
}
.lp-step + .lp-step { margin-top: 0; }
.lp-step-heading {
  display: flex;
  align-items: center;
  color: #1f2937;
  font-size: 13px;
  margin-bottom: 4px;
}
.lp-step-formula {
  color: #6b7280;
  font-size: 12.5px;
  line-height: 1.4;
}
.lp-step-arrow {
  text-align: center;
  color: #94a3b8;
  font-size: 13px;
  line-height: 1;
  margin: 7px 0;
}

@media (max-width: 576px) {
  .lp-step { padding: 9px 10px; }
  .lp-step-heading { font-size: 12.5px; }
  .lp-step-formula { font-size: 12px; }
}

.lp-akuntansi-wrap {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 18px 20px;
  font-size: 14px;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
  font-family: inherit;
}
.lp-ak-heading {
  font-weight: 700;
  color: #2a3f54;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .8px;
  margin: 16px 0 10px;
  padding-bottom: 4px;
  text-align: center;
}
.lp-ak-heading-result {
  margin-top: 4px;
  border-bottom: none;
  padding-bottom: 0;
}
.lp-ak-line {
  display: flex;
  align-items: baseline;
  gap: 0;
  padding: 4px 0;
  line-height: 1.45;
}
.lp-ak-indent {
  padding-left: 14px;
}
.lp-ak-label {
  flex: 0 1 auto;
  min-width: 0;
  text-align: left;
}
.lp-ak-dots {
  flex: 1 1 auto;
  min-width: 16px;
  margin: 0 6px;
  border-bottom: 1px dotted #b0bac9;
  transform: translateY(-3px);
}
.lp-ak-value {
  flex: 0 0 auto;
  text-align: right;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
  font-weight: 500;
}
.lp-ak-hpp {
  color: #b45309;
}
.lp-ak-subtotal .lp-ak-label,
.lp-ak-subtotal .lp-ak-value {
  font-weight: 700;
}
.lp-ak-result .lp-ak-label,
.lp-ak-result .lp-ak-value {
  font-weight: 700;
  font-size: 15px;
}
.lp-ak-rule {
  border: none;
  border-top: 1px solid #cbd5e1;
  margin: 8px 0;
}
.lp-ak-rule-short {
  max-width: 88%;
  margin-left: auto;
  margin-right: 0;
}
.lp-ak-rule-double {
  border-top: 2px solid #2a3f54;
  margin: 12px 0 4px;
}

@media (max-width: 576px) {
  .lp-akuntansi-wrap { padding: 14px 12px; font-size: 12.5px; }
  .lp-ak-indent { padding-left: 8px; }
  .lp-ak-dots { margin: 0 4px; min-width: 8px; }
  .lp-ak-label { font-size: 12px; }
  .lp-ak-value { font-size: 12px; }
  .lp-ak-result .lp-ak-label,
  .lp-ak-result .lp-ak-value { font-size: 13.5px; }
  .lp-ak-rule-short { max-width: 100%; }
}

.btn-ta-excel {
  background: #1e7e34;
  color: #fff !important;
  border-color: #1e7e34;
}
.btn-ta-excel:hover {
  background: #fff;
  color: #1e7e34 !important;
  border-color: #1e7e34;
}
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

  // Aktivasi tooltip Bootstrap untuk ikon informasi (desktop hover + mobile tap/click)
  if (window.bootstrap && window.bootstrap.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      bootstrap.Tooltip.getOrCreateInstance(el, {
        container: 'body',
        trigger: 'hover focus click',
        boundary: 'window'
      });
    });
  }
});
</script>
@endsection
