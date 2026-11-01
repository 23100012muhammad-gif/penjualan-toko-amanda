<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Laba Rugi | Toko Amanda</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; background: #fff; padding: 20px; }

    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #2a3f54; padding-bottom: 10px; }
    .header h2 { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .header h3 { font-size: 13px; font-weight: normal; margin-top: 2px; }
    .header .meta { font-size: 11px; margin-top: 5px; color: #333; display:flex; justify-content:center; gap:14px; flex-wrap:wrap; }
    .header .meta strong { color:#000; }

    .badgeStatus { display:inline-block; padding: 2px 10px; border-radius: 999px; font-size: 10px; font-weight:700; letter-spacing:.3px; }
    .badgeLaba { background:#e9f9ef; color:#1e8c45; border:1px solid #b7ebc8; }
    .badgeRugi { background:#ffe8e8; color:#b91c1c; border:1px solid #fecaca; }

    /* Ringkasan cards */
    .grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:8px; margin: 12px 0 16px; }
    .card { border: 1px solid #d9d9d9; border-radius: 5px; padding: 8px 10px; background: #fff; }
    .card .label { font-size: 10px; color: #555; margin-bottom: 3px; }
    .card .value { font-size: 13px; font-weight: 700; }
    .card.green  { background:#f0fff4; border-color:#b7ebc8; }
    .card.yellow { background:#fffbf0; border-color:#ffe0a0; }
    .card.blue   { background:#f0f4ff; border-color:#d0dcff; }
    .card.red    { background:#fff5f5; border-color:#fecaca; }
    .value.green  { color:#1e8c45; }
    .value.yellow { color:#b45309; }
    .value.red    { color:#b91c1c; }

    /* Format akuntansi */
    .ak-wrap {
      border: 1px solid #d0d7e3;
      border-radius: 6px;
      padding: 14px 16px;
      font-size: 12px;
      color: #000;
      margin-top: 4px;
    }
    .ak-section-title {
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .7px;
      text-align: center;
      color: #2a3f54;
      margin: 14px 0 8px;
    }
    .ak-section-title:first-child { margin-top: 0; }
    .ak-line {
      display: flex;
      align-items: flex-end;
      padding: 3px 0;
      line-height: 1.4;
    }
    .ak-indent { padding-left: 12px; }
    .ak-label { flex: 0 1 auto; min-width: 0; white-space: nowrap; }
    .ak-dots {
      flex: 1 1 auto;
      min-width: 12px;
      margin: 0 5px;
      border-bottom: 1px dotted #999;
      margin-bottom: 2px;
    }
    .ak-value { flex: 0 0 auto; text-align: right; white-space: nowrap; font-weight: 500; }
    .ak-subtotal .ak-label,
    .ak-subtotal .ak-value { font-weight: 700; }
    .ak-result .ak-label,
    .ak-result .ak-value  { font-weight: 700; font-size: 13px; }
    .ak-hpp   { color: #b45309; }
    .ak-rule  { border: none; border-top: 1px solid #bbb; margin: 6px 0; }
    .ak-rule-short { max-width: 75%; margin-left: auto; margin-right: 0; }
    .ak-rule-double { border-top: 2px solid #2a3f54; margin: 10px 0 2px; }
    .color-success { color: #1e8c45; }
    .color-danger  { color: #b91c1c; }

    .section-label {
      font-size: 11px;
      font-weight: 700;
      color: #2a3f54;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin: 14px 0 6px;
    }

    .footer { margin-top: 16px; font-size: 10px; color: #555; border-top: 1px solid #ccc; padding-top: 8px; display: flex; justify-content: space-between; }

    @media print {
      body { padding: 10px; }
      .no-print { display: none !important; }
      @page { size: portrait; margin: 15mm; }
    }
  </style>
</head>
<body>

  {{-- Tombol cetak --}}
  <div class="no-print" style="margin-bottom:12px;">
    <button onclick="window.print()" style="padding:6px 16px;background:#2a3f54;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;">
      🖨️ Cetak / Simpan PDF
    </button>
    <a href="{{ route('laporan.laba-rugi') }}?{{ http_build_query(request()->query()) }}"
       style="margin-left:8px;padding:6px 16px;background:#5f6368;color:#fff;border-radius:4px;text-decoration:none;font-size:12px;">
      ← Kembali
    </a>
  </div>

  {{-- Header --}}
  <div class="header">
    <h2>Toko Amanda</h2>
    <h3>LAPORAN LABA RUGI</h3>
    <div class="meta">
      <div>Periode: <strong>{{ $labelPeriode }}</strong></div>
      <div>Tanggal Cetak: <strong>{{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</strong></div>
      <div>
        @if($labaPositif)
          <span class="badgeStatus badgeLaba">LABA</span>
        @else
          <span class="badgeStatus badgeRugi">RUGI</span>
        @endif
      </div>
    </div>
  </div>

  {{-- Ringkasan --}}
  <div class="grid">
    <div class="card green">
      <div class="label">Omzet Penjualan</div>
      <div class="value green">Rp {{ number_format($penjualan, 0, ',', '.') }}</div>
    </div>
    <div class="card yellow">
      <div class="label">Modal Barang Terjual (HPP)</div>
      <div class="value yellow">Rp {{ number_format($hpp, 0, ',', '.') }}</div>
    </div>
    <div class="card blue">
      <div class="label">Laba Kotor</div>
      <div class="value {{ $labaKotor >= 0 ? 'green' : 'red' }}">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
    </div>
    <div class="card red">
      <div class="label">Pengeluaran Operasional</div>
      <div class="value red">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</div>
    </div>
    <div class="card {{ $labaPositif ? 'green' : 'red' }}" style="grid-column: 1 / -1;">
      <div class="label">{{ $labaPositif ? 'Laba Bersih' : 'Rugi' }}</div>
      <div class="value {{ $labaPositif ? 'green' : 'red' }}">Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}</div>
    </div>
  </div>

  {{-- Format Akuntansi --}}
  <div class="section-label">Laporan Laba Rugi (Format Akuntansi)</div>

  <div class="ak-wrap">

    {{-- Omzet --}}
    <div class="ak-line">
      <span class="ak-label">Penjualan / Omzet</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($penjualan, 0, ',', '.') }}</span>
    </div>

    {{-- HPP --}}
    <div class="ak-section-title">Harga Pokok Penjualan</div>

    <div class="ak-line ak-indent">
      <span class="ak-label">Persediaan Awal (+)</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</span>
    </div>
    <div class="ak-line ak-indent">
      <span class="ak-label">Pembelian</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($pembelian, 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule"></div>

    <div class="ak-line ak-subtotal">
      <span class="ak-label">Barang Siap Dijual</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($barangSiapDijual, 0, ',', '.') }}</span>
    </div>

    <div class="ak-line ak-indent">
      <span class="ak-label">Barang Siap Dijual (&minus;)</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($barangSiapDijual, 0, ',', '.') }}</span>
    </div>
    <div class="ak-line ak-indent">
      <span class="ak-label">Persediaan Akhir</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($persediaanAkhir, 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule ak-rule-short"></div>

    <div class="ak-line ak-result">
      <span class="ak-label">HPP</span>
      <span class="ak-dots"></span>
      <span class="ak-value ak-hpp">Rp {{ number_format($hpp, 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule ak-rule-double"></div>

    {{-- Laba Kotor --}}
    <div class="ak-section-title">Laba Kotor</div>

    <div class="ak-line ak-indent">
      <span class="ak-label">Penjualan / Omzet (&minus;)</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($penjualan, 0, ',', '.') }}</span>
    </div>
    <div class="ak-line ak-indent">
      <span class="ak-label">HPP</span>
      <span class="ak-dots"></span>
      <span class="ak-value ak-hpp">Rp {{ number_format($hpp, 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule"></div>

    <div class="ak-line ak-result">
      <span class="ak-label">Laba Kotor</span>
      <span class="ak-dots"></span>
      <span class="ak-value {{ $labaKotor >= 0 ? 'color-success' : 'color-danger' }}">Rp {{ number_format($labaKotor, 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule ak-rule-double"></div>

    {{-- Laba Bersih --}}
    <div class="ak-section-title {{ $labaPositif ? 'color-success' : 'color-danger' }}">{{ $labaPositif ? 'Laba Bersih' : 'Rugi' }}</div>

    <div class="ak-line ak-indent">
      <span class="ak-label">Laba Kotor (&minus;)</span>
      <span class="ak-dots"></span>
      <span class="ak-value {{ $labaKotor >= 0 ? 'color-success' : 'color-danger' }}">Rp {{ number_format($labaKotor, 0, ',', '.') }}</span>
    </div>
    <div class="ak-line ak-indent">
      <span class="ak-label">Biaya Operasional</span>
      <span class="ak-dots"></span>
      <span class="ak-value">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule ak-rule-short"></div>

    <div class="ak-line ak-result">
      <span class="ak-label {{ $labaPositif ? 'color-success' : 'color-danger' }}">{{ $labaPositif ? 'Laba Bersih' : 'Rugi' }}</span>
      <span class="ak-dots"></span>
      <span class="ak-value {{ $labaPositif ? 'color-success' : 'color-danger' }}">Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}</span>
    </div>

    <div class="ak-rule ak-rule-double"></div>

  </div>

  {{-- Footer --}}
  <div class="footer">
    <span>Sistem Penjualan Toko Amanda</span>
    <span>Dicetak: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</span>
  </div>

  <script>
    window.addEventListener('load', function () {
      setTimeout(function () { window.print(); }, 250);
    });
  </script>
</body>
</html>
