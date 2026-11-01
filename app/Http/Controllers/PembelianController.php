<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\StokLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PembelianController extends Controller
{
    private function generateKodePembelian(): string
    {
        $last = Pembelian::where('kode_pembelian', 'like', 'PB%')
                         ->orderBy('kode_pembelian', 'desc')
                         ->value('kode_pembelian');

        $next = $last ? ((int) substr($last, 2)) + 1 : 1;

        return 'PB' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function histori(Request $request)
    {
        $periode = $request->input('periode', 'semua');
        $bulan   = $request->input('bulan')  ? (int) $request->input('bulan')  : null;
        $tahun   = $request->input('tahun')  ? (int) $request->input('tahun')  : null;
        $dari    = null;
        $sampai  = null;

        if ($periode === 'hari') {
            $dari   = Carbon::today()->toDateString();
            $sampai = Carbon::today()->toDateString();
        } elseif ($periode === 'bulan') {
            if (!$tahun) $tahun = (int) Carbon::now()->format('Y');
            if (!$bulan) $bulan = (int) Carbon::now()->format('n');
            $dari   = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'tahun') {
            if (!$tahun) $tahun = (int) Carbon::now()->format('Y');
            $dari   = "{$tahun}-01-01";
            $sampai = "{$tahun}-12-31";
        } elseif ($periode === 'custom') {
            $dari   = $request->tanggal_dari  ?: null;
            $sampai = $request->tanggal_sampai ?: ($dari ? $dari : null);
        }

        // Label periode untuk info aktif
        $namaBulan    = $bulan ? Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') : '';
        $labelPeriode = match ($periode) {
            'hari'   => 'Hari Ini (' . Carbon::today()->format('d-m-Y') . ')',
            'bulan'  => ($namaBulan ? $namaBulan . ' ' : '') . ($tahun ?? Carbon::now()->year),
            'tahun'  => 'Tahun ' . ($tahun ?? Carbon::now()->year),
            'custom' => $dari
                ? Carbon::parse($dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($sampai ?? $dari)->format('d-m-Y')
                : 'Custom',
            default  => 'Semua Data',
        };

        $baseQuery = function ($q) use ($dari, $sampai, $request) {
            $q->when($request->keyword, fn($q) =>
                $q->where('kode_pembelian', 'like', '%' . $request->keyword . '%')
            )
            ->when($dari,   fn($q) => $q->where('tanggal', '>=', $dari))
            ->when($sampai, fn($q) => $q->where('tanggal', '<=', $sampai));
        };

        $pembelian = Pembelian::with('detailPembelian.barang')
                              ->tap($baseQuery)
                              ->orderBy('tanggal', 'desc')
                              ->orderBy('id', 'desc')
                              ->paginate(10)
                              ->appends($request->query());

        // Summary — hitung dari seluruh hasil filter (bukan hanya halaman aktif)
        $summaryIds       = Pembelian::tap($baseQuery)->pluck('id');
        $totalTransaksi   = $summaryIds->count();
        $totalPengeluaran = Pembelian::whereIn('id', $summaryIds)->sum('total');
        $totalItem        = DetailPembelian::whereIn('pembelian_id', $summaryIds)->sum('jumlah');

        // Autocomplete — kode pembelian yang ada di riwayat
        $daftarKodePembelian = Pembelian::select('kode_pembelian', 'tanggal')
                                ->orderBy('kode_pembelian', 'desc')
                                ->get();

        $tahunList = collect(range((int) date('Y'), 2020));

        return view('transaksi.pembelian.histori', compact(
            'pembelian', 'totalTransaksi', 'totalPengeluaran', 'totalItem', 'daftarKodePembelian',
            'periode', 'bulan', 'tahun', 'labelPeriode', 'tahunList'
        ));
    }

    public function cetak(Pembelian $pembelian)
    {
        $pembelian->load('detailPembelian.barang');

        return view('transaksi.pembelian.cetak', compact('pembelian'));
    }

    public function create()
    {
        $barang = Barang::with('kategori')
                        ->where('aktif', true)
                        ->orderBy('nama_barang')
                        ->get();

        return view('transaksi.pembelian.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'           => ['required', 'date'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.barang_id' => ['required', 'exists:barang,id'],
            'items.*.jumlah'    => ['required', 'integer', 'min:1'],
        ], [
            'tanggal.required'           => 'Tanggal pembelian wajib diisi.',
            'items.required'             => 'Keranjang masih kosong.',
            'items.*.barang_id.required' => 'Barang tidak valid.',
            'items.*.jumlah.min'         => 'Jumlah minimal 1.',
        ]);

        DB::transaction(function () use ($request) {
            $items = $request->input('items');
            $total = 0;

            foreach ($items as $item) {
                $barang    = Barang::findOrFail($item['barang_id']);
                $isiSatuan = max((int) ($barang->isi_per_satuan_beli ?? 1), 1);
                $hargaPack = (float) (($barang->harga_satuan_beli ?? 0) * $isiSatuan);
                $total    += $hargaPack * $item['jumlah'];
            }

            $pembelian = Pembelian::create([
                'kode_pembelian' => $this->generateKodePembelian(),
                'tanggal'        => $request->input('tanggal'),
                'total'          => $total,
                'catatan'        => $request->input('catatan'),
                'user_id'        => Auth::id(),
            ]);

             foreach ($items as $item) {
                $barang      = Barang::findOrFail($item['barang_id']);
                $isiSatuan   = max((int) ($barang->isi_per_satuan_beli ?? 1), 1);
                $hargaPack   = (float) (($barang->harga_satuan_beli ?? 0) * $isiSatuan);
                $subtotal    = $hargaPack * $item['jumlah'];
                // Stok bertambah dalam satuan jual: qty_pack × isi_per_satuan
                $tambahStok  = $item['jumlah'] * $isiSatuan;

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id'    => $barang->id,
                    'harga'        => $hargaPack,
                    'jumlah'       => $item['jumlah'],
                    'subtotal'     => $subtotal,
                ]);

                $stokSebelum = $barang->stok;
                $stokSesudah = $barang->stok + $tambahStok;

                $barang->increment('stok', $tambahStok);

                StokLog::create([
                    'barang_id'    => $barang->id,
                    'jenis'        => 'masuk',
                    'jumlah'       => $tambahStok,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan'   => 'Restock ' . $pembelian->kode_pembelian .
                                      ' (' . $item['jumlah'] . ' ' . strtoupper($barang->satuan_beli) .
                                      ' × ' . $isiSatuan . ' = ' . $tambahStok . ' ' . strtoupper($barang->satuan_jual) . ')',
                    'penjualan_id' => null,
                    'user_id'      => Auth::id(),
                ]);
            }
        });

        return redirect()->route('pembelian.create')
                         ->with('success', 'Restock berhasil disimpan.');
    }
}
