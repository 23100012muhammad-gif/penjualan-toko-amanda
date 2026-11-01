<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Penjualan {{ $penjualan->kode_transaksi }} | Toko Amanda</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #000; background: #fff; padding: 20px; }

    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #2a3f54; padding-bottom: 10px; }
    .header h2 { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .header h3 { font-size: 13px; font-weight: normal; margin-top: 2px; }
    .header .kode { font-size: 12px; margin-top: 4px; color: #333; }

    .info { display: flex; gap: 24px; margin-bottom: 12px; font-size: 12px; flex-wrap: wrap; }
    .info div { border: 1px solid #ccc; border-radius: 4px; padding: 4px 10px; }
    .info div span { font-weight: bold; }

    table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
    thead tr { background: #2a3f54; color: #fff; }
    thead th { padding: 6px 8px; text-align: left; white-space: nowrap; }
    thead th.text-end { text-align: right; }
    thead th.text-center { text-align: center; }
    tbody tr:nth-child(even) { background: #f5f5f5; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
    tbody td.text-end { text-align: right; }
    tbody td.text-center { text-align: center; }
    tfoot tr { background: #eef3fb; font-weight: bold; }
    tfoot td { padding: 6px 8px; border-top: 2px solid #2a3f54; }
    tfoot td.text-end { text-align: right; }

    .catatan { margin-bottom: 12px; font-size: 12px; }
    .catatan strong { display: block; margin-bottom: 2px; }

    .footer { margin-top: 16px; font-size: 11px; color: #555; border-top: 1px solid #ccc; padding-top: 8px; display: flex; justify-content: space-between; }

    @media print {
      body { padding: 10px; }
      .no-print { display: none !important; }
      @page { size: portrait; margin: 15mm; }
    }
  </style>
</head>
<body>

  <div class="no-print" style="margin-bottom:12px;">
    <button onclick="window.print()" style="padding:6px 16px;background:#2a3f54;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;">
      Cetak / Simpan PDF
    </button>
    <a href="{{ route('penjualan.histori') }}"
       style="margin-left:8px;padding:6px 16px;background:#5f6368;color:#fff;border-radius:4px;text-decoration:none;font-size:12px;">
      Kembali
    </a>
  </div>

  <div class="header">
    <h2>Toko Amanda</h2>
    <h3>Detail Transaksi Penjualan</h3>
    <div class="kode">Kode: <strong>{{ $penjualan->kode_transaksi }}</strong></div>
  </div>

  <div class="info">
    <div>Tanggal: <span>{{ $penjualan->tanggal_transaksi->format('d F Y') }}</span></div>
    <div>Status: <span>{{ $penjualan->status === 'selesai' ? 'Selesai' : 'Batal' }}</span></div>
    <div>Total Item: <span>{{ $penjualan->detailPenjualan->count() }} item</span></div>
  </div>

  @if ($penjualan->catatan)
  <div class="catatan">
    <strong>Catatan</strong>
    {{ $penjualan->catatan }}
  </div>
  @endif

  <table>
    <thead>
      <tr>
        <th width="30">No</th>
        <th>Barang</th>
        <th class="text-center" width="90">Qty</th>
        <th class="text-end" width="120">Harga</th>
        <th class="text-end" width="130">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($penjualan->detailPenjualan as $detail)
      <tr>
        <td class="text-center">{{ $loop->iteration }}</td>
        <td>
          {{ $detail->barang->nama_barang }}
          <br><small style="color:#666;">{{ $detail->barang->kode_barang }}</small>
        </td>
        <td class="text-center">{{ $detail->jumlah }} {{ $detail->barang->satuan_jual }}</td>
        <td class="text-end">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
        <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4" class="text-end">Total</td>
        <td class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    <span>Dicetak: {{ now()->format('d-m-Y H:i') }}</span>
    <span>Toko Amanda — Sistem Informasi Penjualan</span>
  </div>

</body>
</html>
