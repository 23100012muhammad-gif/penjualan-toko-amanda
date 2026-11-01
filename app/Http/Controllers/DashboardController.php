<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Services\LabaRugiService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isKasir()) {
            return redirect()->route('penjualan.create');
        }

        $tahunIni = now()->year;

        // ── Kartu statistik ─────────────────────────────────────────────
        // Omzet: SUM(detail_penjualan.subtotal) status=selesai
        // sama persis dengan sumber Laporan Penjualan
        $omzet = (float) DB::table('detail_penjualan')
            ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
            ->where('penjualan.status', 'selesai')
            ->sum('detail_penjualan.subtotal');

        // Laba Bersih & Nilai Persediaan: diambil dari LabaRugiService
        // — sumber yang sama persis dengan Laporan Laba Rugi
        $service   = new LabaRugiService();
        $labaRugi  = $service->hitungLabaRugi('semua', null, null, null, null);
        $labaBersih      = $labaRugi['labaBersih'];
        $nilaiPersediaan = $labaRugi['persediaanAkhir'];

        // Total Transaksi: COUNT WHERE status=selesai
        // sama persis dengan sumber Laporan Penjualan
        $totalTransaksi = Penjualan::where('status', 'selesai')->count();

        // Stok Menipis: barang.stok adalah sumber kanonik
        $stokMenipis = Barang::where('stok', '<=', 10)->count();

        // ── Grafik penjualan bulanan (tahun ini) ─────────────────────────
        // Identik dengan Laporan Penjualan: SUM(subtotal) status=selesai
        $penjualanBulanan = DB::table('detail_penjualan')
            ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
            ->where('penjualan.status', 'selesai')
            ->whereYear('penjualan.tanggal_transaksi', $tahunIni)
            ->selectRaw('MONTH(penjualan.tanggal_transaksi) as bulan, SUM(detail_penjualan.subtotal) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $labelBulan    = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $dataPenjualan = [];
        for ($i = 1; $i <= 12; $i++) {
            $dataPenjualan[] = (float) ($penjualanBulanan[$i] ?? 0);
        }

        // ── Grafik laba bersih bulanan (tahun ini) ───────────────────────
        // Setiap titik grafik memanggil hitungLabaRugi('bulan',...)
        // — sumber identik dengan Laporan Laba Rugi per bulan
        $dataLaba = [];
        for ($i = 1; $i <= 12; $i++) {
            $hasilBulan = $service->hitungLabaRugi('bulan', $i, $tahunIni, null, null);
            $dataLaba[] = $hasilBulan['labaBersih'];
        }

        // ── Barang terlaris ── SUM(jumlah) + satuan_jual dari barang
        $barangTerlaris = DetailPenjualan::join('barang', 'detail_penjualan.barang_id', '=', 'barang.id')
            ->selectRaw('barang.nama_barang, barang.satuan_jual, SUM(detail_penjualan.jumlah) as total_terjual')
            ->groupBy('barang.id', 'barang.nama_barang', 'barang.satuan_jual')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // ── Stok menipis ── sertakan satuan_jual
        $daftarStokMenipis = Barang::where('stok', '<=', 10)
            ->orderBy('stok')
            ->limit(10)
            ->get(['nama_barang', 'stok', 'satuan_jual']);

        // ── Transaksi terbaru ─────────────────────────────────────────────
        $transaksiTerbaru = Penjualan::with('user')
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'omzet', 'labaBersih', 'totalTransaksi', 'stokMenipis', 'nilaiPersediaan',
            'labelBulan', 'dataPenjualan', 'dataLaba',
            'barangTerlaris', 'daftarStokMenipis', 'transaksiTerbaru'
        ));
    }
}
