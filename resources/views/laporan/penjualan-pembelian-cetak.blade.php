<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laporan Penjualan–Pembelian | Toko Amanda</title>
  <style>
    * { box-sizing:border-box;margin:0;padding:0; }
    body { font-family:Arial,sans-serif;font-size:12px;color:#000;background:#fff;padding:20px; }
    .header { text-align:center;margin-bottom:16px;border-bottom:2px solid #2a3f54;padding-bottom:10px; }
    .header h2 { font-size:16px;font-weight:bold;text-transform:uppercase;letter-spacing:1px; }
    .header h3 { font-size:13px;font-weight:normal;margin-top:2px; }
    .header .periode { font-size:12px;margin-top:4px;color:#333; }
    .summary { display:flex;gap:24px;margin-bottom:12px;font-size:12px;flex-wrap:wrap; }
    .summary div { border:1px solid #ccc;border-radius:4px;padding:4px 10px; }
    .summary div span { font-weight:bold; }
    table { width:100%;border-collapse:collapse;font-size:11.5px; }
    thead tr { background:#2a3f54;color:#fff; }
    thead th { padding:6px 8px;text-align:left;white-space:nowrap; }
    thead th.text-end { text-align:right; }
    tbody tr:nth-child(even) { background:#f5f5f5; }
    tbody td { padding:5px 8px;border-bottom:1px solid #e0e0e0; }
    tbody td.text-end { text-align:right; }
    tr.kat-row td { background:#e8edf2;font-weight:bold;font-size:11px;color:#2a3f54;padding:4px 8px;border-bottom:1px solid #c5cdd6; }
    tfoot tr { background:#eef3fb;font-weight:bold; }
    tfoot td { padding:6px 8px;border-top:2px solid #2a3f54; }
    tfoot td.text-end { text-align:right; }
    .footer { margin-top:16px;font-size:11px;color:#555;border-top:1px solid #ccc;padding-top:8px;display:flex;justify-content:space-between; }
    @media print {
      body { padding:10px; }
      .no-print { display:none !important; }
      @page { size:landscape;margin:15mm; }
    }
  </style>
</head>
<body>

  <div class="no-print" style="margin-bottom:12px;">
    <button onclick="window.print()" style="padding:6px 16px;background:#2a3f54;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;">
      🖨️ Cetak / Simpan PDF
    </button>
    <a href="{{ route('laporan.penjualan-pembelian') }}?{{ http_build_query(request()->query()) }}"
       style="margin-left:8px;padding:6px 16px;background:#5f6368;color:#fff;border-radius:4px;text-decoration:none;font-size:12px;">
      ← Kembali
    </a>
  </div>

  <div class="header">
    <h2>Toko Amanda</h2>
    <h3>Laporan Penjualan–Pembelian</h3>
    <div class="periode">Periode: <strong>{{ $labelPeriode }}</strong></div>
  </div>

  <div class="summary">
    <div>Total Barang: <span>{{ number_format($totalBarang, 0, ',', '.') }}</span></div>
    <div>Total Penjualan: <span>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</span></div>
    <div>Total Pembelian: <span>Rp {{ number_format($totalPembelian, 0, ',', '.') }}</span></div>
  </div>

  <table>
    <thead>
      <tr>
        <th width="30">No</th>
        <th width="90">Kode Barang</th>
        <th>Nama Barang</th>
        <th class="text-end" width="150">Total Penjualan</th>
        <th class="text-end" width="150">Total Pembelian</th>
      </tr>
    </thead>
    <tbody>
      @php $prevKat = null; $no = 1; @endphp
      @forelse ($rows as $row)
        @if($row['nama_kategori'] !== $prevKat)
          @php $prevKat = $row['nama_kategori']; @endphp
          <tr class="kat-row">
            <td colspan="5">🏷 {{ $prevKat }}</td>
          </tr>
        @endif
        <tr>
          <td>{{ $no++ }}</td>
          <td><strong>{{ $row['kode_barang'] }}</strong></td>
          <td>{{ $row['nama_barang'] }}</td>
          <td class="text-end">Rp {{ number_format($row['total_penjualan'], 0, ',', '.') }}</td>
          <td class="text-end">Rp {{ number_format($row['total_pembelian'], 0, ',', '.') }}</td>
        </tr>
      @empty
      <tr>
        <td colspan="5" style="text-align:center;padding:20px;color:#888;">Tidak ada data</td>
      </tr>
      @endforelse
    </tbody>
    @if(count($rows) > 0)
    <tfoot>
      <tr>
        <td colspan="3" style="text-align:right;">Total</td>
        <td class="text-end">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
        <td class="text-end">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
    @endif
  </table>

  <div class="footer">
    <span>Sistem Penjualan Toko Amanda</span>
    <span>Dicetak: {{ \Carbon\Carbon::now()->format('d-m-Y H:i') }}</span>
  </div>

</body>
</html>
