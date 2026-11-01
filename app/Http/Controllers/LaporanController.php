<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\DetailPembelian;
use App\Models\Barang;
use App\Models\Kategori;
use App\Exports\LaporanPenjualanExport;
use App\Exports\LaporanPembelianExport;
use App\Exports\LaporanPersediaanExport;
use App\Exports\LaporanPenjualanPembelianExport;
use App\Exports\LaporanLabaRugiExport;
use App\Services\LabaRugiService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Bangun closure filter yang dipakai bersama oleh summary, query utama, export, dan cetak.
     */
    private function buildFilter(string $periode, ?string $dari, ?string $sampai, ?string $keyword, ?int $bulan, ?int $tahun, ?int $kategoriId = null): \Closure
    {
        return function ($q) use ($periode, $dari, $sampai, $keyword, $bulan, $tahun, $kategoriId) {
            $q->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
              ->join('barang', 'barang.id', '=', 'detail_penjualan.barang_id')
              ->join('kategori', 'kategori.id', '=', 'barang.kategori_id')
              ->where('penjualan.status', 'selesai')
              ->when($keyword, fn($q) =>
                  $q->where(fn($q2) =>
                      $q2->where('barang.kode_barang', 'like', '%' . $keyword . '%')
                         ->orWhere('barang.nama_barang', 'like', '%' . $keyword . '%')
                  )
              )
              ->when($kategoriId, fn($q) => $q->where('barang.kategori_id', $kategoriId))
              ->when($periode === 'bulan' && $bulan && $tahun,
                  fn($q) => $q->whereMonth('penjualan.tanggal_transaksi', $bulan)->whereYear('penjualan.tanggal_transaksi', $tahun)
              )
              ->when($periode === 'tahun' && $tahun,
                  fn($q) => $q->whereYear('penjualan.tanggal_transaksi', $tahun)
              )
              ->when($dari,   fn($q) => $q->where('penjualan.tanggal_transaksi', '>=', $dari))
              ->when($sampai, fn($q) => $q->where('penjualan.tanggal_transaksi', '<=', $sampai));
        };
    }

    /**
     * Parse semua parameter filter dari request.
     */
    private function parseParams(Request $request): array
    {
        $periode    = $request->input('periode', 'semua');
        $keyword    = $request->keyword ?: null;
        $bulan      = $request->input('bulan') ? (int) $request->input('bulan') : null;
        $tahun      = $request->input('tahun') ? (int) $request->input('tahun') : null;
        $kategoriId = $request->input('kategori_id') ? (int) $request->input('kategori_id') : null;

        $dari   = null;
        $sampai = null;

        if ($periode === 'hari') {
            $dari   = Carbon::today()->toDateString();
            $sampai = Carbon::today()->toDateString();
        } elseif ($periode === 'custom') {
            $dari   = $request->tanggal_dari ?: null;
            $sampai = $request->tanggal_sampai ?: ($dari ? $dari : null);
        }

        if (in_array($periode, ['bulan', 'tahun']) && !$tahun) {
            $tahun = (int) Carbon::now()->format('Y');
        }
        if ($periode === 'bulan' && !$bulan) {
            $bulan = (int) Carbon::now()->format('n');
        }

        $namaBulan = $bulan ? Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') : '';
        $labelPeriode = match ($periode) {
            'hari'   => 'Hari Ini (' . Carbon::today()->format('d-m-Y') . ')',
            'bulan'  => ($namaBulan ? $namaBulan . ' ' : '') . ($tahun ?? Carbon::now()->year),
            'tahun'  => 'Tahun ' . ($tahun ?? Carbon::now()->year),
            'custom' => 'Custom' . ($dari
                ? ' (' . Carbon::parse($dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($sampai ?? $dari)->format('d-m-Y') . ')'
                : ''),
            default  => 'Semua Data',
        };

        return compact('periode', 'keyword', 'bulan', 'tahun', 'dari', 'sampai', 'labelPeriode', 'kategoriId');
    }

    public function penjualan(Request $request)
    {
        $p = $this->parseParams($request);
        extract($p); // periode, keyword, bulan, tahun, dari, sampai, labelPeriode, kategoriId

        $filter = $this->buildFilter($periode, $dari, $sampai, $keyword, $bulan, $tahun, $kategoriId);

        $totalData      = DetailPenjualan::tap($filter)->select('detail_penjualan.*')->count();
        $totalBarang    = DetailPenjualan::tap($filter)->sum('detail_penjualan.jumlah');
        $totalPenjualan = DetailPenjualan::tap($filter)->sum('detail_penjualan.subtotal');

        $detail = DetailPenjualan::tap($filter)
                    ->with(['penjualan', 'barang.kategori'])
                    ->select('detail_penjualan.*', 'kategori.nama_kategori', 'barang.kategori_id')
                    ->orderBy('kategori.nama_kategori')
                    ->orderBy('barang.nama_barang')
                    ->orderBy('penjualan.tanggal_transaksi', 'desc')
                    ->paginate(25)
                    ->appends($request->query());

        $tahunList      = collect(range((int) date('Y'), 2020));
        $daftarKategori = Kategori::orderBy('nama_kategori')->get();

        $daftarBarang = Barang::select('barang.kode_barang', 'barang.nama_barang')
                            ->join('detail_penjualan', 'detail_penjualan.barang_id', '=', 'barang.id')
                            ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
                            ->where('penjualan.status', 'selesai')
                            ->distinct()
                            ->orderBy('barang.nama_barang')
                            ->get();

        return view('laporan.penjualan', compact(
            'detail', 'totalData', 'totalBarang', 'totalPenjualan',
            'periode', 'labelPeriode', 'bulan', 'tahun', 'tahunList',
            'daftarBarang', 'kategoriId', 'daftarKategori'
        ));
    }

    public function exportExcel(Request $request)
    {
        $p = $this->parseParams($request);
        extract($p);

        $namaFile = 'laporan-penjualan-' . $this->slugPeriode($periode, $bulan, $tahun, $dari, $sampai) . '.xlsx';

        return Excel::download(
            new LaporanPenjualanExport($periode, $dari, $sampai, $keyword, $labelPeriode, $bulan, $tahun, $kategoriId),
            $namaFile
        );
    }

    public function cetak(Request $request)
    {
        $p = $this->parseParams($request);
        extract($p);

        $filter = $this->buildFilter($periode, $dari, $sampai, $keyword, $bulan, $tahun, $kategoriId);

        $totalData      = DetailPenjualan::tap($filter)->select('detail_penjualan.*')->count();
        $totalBarang    = DetailPenjualan::tap($filter)->sum('detail_penjualan.jumlah');
        $totalPenjualan = DetailPenjualan::tap($filter)->sum('detail_penjualan.subtotal');

        $detail = DetailPenjualan::tap($filter)
                    ->with(['penjualan', 'barang.kategori'])
                    ->select('detail_penjualan.*', 'kategori.nama_kategori', 'barang.kategori_id')
                    ->orderBy('kategori.nama_kategori')
                    ->orderBy('barang.nama_barang')
                    ->orderBy('penjualan.tanggal_transaksi', 'desc')
                    ->get();

        return view('laporan.penjualan-cetak', compact(
            'detail', 'totalData', 'totalBarang', 'totalPenjualan', 'labelPeriode'
        ));
    }

    private function slugPeriode(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): string
    {
        if ($periode === 'hari')   return Carbon::today()->format('d-m-Y');
        if ($periode === 'bulan')  return ($bulan ? str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' : '') . ($tahun ?? Carbon::now()->year);
        if ($periode === 'tahun')  return (string) ($tahun ?? Carbon::now()->year);
        if ($periode === 'custom' && $dari) return Carbon::parse($dari)->format('d-m-Y') . '_sd_' . Carbon::parse($sampai ?? $dari)->format('d-m-Y');
        return 'semua';
    }

    // =========================================================
    // LAPORAN PEMBELIAN
    // =========================================================

    private function buildFilterPembelian(string $periode, ?string $dari, ?string $sampai, ?string $keyword, ?int $bulan, ?int $tahun, ?int $kategoriId = null): \Closure
    {
        return function ($q) use ($periode, $dari, $sampai, $keyword, $bulan, $tahun, $kategoriId) {
            $q->join('pembelian', 'pembelian.id', '=', 'detail_pembelian.pembelian_id')
              ->join('barang',    'barang.id',    '=', 'detail_pembelian.barang_id')
              ->join('kategori',  'kategori.id',  '=', 'barang.kategori_id')
              ->when($keyword, fn($q) =>
                  $q->where(fn($q2) =>
                      $q2->where('barang.kode_barang', 'like', '%' . $keyword . '%')
                         ->orWhere('barang.nama_barang', 'like', '%' . $keyword . '%')
                  )
              )
              ->when($kategoriId, fn($q) => $q->where('barang.kategori_id', $kategoriId))
              ->when($periode === 'bulan' && $bulan && $tahun,
                  fn($q) => $q->whereMonth('pembelian.tanggal', $bulan)->whereYear('pembelian.tanggal', $tahun)
              )
              ->when($periode === 'tahun' && $tahun,
                  fn($q) => $q->whereYear('pembelian.tanggal', $tahun)
              )
              ->when($dari,   fn($q) => $q->where('pembelian.tanggal', '>=', $dari))
              ->when($sampai, fn($q) => $q->where('pembelian.tanggal', '<=', $sampai));
        };
    }

    public function pembelian(Request $request)
    {
        $p = $this->parseParams($request);
        extract($p);

        $filter = $this->buildFilterPembelian($periode, $dari, $sampai, $keyword, $bulan, $tahun, $kategoriId);

        $totalData      = DetailPembelian::tap($filter)->select('detail_pembelian.*')->count();
        $totalBarang    = DetailPembelian::tap($filter)->sum('detail_pembelian.jumlah');
        $totalPembelian = DetailPembelian::tap($filter)->sum('detail_pembelian.subtotal');

        $detail = DetailPembelian::tap($filter)
                    ->with(['pembelian', 'barang.kategori'])
                    ->select('detail_pembelian.*', 'kategori.nama_kategori', 'barang.kategori_id')
                    ->orderBy('kategori.nama_kategori')
                    ->orderBy('barang.nama_barang')
                    ->orderBy('pembelian.tanggal', 'desc')
                    ->paginate(25)
                    ->appends($request->query());

        $tahunList      = collect(range((int) date('Y'), 2020));
        $daftarKategori = Kategori::orderBy('nama_kategori')->get();

        $daftarBarang = Barang::select('barang.kode_barang', 'barang.nama_barang')
                            ->join('detail_pembelian', 'detail_pembelian.barang_id', '=', 'barang.id')
                            ->distinct()
                            ->orderBy('barang.nama_barang')
                            ->get();

        return view('laporan.pembelian', compact(
            'detail', 'totalData', 'totalBarang', 'totalPembelian',
            'periode', 'labelPeriode', 'bulan', 'tahun', 'tahunList',
            'daftarBarang', 'kategoriId', 'daftarKategori'
        ));
    }

    public function exportExcelPembelian(Request $request)
    {
        $p = $this->parseParams($request);
        extract($p);

        $namaFile = 'laporan-pembelian-' . $this->slugPeriode($periode, $bulan, $tahun, $dari, $sampai) . '.xlsx';

        return Excel::download(
            new LaporanPembelianExport($periode, $dari, $sampai, $keyword, $labelPeriode, $bulan, $tahun, $kategoriId),
            $namaFile
        );
    }

    public function cetakPembelian(Request $request)
    {
        $p = $this->parseParams($request);
        extract($p);

        $filter = $this->buildFilterPembelian($periode, $dari, $sampai, $keyword, $bulan, $tahun, $kategoriId);

        $totalData      = DetailPembelian::tap($filter)->select('detail_pembelian.*')->count();
        $totalBarang    = DetailPembelian::tap($filter)->sum('detail_pembelian.jumlah');
        $totalPembelian = DetailPembelian::tap($filter)->sum('detail_pembelian.subtotal');

        $detail = DetailPembelian::tap($filter)
                    ->with(['pembelian', 'barang.kategori'])
                    ->select('detail_pembelian.*', 'kategori.nama_kategori', 'barang.kategori_id')
                    ->orderBy('kategori.nama_kategori')
                    ->orderBy('barang.nama_barang')
                    ->orderBy('pembelian.tanggal', 'desc')
                    ->get();

        return view('laporan.pembelian-cetak', compact(
            'detail', 'totalData', 'totalBarang', 'totalPembelian', 'labelPeriode'
        ));
    }

    // =========================================================
    // LAPORAN PERSEDIAAN
    // =========================================================

    /**
     * Hitung data persediaan per barang untuk periode tertentu.
     * TIDAK menggunakan barang.stok — semua dihitung dari histori transaksi.
     *
     * Rumus:
     *   stok_awal_barang  = jumlah_beli_awal × isi_per_satuan_beli
     *   stok_awal_periode = stok_awal_barang + beli_sebelum_periode - jual_sebelum_periode
     *   stok_akhir        = stok_awal_periode + beli_dalam_periode - jual_dalam_periode
     */
    private function hitungPersediaan(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai, ?int $kategoriId = null, string $tampilkan = 'semua'): array
    {
        // Tentukan batas tanggal periode
        [$awalPeriode, $akhirPeriode] = $this->batasPeriode($periode, $bulan, $tahun, $dari, $sampai);

        $barangList = Barang::with('kategori')
            ->when($kategoriId, fn($q) => $q->where('kategori_id', $kategoriId))
            ->orderBy('kategori_id')
            ->orderBy('nama_barang')
            ->get();
        $rows = [];

        foreach ($barangList as $barang) {
            $isi            = max((int) ($barang->isi_per_satuan_beli ?? 1), 1);
            $stokAwalBarang = (int) ($barang->jumlah_beli_awal ?? 0) * $isi;

            // Jika ada batas periode, cek apakah barang sudah ada di periode ini
            if ($akhirPeriode !== null) {
                $tglBarang = $barang->tanggal_stok_awal
                    ? $barang->tanggal_stok_awal->toDateString()
                    : null;
                // Barang belum ada saat periode berakhir → skip
                if ($tglBarang && $tglBarang > $akhirPeriode) {
                    continue;
                }
            }

            // ── Pembelian SEBELUM periode (konversi ke satuan jual) ──
            $beliSebelum = 0;
            if ($awalPeriode !== null) {
                $beliSebelum = (int) DB::table('detail_pembelian')
                    ->join('pembelian', 'pembelian.id', '=', 'detail_pembelian.pembelian_id')
                    ->where('detail_pembelian.barang_id', $barang->id)
                    ->where('pembelian.tanggal', '<', $awalPeriode)
                    ->sum('detail_pembelian.jumlah') * $isi;
            }

            // ── Penjualan SEBELUM periode ──
            $jualSebelum = 0;
            if ($awalPeriode !== null) {
                $jualSebelum = (int) DB::table('detail_penjualan')
                    ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
                    ->where('detail_penjualan.barang_id', $barang->id)
                    ->where('penjualan.status', 'selesai')
                    ->where('penjualan.tanggal_transaksi', '<', $awalPeriode)
                    ->sum('detail_penjualan.jumlah');
            }

            $stokAwal = $stokAwalBarang + $beliSebelum - $jualSebelum;

            // ── Pembelian DALAM periode (konversi ke satuan jual) ──
            $beliQuery = DB::table('detail_pembelian')
                ->join('pembelian', 'pembelian.id', '=', 'detail_pembelian.pembelian_id')
                ->where('detail_pembelian.barang_id', $barang->id);

            if ($awalPeriode && $akhirPeriode) {
                $beliQuery->whereBetween('pembelian.tanggal', [$awalPeriode, $akhirPeriode]);
            } elseif ($awalPeriode) {
                $beliQuery->where('pembelian.tanggal', '>=', $awalPeriode);
            } elseif ($akhirPeriode) {
                $beliQuery->where('pembelian.tanggal', '<=', $akhirPeriode);
            }
            // mode semua: tidak ada filter tanggal

            $beliDalam = (int) $beliQuery->sum('detail_pembelian.jumlah') * $isi;

            // ── Penjualan DALAM periode ──
            $jualQuery = DB::table('detail_penjualan')
                ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
                ->where('detail_penjualan.barang_id', $barang->id)
                ->where('penjualan.status', 'selesai');

            if ($awalPeriode && $akhirPeriode) {
                $jualQuery->whereBetween('penjualan.tanggal_transaksi', [$awalPeriode, $akhirPeriode]);
            } elseif ($awalPeriode) {
                $jualQuery->where('penjualan.tanggal_transaksi', '>=', $awalPeriode);
            } elseif ($akhirPeriode) {
                $jualQuery->where('penjualan.tanggal_transaksi', '<=', $akhirPeriode);
            }

            $jualDalam = (int) $jualQuery->sum('detail_penjualan.jumlah');

            $stokAkhir = $stokAwal + $beliDalam - $jualDalam;

            // Skip barang yang tidak relevan di periode ini
            // (stok awal=0, tidak ada aktivitas)
            if ($stokAwal === 0 && $beliDalam === 0 && $jualDalam === 0) {
                continue;
            }

            $rows[] = [
                'kode_barang'    => $barang->kode_barang,
                'nama_barang'    => $barang->nama_barang,
                'nama_kategori'  => $barang->kategori?->nama_kategori ?? 'Tanpa Kategori',
                'satuan_jual'    => $barang->satuan_jual,
                'stok_awal'      => $stokAwal,
                'pembelian'      => $beliDalam,
                'penjualan'      => $jualDalam,
                'stok_akhir'     => $stokAkhir,
            ];
        }

        // Filter Barang Aktif: hanya tampilkan jika ada aktivitas di periode
        if ($tampilkan === 'aktif') {
            $rows = array_values(array_filter($rows, fn($r) => $r['pembelian'] > 0 || $r['penjualan'] > 0));
        }

        return $rows;
    }

    /**
     * Kembalikan [awal, akhir] — didelegasikan ke LabaRugiService.
     */
    private function batasPeriode(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): array
    {
        return (new LabaRugiService())->batasPeriode($periode, $bulan, $tahun, $dari, $sampai);
    }

    public function persediaan(Request $request)
    {
        $p = $this->parseParamsPersediaan($request);
        extract($p); // periode, bulan, tahun, dari, sampai, labelPeriode, kategoriId, tampilkan

        $rows = $this->hitungPersediaan($periode, $bulan, $tahun, $dari, $sampai, $kategoriId, $tampilkan);

        $totalBarang    = count($rows);
        $totalPembelian = array_sum(array_column($rows, 'pembelian'));
        $totalPenjualan = array_sum(array_column($rows, 'penjualan'));
        $totalStokAkhir = array_sum(array_column($rows, 'stok_akhir'));

        $service = new LabaRugiService();
        $barangMap = Barang::whereIn('kode_barang', array_column($rows, 'kode_barang'))
            ->get()->keyBy('kode_barang');
        $totalNilaiPersediaanAkhir = 0;
        foreach ($rows as $row) {
            $b = $barangMap[$row['kode_barang']] ?? null;
            if (!$b || (int) $row['stok_akhir'] <= 0) {
                continue;
            }
            $isi        = max((int) ($b->isi_per_satuan_beli ?? 1), 1);
            $unitAwal   = (int) ($b->jumlah_beli_awal ?? 0) * $isi;
            $totalNilaiPersediaanAkhir += $service->nilaiPersediaanQty(
                (int) $row['stok_akhir'],
                $unitAwal,
                (float) ($b->total_harga_beli ?? 0),
                (float) ($b->harga_satuan_beli ?? 0)
            );
        }

        $tahunList      = collect(range((int) date('Y'), 2020));
        $daftarKategori = \App\Models\Kategori::orderBy('nama_kategori')->get();

        return view('laporan.persediaan', compact(
            'rows', 'totalBarang', 'totalPembelian', 'totalPenjualan', 'totalStokAkhir',
            'totalNilaiPersediaanAkhir',
            'periode', 'labelPeriode', 'bulan', 'tahun', 'tahunList', 'kategoriId', 'daftarKategori', 'tampilkan'
        ));
    }

    public function exportExcelPersediaan(Request $request)
    {
        $p = $this->parseParamsPersediaan($request);
        extract($p);

        $rows     = $this->hitungPersediaan($periode, $bulan, $tahun, $dari, $sampai, $kategoriId, $tampilkan);
        $namaFile = 'laporan-persediaan-' . $this->slugPeriode($periode, $bulan, $tahun, $dari, $sampai) . '.xlsx';

        return Excel::download(new LaporanPersediaanExport($rows, $labelPeriode), $namaFile);
    }

    public function cetakPersediaan(Request $request)
    {
        $p = $this->parseParamsPersediaan($request);
        extract($p);

        $rows = $this->hitungPersediaan($periode, $bulan, $tahun, $dari, $sampai, $kategoriId, $tampilkan);

        $totalBarang    = count($rows);
        $totalPembelian = array_sum(array_column($rows, 'pembelian'));
        $totalPenjualan = array_sum(array_column($rows, 'penjualan'));
        $totalStokAkhir = array_sum(array_column($rows, 'stok_akhir'));

        $service = new LabaRugiService();
        $barangMap = Barang::whereIn('kode_barang', array_column($rows, 'kode_barang'))
            ->get()->keyBy('kode_barang');
        $totalNilaiPersediaanAkhir = 0;
        foreach ($rows as $row) {
            $b = $barangMap[$row['kode_barang']] ?? null;
            if (!$b || (int) $row['stok_akhir'] <= 0) {
                continue;
            }
            $isi      = max((int) ($b->isi_per_satuan_beli ?? 1), 1);
            $unitAwal = (int) ($b->jumlah_beli_awal ?? 0) * $isi;
            $totalNilaiPersediaanAkhir += $service->nilaiPersediaanQty(
                (int) $row['stok_akhir'],
                $unitAwal,
                (float) ($b->total_harga_beli ?? 0),
                (float) ($b->harga_satuan_beli ?? 0)
            );
        }

        return view('laporan.persediaan-cetak', compact(
            'rows', 'totalBarang', 'totalPembelian', 'totalPenjualan', 'totalStokAkhir',
            'totalNilaiPersediaanAkhir', 'labelPeriode'
        ));
    }

    // =========================================================
    // LAPORAN PENJUALAN–PEMBELIAN
    // =========================================================

    /**
     * Hitung rekap nilai rupiah penjualan dan pembelian per barang untuk periode tertentu.
     * Sumber: SUM(detail_penjualan.subtotal) dan SUM(detail_pembelian.subtotal).
     * jumlah_beli_awal TIDAK dihitung — bukan transaksi pembelian.
     */
    /**
     * Hitung rekap nilai rupiah penjualan dan pembelian per barang untuk periode tertentu.
     * Sumber: SUM(detail_penjualan.subtotal) dan SUM(detail_pembelian.subtotal).
     * jumlah_beli_awal TIDAK dihitung — bukan transaksi pembelian.
     *
     * // mengikuti pola hitungPersediaan agar konsisten dengan sistem
     */
    private function hitungPenjualanPembelian(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai, ?int $kategoriId = null, string $tampilkan = 'semua'): array
    {
        // mengikuti pola hitungPersediaan agar konsisten dengan sistem
        [$awalPeriode, $akhirPeriode] = $this->batasPeriode($periode, $bulan, $tahun, $dari, $sampai);

        $barangList = Barang::with('kategori')
            ->when($kategoriId, fn($q) => $q->where('kategori_id', $kategoriId))
            ->orderBy('kategori_id')
            ->orderBy('nama_barang')
            ->get();

        $rows = [];

        foreach ($barangList as $barang) {
            // ── Total Penjualan dalam periode ──
            $jualQuery = DB::table('detail_penjualan')
                ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
                ->where('detail_penjualan.barang_id', $barang->id)
                ->where('penjualan.status', 'selesai');

            if ($awalPeriode && $akhirPeriode) {
                $jualQuery->whereBetween('penjualan.tanggal_transaksi', [$awalPeriode, $akhirPeriode]);
            } elseif ($awalPeriode) {
                $jualQuery->where('penjualan.tanggal_transaksi', '>=', $awalPeriode);
            } elseif ($akhirPeriode) {
                $jualQuery->where('penjualan.tanggal_transaksi', '<=', $akhirPeriode);
            }
            // mode semua: tidak ada filter tanggal

            $totalJual = (float) $jualQuery->sum('detail_penjualan.subtotal');

            // ── Total Pembelian dalam periode ──
            $beliQuery = DB::table('detail_pembelian')
                ->join('pembelian', 'pembelian.id', '=', 'detail_pembelian.pembelian_id')
                ->where('detail_pembelian.barang_id', $barang->id);

            if ($awalPeriode && $akhirPeriode) {
                $beliQuery->whereBetween('pembelian.tanggal', [$awalPeriode, $akhirPeriode]);
            } elseif ($awalPeriode) {
                $beliQuery->where('pembelian.tanggal', '>=', $awalPeriode);
            } elseif ($akhirPeriode) {
                $beliQuery->where('pembelian.tanggal', '<=', $akhirPeriode);
            }
            // mode semua: tidak ada filter tanggal

            $totalBeli = (float) $beliQuery->sum('detail_pembelian.subtotal');

            // Mode 'aktif': skip hanya jika keduanya nol
            // Mode 'semua': tidak ada skip — barang dengan nilai 0 tetap tampil
            if ($tampilkan === 'aktif' && $totalJual == 0 && $totalBeli == 0) {
                continue;
            }

            $rows[] = [
                'kode_barang'     => $barang->kode_barang,
                'nama_barang'     => $barang->nama_barang,
                'nama_kategori'   => $barang->kategori?->nama_kategori ?? 'Tanpa Kategori',
                'total_penjualan' => $totalJual,
                'total_pembelian' => $totalBeli,
            ];
        }

        return $rows;
    }

    private function parseParamsPenjualanPembelian(Request $request): array
    {
        $periode = $request->input('periode', 'semua');
        $bulan   = $request->input('bulan') ? (int) $request->input('bulan') : null;
        $tahun   = $request->input('tahun') ? (int) $request->input('tahun') : null;
        $dari    = null;
        $sampai  = null;

        if ($periode === 'custom') {
            $dari   = $request->tanggal_dari ?: null;
            $sampai = $request->tanggal_sampai ?: ($dari ? $dari : null);
        }

        if (in_array($periode, ['bulan', 'tahun']) && !$tahun) {
            $tahun = (int) Carbon::now()->format('Y');
        }
        if ($periode === 'bulan' && !$bulan) {
            $bulan = (int) Carbon::now()->format('n');
        }

        $namaBulan    = $bulan ? Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') : '';
        $labelPeriode = match ($periode) {
            'bulan'  => ($namaBulan ? $namaBulan . ' ' : '') . ($tahun ?? Carbon::now()->year),
            'tahun'  => 'Tahun ' . ($tahun ?? Carbon::now()->year),
            'custom' => 'Custom' . ($dari
                ? ' (' . Carbon::parse($dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($sampai ?? $dari)->format('d-m-Y') . ')'
                : ''),
            default  => 'Semua Data',
        };

        $kategoriId = $request->input('kategori_id') ? (int) $request->input('kategori_id') : null;
        $tampilkan  = $request->input('tampilkan', 'semua');

        return compact('periode', 'bulan', 'tahun', 'dari', 'sampai', 'labelPeriode', 'kategoriId', 'tampilkan');
    }

    public function penjualanPembelian(Request $request)
    {
        $p = $this->parseParamsPenjualanPembelian($request);
        extract($p);

        $rows = $this->hitungPenjualanPembelian($periode, $bulan, $tahun, $dari, $sampai, $kategoriId, $tampilkan);

        $totalBarang    = count($rows);
        $totalPenjualan = array_sum(array_column($rows, 'total_penjualan'));
        $totalPembelian = array_sum(array_column($rows, 'total_pembelian'));

        $tahunList      = collect(range((int) date('Y'), 2020));
        $daftarKategori = Kategori::orderBy('nama_kategori')->get();

        return view('laporan.penjualan-pembelian', compact(
            'rows', 'totalBarang', 'totalPenjualan', 'totalPembelian',
            'periode', 'labelPeriode', 'bulan', 'tahun', 'tahunList',
            'kategoriId', 'daftarKategori', 'tampilkan'
        ));
    }

    public function exportExcelPenjualanPembelian(Request $request)
    {
        $p = $this->parseParamsPenjualanPembelian($request);
        extract($p);

        $rows     = $this->hitungPenjualanPembelian($periode, $bulan, $tahun, $dari, $sampai, $kategoriId, $tampilkan);
        $namaFile = 'laporan-penjualan-pembelian-' . $this->slugPeriode($periode, $bulan, $tahun, $dari, $sampai) . '.xlsx';

        return Excel::download(new LaporanPenjualanPembelianExport($rows, $labelPeriode), $namaFile);
    }

    public function cetakPenjualanPembelian(Request $request)
    {
        $p = $this->parseParamsPenjualanPembelian($request);
        extract($p);

        $rows = $this->hitungPenjualanPembelian($periode, $bulan, $tahun, $dari, $sampai, $kategoriId, $tampilkan);

        $totalBarang    = count($rows);
        $totalPenjualan = array_sum(array_column($rows, 'total_penjualan'));
        $totalPembelian = array_sum(array_column($rows, 'total_pembelian'));

        return view('laporan.penjualan-pembelian-cetak', compact(
            'rows', 'totalBarang', 'totalPenjualan', 'totalPembelian', 'labelPeriode'
        ));
    }

    /**
     * Parse parameter khusus laporan persediaan.
     * Sama dengan parseParams tapi default periode = 'semua' dan tanpa keyword.
     */
    private function parseParamsPersediaan(Request $request): array
    {
        $periode = $request->input('periode', 'semua');
        $bulan   = $request->input('bulan') ? (int) $request->input('bulan') : null;
        $tahun   = $request->input('tahun') ? (int) $request->input('tahun') : null;
        $dari    = null;
        $sampai  = null;

        if ($periode === 'custom') {
            $dari   = $request->tanggal_dari ?: null;
            $sampai = $request->tanggal_sampai ?: ($dari ? $dari : null);
        }

        if (in_array($periode, ['bulan', 'tahun']) && !$tahun) {
            $tahun = (int) Carbon::now()->format('Y');
        }
        if ($periode === 'bulan' && !$bulan) {
            $bulan = (int) Carbon::now()->format('n');
        }

        $namaBulan    = $bulan ? Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') : '';
        $labelPeriode = match ($periode) {
            'bulan'  => ($namaBulan ? $namaBulan . ' ' : '') . ($tahun ?? Carbon::now()->year),
            'tahun'  => 'Tahun ' . ($tahun ?? Carbon::now()->year),
            'custom' => 'Custom' . ($dari
                ? ' (' . Carbon::parse($dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($sampai ?? $dari)->format('d-m-Y') . ')'
                : ''),
            default  => 'Semua Data',
        };

        $kategoriId = $request->input('kategori_id') ? (int) $request->input('kategori_id') : null;
        $tampilkan  = $request->input('tampilkan', 'semua');

        return compact('periode', 'bulan', 'tahun', 'dari', 'sampai', 'labelPeriode', 'kategoriId', 'tampilkan');
    }

    // =========================================================
    // LAPORAN LABA RUGI (READ ONLY)
    // =========================================================

    /**
     * Parse filter periode — identik dengan Histori Penjualan / Pembelian / Pengeluaran.
     */
    private function parseParamsLabaRugi(Request $request): array
    {
        $periode = $request->input('periode', 'semua');
        $bulan   = $request->input('bulan') ? (int) $request->input('bulan') : null;
        $tahun   = $request->input('tahun') ? (int) $request->input('tahun') : null;
        $dari    = null;
        $sampai  = null;

        if ($periode === 'hari') {
            $dari   = Carbon::today()->toDateString();
            $sampai = Carbon::today()->toDateString();
        } elseif ($periode === 'bulan') {
            if (!$tahun) {
                $tahun = (int) Carbon::now()->format('Y');
            }
            if (!$bulan) {
                $bulan = (int) Carbon::now()->format('n');
            }
            $dari   = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'tahun') {
            if (!$tahun) {
                $tahun = (int) Carbon::now()->format('Y');
            }
            $dari   = "{$tahun}-01-01";
            $sampai = "{$tahun}-12-31";
        } elseif ($periode === 'custom') {
            $dari   = $request->tanggal_dari ?: null;
            $sampai = $request->tanggal_sampai ?: ($dari ? $dari : null);
        }

        $namaBulan    = $bulan ? Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') : '';
        $labelPeriode = match ($periode) {
            'hari'   => 'Hari Ini (' . Carbon::today()->format('d-m-Y') . ')',
            'bulan'  => ($namaBulan ? $namaBulan . ' ' : '') . ($tahun ?? Carbon::now()->year),
            'tahun'  => 'Tahun ' . ($tahun ?? Carbon::now()->year),
            'custom' => $dari
                ? Carbon::parse($dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($sampai ?? $dari)->format('d-m-Y')
                : 'Custom',
            default  => 'Semua Data (Sejak Awal Sistem)',
        };

        return compact('periode', 'bulan', 'tahun', 'dari', 'sampai', 'labelPeriode');
    }

    /**
     * Nilai persediaan (rupiah) — didelegasikan ke LabaRugiService.
     */
    private function nilaiPersediaanQty(int $qty, int $unitAwalBarang, float $totalHargaBeli, float $hargaSatuan): int
    {
        return (new LabaRugiService())->nilaiPersediaanQty($qty, $unitAwalBarang, $totalHargaBeli, $hargaSatuan);
    }

    /**
     * Batas periode laba rugi — didelegasikan ke LabaRugiService.
     */
    private function batasPeriodeLabaRugi(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): array
    {
        return (new LabaRugiService())->batasPeriodeLabaRugi($periode, $bulan, $tahun, $dari, $sampai);
    }

    /**
     * Hitung laba rugi — didelegasikan ke LabaRugiService.
     */
    private function hitungLabaRugi(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): array
    {
        return (new LabaRugiService())->hitungLabaRugi($periode, $bulan, $tahun, $dari, $sampai);
    }

    public function labaRugi(Request $request)
    {
        $p = $this->parseParamsLabaRugi($request);
        extract($p);

        $hasil = $this->hitungLabaRugi($periode, $bulan, $tahun, $dari, $sampai);
        extract($hasil);

        $tahunList = collect(range((int) date('Y'), 2020));
        $labaPositif = $labaBersih >= 0;

        return view('laporan.laba-rugi', compact(
            'periode',
            'bulan',
            'tahun',
            'labelPeriode',
            'tahunList',
            'persediaanAwal',
            'pembelian',
            'barangSiapDijual',
            'persediaanAkhir',
            'hpp',
            'penjualan',
            'labaKotor',
            'pengeluaran',
            'labaBersih',
            'labaPositif'
        ));
    }

    public function exportExcelLabaRugi(Request $request)
    {
        $p = $this->parseParamsLabaRugi($request);
        extract($p);

        $hasil = $this->hitungLabaRugi($periode, $bulan, $tahun, $dari, $sampai);
        extract($hasil);

        return Excel::download(
            new LaporanLabaRugiExport(
                $labelPeriode,
                (float) $persediaanAwal,
                (float) $pembelian,
                (float) $barangSiapDijual,
                (float) $persediaanAkhir,
                (float) $hpp,
                (float) $penjualan,
                (float) $labaKotor,
                (float) $pengeluaran,
                (float) $labaBersih
            ),
            'Laporan_Laba_Rugi.xlsx'
        );
    }

    public function cetakLabaRugi(Request $request)
    {
        $p = $this->parseParamsLabaRugi($request);
        extract($p);

        $hasil = $this->hitungLabaRugi($periode, $bulan, $tahun, $dari, $sampai);
        extract($hasil);

        $labaPositif = $labaBersih >= 0;

        return view('laporan.laba-rugi-cetak', compact(
            'labelPeriode',
            'persediaanAwal',
            'pembelian',
            'barangSiapDijual',
            'persediaanAkhir',
            'hpp',
            'penjualan',
            'labaKotor',
            'pengeluaran',
            'labaBersih',
            'labaPositif'
        ));
    }
}
