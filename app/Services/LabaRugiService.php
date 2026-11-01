<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service untuk perhitungan Laba Rugi.
 *
 * Semua metode dipindahkan PERSIS dari LaporanController
 * tanpa mengubah rumus, operator, pembulatan, tipe data, atau urutan logika.
 *
 * Dipakai oleh: LaporanController dan DashboardController.
 */
class LabaRugiService
{
    /**
     * Nilai persediaan (rupiah) dari qty stok — konsisten dengan total_harga_beli di master.
     * Unit awal barang memakai prorata total_harga_beli; sisanya × harga_satuan_beli.
     * Menghindari selisih pembulatan dari round(total_harga ÷ unit, 2).
     *
     * COPY PERSIS dari LaporanController::nilaiPersediaanQty() — tidak ada perubahan.
     */
    public function nilaiPersediaanQty(int $qty, int $unitAwalBarang, float $totalHargaBeli, float $hargaSatuan): int
    {
        if ($qty <= 0) {
            return 0;
        }

        $unitAwalBarang = max($unitAwalBarang, 0);
        $totalHargaBeli = max($totalHargaBeli, 0);

        if ($unitAwalBarang <= 0) {
            return (int) round($qty * $hargaSatuan, 0);
        }

        $unitDariAwal  = min($qty, $unitAwalBarang);
        $unitTambahan  = $qty - $unitDariAwal;
        $nilaiDariAwal = $totalHargaBeli * ($unitDariAwal / $unitAwalBarang);

        return (int) round($nilaiDariAwal + ($unitTambahan * $hargaSatuan), 0);
    }

    /**
     * Kembalikan [awal, akhir] string tanggal berdasarkan mode periode.
     * Mode semua → [null, null]
     *
     * COPY PERSIS dari LaporanController::batasPeriode() — tidak ada perubahan.
     */
    public function batasPeriode(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): array
    {
        if ($periode === 'semua') {
            return [null, null];
        }

        if ($periode === 'bulan' && $bulan && $tahun) {
            $awal  = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $akhir = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
            return [$awal, $akhir];
        }

        if ($periode === 'tahun' && $tahun) {
            return ["$tahun-01-01", "$tahun-12-31"];
        }

        if ($periode === 'custom' && $dari) {
            return [$dari, $sampai ?? $dari];
        }

        return [null, null];
    }

    /**
     * Batas periode untuk laba rugi — termasuk mode hari.
     *
     * COPY PERSIS dari LaporanController::batasPeriodeLabaRugi() — tidak ada perubahan.
     */
    public function batasPeriodeLabaRugi(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): array
    {
        if ($periode === 'hari') {
            $today = Carbon::today()->toDateString();
            return [$today, $today];
        }

        return $this->batasPeriode($periode, $bulan, $tahun, $dari, $sampai);
    }

    /**
     * Hitung komponen laba rugi (read only).
     * Valuasi persediaan: prorata total_harga_beli + harga_satuan_beli (satuan jual).
     *
     * COPY PERSIS dari LaporanController::hitungLabaRugi() — tidak ada perubahan.
     */
    public function hitungLabaRugi(string $periode, ?int $bulan, ?int $tahun, ?string $dari, ?string $sampai): array
    {
        [$awalPeriode, $akhirPeriode] = $this->batasPeriodeLabaRugi($periode, $bulan, $tahun, $dari, $sampai);

        $persediaanAwal  = 0.0;
        $persediaanAkhir = 0.0;

        foreach (Barang::orderBy('kategori_id')->orderBy('nama_barang')->get() as $barang) {
            $isi            = max((int) ($barang->isi_per_satuan_beli ?? 1), 1);
            $stokAwalBarang = (int) ($barang->jumlah_beli_awal ?? 0) * $isi;
            $totalHargaBeli = (float) ($barang->total_harga_beli ?? 0);
            $hargaSatuan    = (float) ($barang->harga_satuan_beli ?? 0);

            if ($akhirPeriode !== null) {
                $tglBarang = $barang->tanggal_stok_awal
                    ? $barang->tanggal_stok_awal->toDateString()
                    : null;
                if ($tglBarang && $tglBarang > $akhirPeriode) {
                    continue;
                }
            }

            $beliSebelum = 0;
            if ($awalPeriode !== null) {
                $beliSebelum = (int) DB::table('detail_pembelian')
                    ->join('pembelian', 'pembelian.id', '=', 'detail_pembelian.pembelian_id')
                    ->where('detail_pembelian.barang_id', $barang->id)
                    ->where('pembelian.tanggal', '<', $awalPeriode)
                    ->sum('detail_pembelian.jumlah') * $isi;
            }

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

            $beliDalam = (int) $beliQuery->sum('detail_pembelian.jumlah') * $isi;

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

            if ($stokAwal === 0 && $beliDalam === 0 && $jualDalam === 0) {
                continue;
            }

            $persediaanAwal  += $this->nilaiPersediaanQty($stokAwal, $stokAwalBarang, $totalHargaBeli, $hargaSatuan);
            $persediaanAkhir += $this->nilaiPersediaanQty($stokAkhir, $stokAwalBarang, $totalHargaBeli, $hargaSatuan);
        }

        $pembelianQuery = DB::table('detail_pembelian')
            ->join('pembelian', 'pembelian.id', '=', 'detail_pembelian.pembelian_id');

        if ($awalPeriode && $akhirPeriode) {
            $pembelianQuery->whereBetween('pembelian.tanggal', [$awalPeriode, $akhirPeriode]);
        } elseif ($awalPeriode) {
            $pembelianQuery->where('pembelian.tanggal', '>=', $awalPeriode);
        } elseif ($akhirPeriode) {
            $pembelianQuery->where('pembelian.tanggal', '<=', $akhirPeriode);
        }

        $pembelian = (float) $pembelianQuery->sum('detail_pembelian.subtotal');

        $penjualanQuery = DB::table('detail_penjualan')
            ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
            ->where('penjualan.status', 'selesai');

        if ($awalPeriode && $akhirPeriode) {
            $penjualanQuery->whereBetween('penjualan.tanggal_transaksi', [$awalPeriode, $akhirPeriode]);
        } elseif ($awalPeriode) {
            $penjualanQuery->where('penjualan.tanggal_transaksi', '>=', $awalPeriode);
        } elseif ($akhirPeriode) {
            $penjualanQuery->where('penjualan.tanggal_transaksi', '<=', $akhirPeriode);
        }

        $penjualan = (float) $penjualanQuery->sum('detail_penjualan.subtotal');

        $pengeluaranQuery = DB::table('pengeluaran');

        if ($awalPeriode && $akhirPeriode) {
            $pengeluaranQuery->whereBetween('tanggal', [$awalPeriode, $akhirPeriode]);
        } elseif ($awalPeriode) {
            $pengeluaranQuery->where('tanggal', '>=', $awalPeriode);
        } elseif ($akhirPeriode) {
            $pengeluaranQuery->where('tanggal', '<=', $akhirPeriode);
        }

        $pengeluaran = (float) $pengeluaranQuery->sum('nominal');

        $barangSiapDijual = $persediaanAwal + $pembelian;
        $hpp              = $barangSiapDijual - $persediaanAkhir;
        $labaKotor        = $penjualan - $hpp;
        $labaBersih       = $labaKotor - $pengeluaran;

        return compact(
            'persediaanAwal',
            'pembelian',
            'barangSiapDijual',
            'persediaanAkhir',
            'hpp',
            'penjualan',
            'labaKotor',
            'pengeluaran',
            'labaBersih'
        );
    }
}
