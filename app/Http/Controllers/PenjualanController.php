<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\StokLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    private function generateKodeTransaksi(): string
    {
        $last = Penjualan::where('kode_transaksi', 'like', 'TRX%')
                         ->orderBy('kode_transaksi', 'desc')
                         ->value('kode_transaksi');

        $next = $last ? ((int) substr($last, 3)) + 1 : 1;

        return 'TRX' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function histori(Request $request)
    {
        $periode = $request->input('periode', 'semua');
        $bulan   = $request->input('bulan')   ? (int) $request->input('bulan')  : null;
        $tahun   = $request->input('tahun')   ? (int) $request->input('tahun')  : null;
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
            $q->when($request->keyword, function ($q) use ($request) {
                $q->where('kode_transaksi', 'like', '%' . $request->keyword . '%')
                  ->orWhereHas('detailPenjualan.barang', fn($q2) =>
                      $q2->where('nama_barang', 'like', '%' . $request->keyword . '%')
                  );
            })
            ->when($dari,   fn($q) => $q->where('tanggal_transaksi', '>=', $dari))
            ->when($sampai, fn($q) => $q->where('tanggal_transaksi', '<=', $sampai));
        };

        $penjualan = Penjualan::with('detailPenjualan.barang')
                              ->tap($baseQuery)
                              ->orderBy('tanggal_transaksi', 'desc')
                              ->orderBy('id', 'desc')
                              ->paginate(10)
                              ->appends($request->query());

        // Summary — hitung dari seluruh hasil filter (bukan hanya halaman aktif)
        $summaryIds      = Penjualan::tap($baseQuery)->pluck('id');
        $totalTransaksi  = $summaryIds->count();
        $totalPendapatan = Penjualan::whereIn('id', $summaryIds)->sum('total');
        $totalItem       = DetailPenjualan::whereIn('penjualan_id', $summaryIds)->sum('jumlah');

        // Autocomplete — kode transaksi yang ada di riwayat
        $daftarBarangHistori = Penjualan::select('kode_transaksi', 'tanggal_transaksi')
                                ->orderBy('kode_transaksi', 'desc')
                                ->get();

        $tahunList = collect(range((int) date('Y'), 2020));

        return view('transaksi.penjualan.histori', compact(
            'penjualan', 'totalTransaksi', 'totalPendapatan', 'totalItem', 'daftarBarangHistori',
            'periode', 'bulan', 'tahun', 'labelPeriode', 'tahunList'
        ));
    }

    public function cetak(Penjualan $penjualan)
    {
        $penjualan->load('detailPenjualan.barang');

        return view('transaksi.penjualan.cetak', compact('penjualan'));
    }

    public function create()
    {
        $barang = Barang::with('kategori')
                        ->where('aktif', true)
                        ->where('stok', '>', 0)
                        ->orderBy('nama_barang')
                        ->get();

        return view('transaksi.penjualan.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_transaksi'  => ['required', 'date'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.barang_id'  => ['required', 'exists:barang,id'],
            'items.*.jumlah'     => ['required', 'integer', 'min:1'],
        ], [
            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date'     => 'Format tanggal tidak valid.',
            'items.required'             => 'Keranjang masih kosong. Tambahkan barang terlebih dahulu.',
            'items.*.barang_id.required' => 'Barang tidak valid.',
            'items.*.jumlah.min'         => 'Jumlah minimal 1.',
        ]);

        $items  = $request->input('items');
        $errors = [];

        foreach ($items as $item) {
            $barang = Barang::find($item['barang_id']);
            if (!$barang) {
                $errors[] = "Barang tidak ditemukan.";
                continue;
            }
            if ((int) $item['jumlah'] <= 0) {
                $errors[] = "{$barang->nama_barang}: jumlah harus lebih dari 0.";
                continue;
            }
            if ((int) $item['jumlah'] > $barang->stok) {
                $errors[] = "Stok {$barang->nama_barang} tidak cukup. Tersedia: {$barang->stok} {$barang->satuan_jual}.";
            }
        }

        if (!empty($errors)) {
            return back()->withInput()->with('error_stok', $errors);
        }

        DB::transaction(function () use ($items, $request) {

            $total = 0;
            foreach ($items as $item) {
                $barang = Barang::find($item['barang_id']);
                $total += $barang->harga_jual * $item['jumlah'];
            }

            $tanggalTrx = $request->input('tanggal_transaksi', now()->toDateString());

            $penjualan = Penjualan::create([
                'kode_transaksi'    => $this->generateKodeTransaksi(),
                'tanggal_transaksi' => $tanggalTrx,
                'total'             => $total,
                'status'            => 'selesai',
                'catatan'           => $request->input('catatan'),
                'user_id'           => Auth::id(),
            ]);

            foreach ($items as $item) {
                $barang   = Barang::find($item['barang_id']);
                $subtotal = $barang->harga_jual * $item['jumlah'];

                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id'    => $barang->id,
                    'harga'        => $barang->harga_jual,
                    'jumlah'       => $item['jumlah'],
                    'subtotal'     => $subtotal,
                ]);

                $stokSebelum = $barang->stok;
                $stokSesudah = $barang->stok - $item['jumlah'];

                $barang->decrement('stok', $item['jumlah']);

                StokLog::create([
                    'barang_id'    => $barang->id,
                    'jenis'        => 'keluar',
                    'jumlah'       => $item['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'keterangan'   => 'Penjualan ' . $penjualan->kode_transaksi,
                    'penjualan_id' => $penjualan->id,
                    'user_id'      => Auth::id(),
                ]);
            }
        });

        return redirect()->route('penjualan.create')
                         ->with('success', 'Transaksi berhasil disimpan.');
    }
}
