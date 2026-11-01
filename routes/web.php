<?php
 
use App\Http\Controllers\AccountController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KategoriPengeluaranController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
 
// Redirect root ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});
 
Route::middleware(['auth', 'verified'])->group(function () {
 
    // Dashboard (All roles, kasir redirected inside controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
 
    // Pengaturan Akun (All roles)
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::post('/account/avatar', [AccountController::class, 'updateAvatar'])->name('account.avatar');
    // Note: Route::delete('/account') is disabled to protect database integrity
 
    // Riwayat Penjualan & Cetak (All roles)
    Route::get('penjualan/histori', [PenjualanController::class, 'histori'])->name('penjualan.histori');
    Route::get('penjualan/{penjualan}/cetak', [PenjualanController::class, 'cetak'])->name('penjualan.cetak');
 
    // --- Pemilik Toko Only ---
    Route::middleware(['role:pemilik'])->group(function () {
        // Kelola User
        Route::resource('users', UserController::class)->except(['destroy']);
 
        // Laporan
        Route::get('laporan/penjualan',              [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('laporan/penjualan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.penjualan.export');
        Route::get('laporan/penjualan/cetak',        [LaporanController::class, 'cetak'])->name('laporan.penjualan.cetak');
        Route::get('laporan/pembelian',              [LaporanController::class, 'pembelian'])->name('laporan.pembelian');
        Route::get('laporan/pembelian/export-excel', [LaporanController::class, 'exportExcelPembelian'])->name('laporan.pembelian.export');
        Route::get('laporan/pembelian/cetak',        [LaporanController::class, 'cetakPembelian'])->name('laporan.pembelian.cetak');
        Route::get('laporan/persediaan',              [LaporanController::class, 'persediaan'])->name('laporan.persediaan');
        Route::get('laporan/persediaan/export-excel', [LaporanController::class, 'exportExcelPersediaan'])->name('laporan.persediaan.export');
        Route::get('laporan/persediaan/cetak',        [LaporanController::class, 'cetakPersediaan'])->name('laporan.persediaan.cetak');
        Route::get('laporan/penjualan-pembelian',              [LaporanController::class, 'penjualanPembelian'])->name('laporan.penjualan-pembelian');
        Route::get('laporan/penjualan-pembelian/export-excel', [LaporanController::class, 'exportExcelPenjualanPembelian'])->name('laporan.penjualan-pembelian.export');
        Route::get('laporan/penjualan-pembelian/cetak',        [LaporanController::class, 'cetakPenjualanPembelian'])->name('laporan.penjualan-pembelian.cetak');
        Route::get('laporan/laba-rugi',              [LaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
        Route::get('laporan/laba-rugi/export-excel', [LaporanController::class, 'exportExcelLabaRugi'])->name('laporan.laba-rugi.export');
        Route::get('laporan/laba-rugi/cetak',        [LaporanController::class, 'cetakLabaRugi'])->name('laporan.laba-rugi.cetak');
    });
 
    // --- Pemilik Toko & Admin ---
    Route::middleware(['role:pemilik,admin'])->group(function () {
        Route::get('pembelian/histori', [PembelianController::class, 'histori'])->name('pembelian.histori');
        Route::get('pembelian/{pembelian}/cetak', [PembelianController::class, 'cetak'])->name('pembelian.cetak');
    });
 
    // --- Admin Only ---
    Route::middleware(['role:admin'])->group(function () {
        // Master Data
        Route::resource('kategori', KategoriController::class)->except(['show']);
        Route::resource('kategori-pengeluaran', KategoriPengeluaranController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('pengeluaran', PengeluaranController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        
        // Barang
        Route::post('barang/import', [BarangController::class, 'import'])->name('barang.import');
        Route::get('barang/export', [BarangController::class, 'export'])->name('barang.export');
        Route::get('barang/template', [BarangController::class, 'template'])->name('barang.template');
        Route::resource('barang', BarangController::class)->except(['show']);
        Route::get('barang/preview-kode', [BarangController::class, 'previewKode'])->name('barang.preview-kode');
 
        // Transaksi Pembelian / Restock
        Route::get('pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
        Route::post('pembelian',       [PembelianController::class, 'store'])->name('pembelian.store');
    });
 
    // --- Admin & Kasir ---
    Route::middleware(['role:admin,kasir'])->group(function () {
        // Transaksi Penjualan
        Route::get('penjualan/create',  [PenjualanController::class, 'create'])->name('penjualan.create');
        Route::post('penjualan',        [PenjualanController::class, 'store'])->name('penjualan.store');
    });
 
});


require __DIR__.'/auth.php';
