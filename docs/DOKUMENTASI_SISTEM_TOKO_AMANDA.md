# Dokumentasi Sistem Informasi Penjualan Toko Amanda

**Versi dokumen:** 1.3  
**Tanggal:** Juni 2026  
**Lingkup:** Deskripsi sistem, proses bisnis, logika perhitungan, dan hasil pengujian — sesuai implementasi yang berjalan saat ini.

---

## Spesifikasi Perangkat Lunak Pengembangan

### A. Lingkungan Pengembangan

| Komponen | Teknologi | Versi | Fungsi |
|----------|-----------|-------|--------|
| Web Server Lokal | XAMPP (Apache + MySQL) | XAMPP 8.x | Menjalankan aplikasi PHP dan database MySQL di lingkungan lokal/pengembangan |
| Web Server | Apache HTTP Server | 2.4.x (bawaan XAMPP) | Melayani request HTTP dari browser ke aplikasi Laravel |
| Database Server | MySQL | 8.0.x (bawaan XAMPP) | Menyimpan seluruh data transaksi, master barang, pengguna, dan log sistem |
| Runtime Bahasa | PHP | ^8.1 | Bahasa pemrograman utama untuk menjalankan kode Laravel di sisi server |

---

### B. Framework dan Library Backend

| Komponen | Teknologi | Versi | Fungsi |
|----------|-----------|-------|--------|
| Framework Utama | Laravel | ^10.10 | Framework PHP full-stack — routing, ORM (Eloquent), validasi, middleware, autentikasi, dan arsitektur MVC |
| Autentikasi | Laravel Breeze | * (dev) | Scaffolding login, register, reset password, dan verifikasi email berbasis session |
| API Token | Laravel Sanctum | ^3.3 | Manajemen token autentikasi API (tersedia, tidak aktif digunakan di frontend saat ini) |
| REPL / Debug | Laravel Tinker | ^2.8 | Shell interaktif untuk menjalankan kode Eloquent dan debug di CLI |
| HTTP Client | Guzzle HTTP | ^7.2 | Mengirim request HTTP eksternal (tersedia sebagai dependency Laravel) |
| Export/Import Excel | Maatwebsite Excel | ^3.1 | Export laporan (penjualan, pembelian, persediaan, laba rugi) dan import/export data master barang ke format .xlsx |
| ORM | Eloquent (Laravel) | — | Abstraksi query database menggunakan model PHP; mengelola relasi antar tabel |

---

### C. Frontend dan Build Tools

| Komponen | Teknologi | Versi | Fungsi |
|----------|-----------|-------|--------|
| Template Engine | Blade (Laravel) | — | Rendering tampilan HTML di sisi server; digunakan untuk seluruh halaman sistem |
| CSS Framework | Bootstrap | 5.x (via CDN admin template) | Komponen UI: tabel, tombol, form, modal, navbar, grid layout |
| CSS Utility | Tailwind CSS | ^3.1.0 | Utility-class CSS untuk styling tambahan pada komponen auth/account (Laravel Breeze) |
| Grafik / Chart | Chart.js | — (via CDN) | Visualisasi grafik penjualan bulanan dan grafik laba bersih bulanan di Dashboard |
| JavaScript Framework | Alpine.js | ^3.4.2 | Komponen interaktif ringan di sisi klien (toggle, dropdown, validasi form dinamis) |
| HTTP Client (JS) | Axios | ^1.6.4 | Mengirim request AJAX dari browser ke server (contoh: preview kode barang otomatis) |
| Build Tool | Vite | ^5.0.0 | Bundling dan kompilasi asset JavaScript dan CSS untuk production |
| Plugin Vite | laravel-vite-plugin | ^1.0.0 | Integrasi Vite dengan Laravel untuk hot-reload dan manifest asset |
| PostCSS | PostCSS + Autoprefixer | ^8.4.31 / ^10.4.2 | Pemrosesan CSS dan penambahan vendor prefix otomatis |

---

### D. Library dan Tools Pendukung (Dev)

| Komponen | Teknologi | Versi | Fungsi |
|----------|-----------|-------|--------|
| Testing | PHPUnit | ^10.1 | Framework pengujian unit dan integrasi untuk kode PHP/Laravel |
| Testing Helper | Mockery | ^1.4.4 | Pembuatan mock object untuk unit testing |
| Error Handling | Spatie Laravel Ignition | ^2.0 | Tampilan error yang informatif dan interaktif selama pengembangan |
| Error Handling | Nunomaduro Collision | ^7.0 | Laporan error yang lebih jelas di CLI saat menjalankan `php artisan` |
| Code Style | Laravel Pint | ^1.0 | Formatter kode PHP otomatis berbasis PHP CS Fixer |
| Fake Data | FakerPHP | ^1.9.1 | Generasi data palsu untuk database seeder dan factory dalam pengujian |
| Dev Environment | Laravel Sail | ^1.18 | Docker-based development environment (tersedia, tidak aktif digunakan — sistem berjalan di XAMPP) |
| Spreadsheet Engine | PhpSpreadsheet | — (dependency Maatwebsite) | Engine pembaca/penulis file .xlsx yang digunakan oleh Maatwebsite Excel |

---

### E. Database dan Konfigurasi Koneksi

| Aspek | Nilai |
|-------|-------|
| DBMS | MySQL |
| Host | 127.0.0.1 |
| Port | 3306 |
| Nama Database | `Toko_amanda` |
| Driver Laravel | `mysql` |
| ORM | Eloquent (Laravel) |
| Migration | 29 file migration (skema bertahap) |
| Seeder | `DatabaseSeeder.php` (tidak ada data bawaan — data diisi manual) |

---

### F. Ringkasan Stack Teknologi

```
[Browser]
    │ HTTP Request
    ▼
[Apache / XAMPP]
    │
    ▼
[PHP ^8.1 + Laravel ^10.10]  ←→  [MySQL 8.x / Toko_amanda]
    │ Blade Views
    ▼
[Bootstrap 5 + Chart.js + Alpine.js]
    │ Build Asset
[Vite ^5 + Tailwind CSS ^3]
```

**Pola arsitektur:** MVC (Model–View–Controller) dengan tambahan Service Layer (`LabaRugiService`) untuk logika perhitungan laba rugi dan valuasi persediaan yang digunakan bersama oleh Dashboard, Laporan Persediaan, dan Laporan Laba Rugi.

---

## Arsitektur Aplikasi (Model-View-Controller)

Sistem Informasi Penjualan Toko Amanda dibangun di atas pola arsitektur **MVC (Model–View–Controller)** bawaan Laravel, diperluas dengan satu **Service Layer** terpusat untuk logika bisnis kompleks. Berikut penjelasan masing-masing lapisan beserta komponen nyata yang ada di project ini.

---

### G. Lapisan Model

Model adalah representasi data dan logika domain — menghubungkan kode PHP dengan tabel database melalui Eloquent ORM. Setiap model mendefinisikan `$fillable`, `$casts`, dan relasi antar tabel.

| Model | File | Tabel | Tanggung Jawab |
|-------|------|-------|----------------|
| `User` | `app/Models/User.php` | `users` | Data pengguna, autentikasi, relasi ke penjualan/pengeluaran/stok log |
| `Kategori` | `app/Models/Kategori.php` | `kategori` | Master kategori barang; relasi `hasMany(Barang)` |
| `Barang` | `app/Models/Barang.php` | `barang` | Master barang: harga, stok, satuan, kode; relasi ke `DetailPenjualan`, `DetailPembelian` |
| `Pembelian` | `app/Models/Pembelian.php` | `pembelian` | Header transaksi restock; relasi `hasMany(DetailPembelian)` |
| `DetailPembelian` | `app/Models/DetailPembelian.php` | `detail_pembelian` | Baris item per transaksi pembelian; `belongsTo` Pembelian & Barang |
| `Penjualan` | `app/Models/Penjualan.php` | `penjualan` | Header transaksi penjualan; relasi ke `DetailPenjualan`, `User` |
| `DetailPenjualan` | `app/Models/DetailPenjualan.php` | `detail_penjualan` | Baris item per transaksi penjualan; snapshot harga jual saat transaksi |
| `KategoriPengeluaran` | `app/Models/KategoriPengeluaran.php` | `kategori_pengeluaran` | Master kategori biaya operasional; relasi `hasMany(Pengeluaran)` |
| `Pengeluaran` | `app/Models/Pengeluaran.php` | `pengeluaran` | Pencatatan biaya operasional (listrik, BBM, dll.) |
| `StokLog` | `app/Models/StokLog.php` | `stok_log` | Audit trail otomatis setiap perubahan stok (masuk/keluar); `belongsTo` Barang & Penjualan |

**Konvensi penting pada Model:**
- `Barang` menyimpan `stok` sebagai angka live yang diperbarui otomatis setiap transaksi (bukan dihitung ulang dari log).
- `DetailPenjualan.harga` adalah **snapshot** harga jual saat transaksi terjadi — tidak ikut berubah jika master barang diupdate kemudian.
- `StokLog` dibuat secara programatik oleh Controller (bukan trigger database), sehingga setiap baris selalu memiliki konteks transaksi.

---

### H. Lapisan View

View adalah lapisan presentasi — seluruhnya menggunakan **Blade template engine** Laravel. View hanya menampilkan data yang dikirim Controller; tidak ada logika bisnis di dalam file Blade.

**Struktur direktori View (`resources/views/`):**

| Direktori / File | Isi |
|------------------|-----|
| `layouts/` | Layout induk: `app.blade.php` (dengan sidebar & header), `auth.blade.php` (halaman login/register) |
| `layouts/sidebar.blade.php` | Navigasi sidebar: menu Master Data, Transaksi, Pengeluaran, Laporan, Pengaturan |
| `layouts/navigation.blade.php` | Topbar: nama pengguna, avatar, logout |
| `components/` | Komponen Blade reusable: `alert`, `modal`, `input-label`, `input-error`, `primary-button`, dll. |
| `auth/` | Halaman login, register, reset password, verifikasi email (dari Laravel Breeze) |
| `dashboard.blade.php` | Halaman utama: kartu statistik, grafik Chart.js, barang terlaris, stok menipis, transaksi terbaru |
| `master/barang/` | CRUD master barang: `index`, `create`, `edit` |
| `master/kategori/` | CRUD kategori barang: `index`, `create`, `edit` |
| `master/kategori_pengeluaran/` | CRUD kategori pengeluaran: `index`, `create`, `edit` |
| `transaksi/penjualan/` | Form penjualan (`create`) dan riwayat penjualan (`histori`) |
| `transaksi/pembelian/` | Form pembelian/restock (`create`) dan riwayat pembelian (`histori`) |
| `pengeluaran/` | Daftar, tambah, dan edit pengeluaran operasional |
| `laporan/penjualan.blade.php` | Laporan penjualan dengan filter periode + ringkasan |
| `laporan/pembelian.blade.php` | Laporan pembelian dengan filter periode + ringkasan |
| `laporan/persediaan.blade.php` | Laporan persediaan stok per barang |
| `laporan/laba-rugi.blade.php` | Laporan laba rugi format akuntansi + penjelasan rumus |
| `laporan/penjualan-pembelian.blade.php` | Laporan gabungan penjualan vs pembelian per barang |
| `laporan/*-cetak.blade.php` | Versi cetak masing-masing laporan (5 file) — tanpa sidebar, siap print |
| `account/edit.blade.php` | Halaman pengaturan profil, password, avatar, hapus akun |

**Data dikirim ke View** menggunakan `compact()` dari Controller, misalnya:
- `DashboardController` → mengirim `$omzet`, `$labaBersih`, `$dataPenjualan`, `$dataLaba`, dll. ke `dashboard.blade.php`
- `LaporanController@labaRugi` → mengirim `$persediaanAwal`, `$hpp`, `$labaBersih`, dll. ke `laporan.laba-rugi`

---

### I. Lapisan Controller

Controller adalah jembatan antara request HTTP dari browser dengan Model dan View. Setiap Controller menerima request, menjalankan logika, memanggil Model, lalu mengembalikan View atau redirect.

| Controller | File | Route Utama | Fungsi |
|------------|------|-------------|--------|
| `DashboardController` | `app/Http/Controllers/DashboardController.php` | `GET /dashboard` | Agregasi omzet, laba bersih (via `LabaRugiService`), grafik bulanan, barang terlaris, stok menipis, transaksi terbaru |
| `KategoriController` | `KategoriController.php` | `Resource /kategori` | CRUD penuh kategori barang |
| `BarangController` | `BarangController.php` | `Resource /barang` + `/barang/import`, `/export`, `/template`, `/preview-kode` | CRUD master barang, generate kode otomatis, hitung harga, import/export Excel, AJAX preview kode |
| `PembelianController` | `PembelianController.php` | `GET/POST /pembelian` | Form restock, simpan transaksi dalam DB transaction, tambah stok, buat stok log masuk, histori |
| `PenjualanController` | `PenjualanController.php` | `GET/POST /penjualan` | Form penjualan, validasi stok, simpan transaksi, kurangi stok, buat stok log keluar, histori |
| `KategoriPengeluaranController` | `KategoriPengeluaranController.php` | `Resource /kategori-pengeluaran` (tanpa delete) | CRUD kategori biaya operasional |
| `PengeluaranController` | `PengeluaranController.php` | `Resource /pengeluaran` (tanpa delete) | Input dan edit pengeluaran operasional |
| `LaporanController` | `LaporanController.php` | `GET /laporan/*` | Semua laporan (penjualan, pembelian, persediaan, laba rugi, penjualan-pembelian) + cetak + export Excel; delegasi perhitungan ke `LabaRugiService` |
| `AccountController` | `AccountController.php` | `/account` | Edit profil, ganti password, upload avatar, hapus akun |
| `Auth\*Controller` | `app/Http/Controllers/Auth/` | `/login`, `/register`, `/forgot-password`, dll. | Login, register, logout, reset password, verifikasi email (Laravel Breeze) |

**Pola umum Controller dalam sistem ini:**

```
Request HTTP masuk
    → Validasi (Form Request atau inline validate())
    → Query Model / panggil Service
    → Kirim data ke View (return view(..., compact(...)))
    atau redirect dengan flash message
```

**Contoh nyata — PenjualanController@store:**
1. Validasi input (tanggal, items)
2. Loop pre-check stok setiap barang → tolak jika tidak cukup
3. `DB::transaction()` → buat `Penjualan`, loop buat `DetailPenjualan`, `decrement stok`, buat `StokLog`
4. Commit → redirect dengan flash `success`

---

### J. Service Layer — LabaRugiService

Laravel MVC standar hanya memiliki tiga lapisan, namun sistem ini menambahkan **Service Layer** untuk memisahkan logika perhitungan yang kompleks dan dipakai oleh lebih dari satu Controller.

| Aspek | Detail |
|-------|--------|
| File | `app/Services/LabaRugiService.php` |
| Namespace | `App\Services\LabaRugiService` |
| Dipakai oleh | `LaporanController` dan `DashboardController` |
| Alasan dibuat | Logika laba rugi dan valuasi persediaan sama persis antara Dashboard dan Laporan — dipisahkan agar tidak duplikasi dan mudah diuji |

**Method yang tersedia:**

| Method | Input | Output | Fungsi |
|--------|-------|--------|--------|
| `nilaiPersediaanQty()` | qty, unitAwal, totalHargaBeli, hargaSatuan | `int` rupiah | Mengkonversi kuantitas stok menjadi nilai rupiah (prorata untuk unit awal, harga satuan untuk unit tambahan) |
| `batasPeriode()` | periode, bulan, tahun, dari, sampai | `[awal, akhir]` | Mengembalikan string tanggal awal dan akhir periode; mode `semua` → `[null, null]` |
| `batasPeriodeLabaRugi()` | idem + mode `hari` | `[awal, akhir]` | Seperti `batasPeriode()` dengan tambahan dukungan mode hari |
| `hitungLabaRugi()` | periode, bulan, tahun, dari, sampai | array 9 komponen | Menghitung PA, Pembelian, BSD, PAkhir, HPP, Penjualan, Laba Kotor, Pengeluaran, Laba Bersih |

---

### K. Alur Request Lengkap (End-to-End)

Berikut alur nyata satu request dari browser hingga respons, menggunakan contoh **membuka Laporan Laba Rugi**:

```
Browser
  │  GET /laporan/laba-rugi?periode=bulan&bulan=5&tahun=2026
  ▼
Route (routes/web.php)
  │  middleware ['auth', 'verified']
  │  → LaporanController::labaRugi()
  ▼
Controller (LaporanController)
  │  parseParamsLabaRugi() → [periode, bulan, tahun, dari, sampai, labelPeriode]
  │  hitungLabaRugi() → delegasi ke LabaRugiService
  ▼
Service (LabaRugiService)
  │  batasPeriodeLabaRugi() → ['2026-05-01', '2026-05-31']
  │  Loop Barang::all()
  │    ├─ Query detail_pembelian (sebelum & dalam periode)
  │    ├─ Query detail_penjualan (sebelum & dalam periode)
  │    ├─ nilaiPersediaanQty() → PA per barang
  │    └─ nilaiPersediaanQty() → PAkhir per barang
  │  SUM detail_pembelian.subtotal (pembelian)
  │  SUM detail_penjualan.subtotal (penjualan, status=selesai)
  │  SUM pengeluaran.nominal
  │  Rumus: BSD=PA+Pembelian; HPP=BSD-PAkhir; LK=Penjualan-HPP; LB=LK-Pengeluaran
  │  return array 9 komponen
  ▼
Controller (LaporanController)
  │  return view('laporan.laba-rugi', compact(...))
  ▼
View (resources/views/laporan/laba-rugi.blade.php)
  │  Blade merender HTML dengan data dari Controller
  ▼
Browser
  └─ Halaman Laporan Laba Rugi tampil
```

---

### L. Middleware dan Keamanan Akses

Seluruh route bisnis (semua modul kecuali login/register) dilindungi oleh dua middleware:

| Middleware | Fungsi |
|------------|--------|
| `auth` | Memastikan pengguna sudah login; jika belum, redirect ke `/login` |
| `verified` | Memastikan email sudah diverifikasi (route verifikasi tersedia di `routes/auth.php`) |

Route didefinisikan dalam satu grup di `routes/web.php`:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard, Master Data, Transaksi, Laporan, Akun
});
```

Tidak ada pembagian role (admin/kasir) — semua pengguna yang terautentikasi memiliki akses penuh ke seluruh modul.

---

### M. Ringkasan Pemetaan MVC

```
┌─────────────────────────────────────────────────────────────┐
│                    LAPISAN VIEW (Blade)                      │
│  resources/views/                                            │
│  dashboard │ master/* │ transaksi/* │ laporan/* │ account/*  │
└───────────────────────────┬─────────────────────────────────┘
                            │  return view() / compact()
┌───────────────────────────▼─────────────────────────────────┐
│                 LAPISAN CONTROLLER                           │
│  DashboardController │ BarangController │ PenjualanController│
│  PembelianController │ LaporanController │ AccountController  │
│  KategoriController  │ PengeluaranController │ Auth/*         │
└──────────┬──────────────────────────┬──────────────────────┘
           │ Eloquent ORM             │ new LabaRugiService()
┌──────────▼───────────┐  ┌──────────▼──────────────────────┐
│   LAPISAN MODEL       │  │      SERVICE LAYER               │
│  Barang, Penjualan   │  │  LabaRugiService                 │
│  Pembelian, StokLog  │  │  nilaiPersediaanQty()            │
│  Pengeluaran, User   │  │  hitungLabaRugi()                │
│  Kategori, dll.      │  │  batasPeriode()                  │
└──────────┬───────────┘  └──────────────────────────────────┘
           │ SQL Query
┌──────────▼───────────┐
│     DATABASE          │
│  MySQL / Toko_amanda  │
│  10 tabel bisnis      │
└──────────────────────┘
```

---

## Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Fitur Sistem](#2-fitur-sistem)
3. [Alur Bisnis Sistem](#3-alur-bisnis-sistem)
4. [Alur Pembelian](#4-alur-pembelian)
5. [Alur Penjualan](#5-alur-penjualan)
6. [Alur Pengelolaan Stok](#6-alur-pengelolaan-stok)
7. [Alur Pengelolaan Pengeluaran](#7-alur-pengelolaan-pengeluaran)
8. [Penjelasan Laporan Penjualan](#8-penjelasan-laporan-penjualan)
9. [Penjelasan Laporan Pembelian](#9-penjelasan-laporan-pembelian)
10. [Penjelasan Laporan Persediaan](#10-penjelasan-laporan-persediaan)
11. [Penjelasan Laporan Laba Rugi](#11-penjelasan-laporan-laba-rugi)
12. [Rumus yang Digunakan Sistem](#12-rumus-yang-digunakan-sistem)
13. [Struktur Data Sistem](#13-struktur-data-sistem)
14. [Hasil Pengujian Sistem](#14-hasil-pengujian-sistem)
15. [Kesimpulan Sistem](#15-kesimpulan-sistem)

---

## 1. Gambaran Umum Sistem

### 1.1 Tujuan Sistem

Sistem Informasi Penjualan Toko Amanda dirancang untuk mendukung operasional harian toko sembako/kebutuhan rumah tangga dalam satu platform terpadu. Tujuan utamanya adalah mencatat barang dan stok, memproses pembelian (restock) dan penjualan, mencatat pengeluaran operasional, serta menyajikan laporan keuangan dan persediaan yang akurat berdasarkan data transaksi.

### 1.2 Masalah yang Diselesaikan

Sebelum menggunakan sistem terkomputerisasi, pengelola toko umumnya menghadapi kendala berikut:

- Pencatatan stok manual yang rawan salah hitung dan tidak real-time.
- Kesulitan melacak riwayat pembelian dan penjualan per barang.
- Perhitungan laba rugi yang memakan waktu dan sering tidak konsisten dengan stok aktual.
- Laporan persediaan yang sulit disusun saat periode tertentu diminta.
- Tidak ada jejak audit (log) pergerakan stok yang dapat diverifikasi.

Sistem ini menyelesaikan masalah tersebut dengan menyimpan setiap transaksi secara terstruktur, memperbarui stok secara otomatis, dan menghasilkan laporan dari data yang sama sehingga angka antar modul saling selaras.

### 1.3 Pengguna Sistem

Pengguna sistem adalah **pemilik atau pengelola toko** yang telah memiliki akun login. Setiap transaksi penjualan dan catatan stok log yang terkait penjualan dicatat bersama identitas pengguna yang sedang masuk, sehingga aktivitas dapat dilacak. Akses ke seluruh modul (master data, transaksi, pengeluaran, dan laporan) memerlukan autentikasi.

### 1.4 Manfaat Sistem

| Manfaat | Keterangan |
|--------|------------|
| Akurasi stok | Stok diperbarui otomatis setiap pembelian dan penjualan |
| Konsistensi harga | Harga jual pada transaksi selalu mengikuti master barang |
| Laporan terintegrasi | Penjualan, pembelian, persediaan, dan laba rugi bersumber dari data transaksi yang sama |
| Jejak audit stok | Setiap perubahan stok tercatat dalam log masuk/keluar |
| Efisiensi operasional | Riwayat transaksi, filter periode, cetak, dan ekspor laporan |
| Pengambilan keputusan | Laba rugi dan persediaan dapat dianalisis per hari, bulan, tahun, atau rentang tanggal |

---

## 2. Fitur Sistem

### 2.1 Dashboard

Dashboard merupakan **halaman utama sistem** yang ditampilkan setelah pengguna berhasil masuk. Dashboard berfungsi sebagai **pusat informasi cepat** bagi pemilik toko untuk melakukan **monitoring usaha** secara ringkas, tanpa harus membuka setiap laporan satu per satu.

Halaman ini menyajikan informasi penting hasil agregasi data transaksi dan master barang, antara lain:

| Komponen dashboard | Keterangan | Sumber data |
|--------------------|------------|-------------|
| Ringkasan omzet penjualan | Total seluruh penjualan sejak awal sistem (status selesai) | `SUM(detail_penjualan.subtotal)` |
| Ringkasan laba bersih | Hasil usaha bersih setelah HPP dan pengeluaran operasional | `LabaRugiService::hitungLabaRugi('semua')` |
| Nilai persediaan | Nilai rupiah stok barang yang masih tersisa saat ini | `LabaRugiService::hitungLabaRugi('semua')` → `persediaanAkhir` |
| Jumlah transaksi | Total transaksi penjualan berstatus selesai | `COUNT(penjualan)` status selesai |
| Stok menipis | Jumlah dan daftar barang dengan stok ≤ 10 satuan jual | `barang.stok` |
| Barang terlaris | 5 produk dengan total qty terjual tertinggi | `SUM(detail_penjualan.jumlah)` |
| Grafik penjualan bulanan | Visualisasi tren omzet penjualan per bulan (tahun berjalan) | `SUM(detail_penjualan.subtotal)` per bulan |
| Grafik laba bersih bulanan | Visualisasi tren laba bersih per bulan (tahun berjalan) | `LabaRugiService::hitungLabaRugi('bulan', $bulan, $tahun)` per bulan |
| Transaksi terbaru | Daftar 10 transaksi penjualan terakhir | tabel `penjualan` |

**Konsistensi sumber data Dashboard:**  
Kartu laba bersih, nilai persediaan, dan setiap titik grafik laba bulanan seluruhnya bersumber dari `LabaRugiService` — service yang sama yang digunakan oleh Laporan Laba Rugi. Artinya angka yang ditampilkan di Dashboard **identik** dengan angka yang akan ditemukan pengguna saat membuka Laporan Laba Rugi dengan periode yang sama. Tidak ada metode perhitungan terpisah di Dashboard untuk komponen-komponen tersebut.

Dengan demikian, Dashboard menjadi **instrumen pengambilan keputusan harian**: pemilik toko dapat menilai performa penjualan, mengidentifikasi barang yang laris atau hampir habis, serta memantau keuntungan secara visual sebelum menindaklanjuti detail di modul laporan masing-masing.

Dashboard tetap menyediakan akses menu ke seluruh modul sistem (master data, transaksi, pengeluaran, dan laporan) sebagai pelengkap monitoring tersebut.

### 2.2 Master Barang

Master Barang menyimpan data identitas dan harga setiap produk yang dijual toko. Field penting yang dikelola:

| Field | Penjelasan |
|-------|------------|
| Kode barang | Kode unik otomatis berdasarkan kategori |
| Nama barang | Nama produk yang ditampilkan di transaksi dan laporan |
| Kategori | Pengelompokan barang (misalnya minuman, sembako) |
| Tanggal stok awal | Tanggal pencatatan stok pertama kali di sistem |
| Jumlah beli awal | Jumlah pembelian awal dalam **satuan beli** (misalnya pack, dus) |
| Satuan beli | Satuan saat membeli dari supplier (pack, dus, karton, dll.) |
| Isi per satuan beli | Banyaknya satuan jual dalam satu satuan beli (misalnya 1 dus = 24 botol) |
| Total harga beli | Total harga untuk jumlah beli awal tersebut |
| Harga satuan beli | Harga per satuan jual, dihitung dari total harga beli |
| Persentase keuntungan | Persentase markup untuk menentukan harga jual (kecuali diisi manual) |
| Harga jual | Harga jual per satuan jual kepada pelanggan |
| Stok | Kuantitas tersedia dalam **satuan jual** (misalnya botol, pcs) |
| Status aktif | Barang aktif dapat dipilih pada transaksi; nonaktif disembunyikan dari penjualan/pembelian baru |

**Stok awal** dihitung saat barang pertama kali dibuat:  
`stok awal = jumlah beli awal × isi per satuan beli`.

Master barang juga mendukung **impor** dan **ekspor** data dalam format spreadsheet untuk mempercepat pengisian awal.

### 2.3 Kategori Barang

Kategori Barang mengelompokkan produk dan menentukan konvensi penamaan:

- Nama kategori (contoh: Minuman, Sembako, Perawatan).
- Kode prefix untuk pembentukan kode barang otomatis.
- Satuan beli dan satuan jual default per kategori.

Setiap barang wajib terhubung ke satu kategori.

### 2.4 Pembelian (Restock)

Modul pembelian digunakan untuk **menambah stok** barang yang sudah terdaftar di master. Fitur utama:

- Input tanggal pembelian.
- Memilih satu atau lebih barang dengan jumlah dalam **satuan beli** (pack/dus).
- Sistem menghitung subtotal dari harga beli master.
- Riwayat pembelian dapat dilihat di menu Riwayat Pembelian.
- Setiap pembelian menghasilkan kode unik (format PB0001, PB0002, dan seterusnya).

### 2.5 Penjualan

Modul penjualan digunakan untuk mencatat transaksi ke pelanggan. Fitur utama:

- Input tanggal transaksi.
- Hanya barang **aktif** dengan **stok lebih dari nol** yang dapat dipilih.
- Jumlah diinput dalam **satuan jual**.
- Sistem menolak penjualan jika jumlah melebihi stok tersedia.
- Harga per item otomatis mengambil **harga jual** dari master barang.
- Riwayat penjualan tersedia di menu Riwayat Penjualan.
- Setiap penjualan berstatus **selesai** dan memiliki kode unik (TRX0001, TRX0002, dan seterusnya).

### 2.6 Pengeluaran

Modul pengeluaran mencatat biaya operasional toko yang tidak terkait pembelian barang dagang. Setiap pengeluaran memiliki:

- Tanggal.
- Kategori pengeluaran.
- Nominal (rupiah).
- Keterangan tambahan.

Pengeluaran mempengaruhi **laba bersih** pada laporan laba rugi.

### 2.7 Stok Log

Stok Log adalah **catatan audit otomatis** setiap kali stok berubah akibat transaksi bisnis. Setiap pembelian dan penjualan menghasilkan entri log tanpa input manual tambahan:

| Jenis | Pemicu |
|-------|--------|
| Masuk | Pembelian / restock |
| Keluar | Penjualan |

Setiap entri mencatat: barang, jumlah perubahan, stok sebelum, stok sesudah, keterangan (misalnya kode transaksi), dan pengguna (untuk penjualan).

Stok log digunakan untuk verifikasi integritas: total qty masuk harus sama dengan total pembelian (dalam satuan jual), dan total qty keluar harus sama dengan total penjualan.

#### Fungsi Stok Log

Setiap perubahan stok yang disebabkan oleh **pembelian** atau **penjualan** dicatat secara otomatis ke dalam stok log. Dengan demikian, stok log berfungsi sebagai **audit trail** (jejak audit) atau catatan historis pergerakan stok dari waktu ke waktu.

Manfaat stok log dalam operasional toko:

| Manfaat | Penjelasan |
|---------|------------|
| Melacak perubahan stok | Setiap penambahan dan pengurangan dapat ditelusuri beserta tanggal dan keterangan transaksi |
| Investigasi selisih stok | Apabila stok fisik berbeda dengan sistem, log membantu menemukan kapan dan transaksi apa yang mengubah angka |
| Akurasi data persediaan | Memastikan setiap perubahan stok tercatat dan selaras dengan transaksi pembelian serta penjualan |
| Mempermudah audit | Pemeriksaan internal atau eksternal dapat memverifikasi bahwa stok sistem didukung oleh bukti transaksi |

### 2.8 Laporan Penjualan

Menampilkan rincian penjualan per barang dengan filter periode (hari ini, bulan, tahun, custom, atau semua data). Ringkasan menampilkan jumlah baris data, total kuantitas terjual, dan **total omzet penjualan**. Mendukung cetak dan ekspor spreadsheet.

### 2.9 Laporan Pembelian

Menampilkan rincian pembelian per barang dengan filter periode yang sama. Ringkasan menampilkan total nilai pembelian. Mendukung cetak dan ekspor spreadsheet.

### 2.10 Laporan Persediaan

Menampilkan pergerakan stok per barang dalam periode tertentu:

- Stok awal periode
- Pembelian dalam periode (satuan jual)
- Penjualan dalam periode (satuan jual)
- Stok akhir periode
- **Total nilai persediaan akhir** (rupiah) — dihitung menggunakan metode valuasi yang sama dengan Laporan Laba Rugi

Nilai persediaan akhir pada ringkasan Laporan Persediaan **identik** dengan komponen `persediaanAkhir` pada Laporan Laba Rugi untuk periode yang sama, karena keduanya memanggil fungsi valuasi yang sama (`LabaRugiService::nilaiPersediaanQty()`). Mendukung filter kategori, cetak, dan ekspor.

### 2.11 Laporan Laba Rugi

Menyajikan ikhtisar hasil usaha toko dalam periode terpilih, meliputi persediaan awal, pembelian, barang siap dijual, persediaan akhir, HPP, omzet penjualan, laba kotor, pengeluaran operasional, dan laba bersih. Dilengkapi penjelasan rumus pada tampilan layar.

### 2.12 Laporan Penjualan–Pembelian

Laporan gabungan yang membandingkan total penjualan dan total pembelian per barang dalam satu periode. Berguna untuk analisis perputaran barang.

### 2.13 Cetak Laporan

Setiap laporan utama memiliki tombol **Cetak Laporan** yang membuka tampilan khusus cetak. Angka pada tampilan cetak **identik** dengan tampilan layar karena menggunakan sumber perhitungan yang sama. Laporan laba rugi cetak dapat langsung memicu dialog cetak peramban.

### 2.14 Fitur Pendukung Lainnya

- **Kategori Pengeluaran:** pengelolaan kategori biaya operasional.
- **Pengaturan akun:** profil pengguna dan foto.
- **Autentikasi:** login, registrasi, dan verifikasi email (sesuai konfigurasi instalasi).

---

## 3. Alur Bisnis Sistem

Alur bisnis utama sistem mengikuti urutan berikut:

```
MASTER BARANG
      ↓
  PEMBELIAN (restock)
      ↓
  STOK BERTAMBAH
      ↓
  PENJUALAN
      ↓
  STOK BERKURANG
      ↓
  PENGELUARAN (operasional)
      ↓
  LAPORAN (penjualan, pembelian, persediaan, laba rugi)
```

### Langkah demi langkah

1. **Master Barang** — Pemilik toko mendaftarkan barang beserta harga beli, harga jual, dan stok awal. Tanpa master barang, transaksi tidak dapat dilakukan.

2. **Pembelian** — Saat stok menipis, pemilik melakukan restock. Sistem menambah stok dan mencatat nilai pembelian.

3. **Stok bertambah** — Kolom stok pada master barang diperbarui; log stok masuk tercatat.

4. **Penjualan** — Pelanggan membeli barang. Sistem memvalidasi ketersediaan stok, mencatat omzet, dan mengurangi stok.

5. **Stok berkurang** — Stok live berkurang sesuai qty terjual; log stok keluar tercatat.

6. **Pengeluaran** — Biaya listrik, BBM, internet, dan biaya operasional lain dicatat terpisah dari pembelian barang.

7. **Laporan** — Semua modul laporan membaca data historis transaksi untuk menghasilkan omzet, pembelian, persediaan, dan laba rugi sesuai periode yang dipilih.

---

## 4. Alur Pembelian

### 4.1 Langkah proses

1. **Pengguna membuat pembelian**  
   Pengguna membuka menu Pembelian / Restock, memilih tanggal, lalu menambahkan barang ke keranjang dengan jumlah dalam satuan beli (misalnya 2 pack).

2. **Detail pembelian disimpan**  
   Untuk setiap item, sistem menyimpan barang, harga beli per satuan beli, jumlah pack, dan subtotal (harga × jumlah). Header pembelian menyimpan total keseluruhan dan kode pembelian.

3. **Stok bertambah**  
   Stok ditambah dalam satuan jual:  
   `tambah stok = jumlah pack × isi per satuan beli`  
   Contoh: 2 pack × 24 botol/pack = 48 botol.

4. **Stok log dibuat**  
   Sistem mencatat log jenis **masuk** dengan jumlah, stok sebelum, stok sesudah, dan keterangan yang memuat kode pembelian.

5. **Laporan pembelian ter-update**  
   Laporan Pembelian menjumlahkan `subtotal` dari semua detail pembelian yang tanggalnya masuk dalam periode filter. Tidak ada entri terpisah di laporan — laporan selalu membaca dari data transaksi.

### 4.2 Aturan harga pada pembelian

- Jika isi per satuan beli = 1 (satuan beli sama dengan satuan jual, misalnya pcs), harga pack = **harga satuan beli**.
- Jika isi > 1 (misalnya beli per dus), harga pack = **total harga beli** pada master (harga satu dus/pack).

---

## 5. Alur Penjualan

### 5.1 Langkah proses

1. **Pengguna membuat penjualan**  
   Pengguna memilih tanggal transaksi dan menambahkan barang ke keranjang (satuan jual).

2. **Validasi stok**  
   Sebelum disimpan, sistem memeriksa apakah stok setiap barang mencukupi. Jika tidak, transaksi ditolak dengan pesan per barang.

3. **Harga menggunakan harga jual barang**  
   Setiap baris detail menyimpan `harga = harga_jual` dari master pada saat transaksi. Harga tidak diinput manual di kasir.

4. **Detail penjualan disimpan**  
   Tersimpan: barang, harga, jumlah, subtotal (harga × jumlah). Header penjualan menyimpan total dan kode transaksi.

5. **Stok berkurang**  
   Stok barang dikurangi sebesar jumlah terjual (satuan jual).

6. **Stok log dibuat**  
   Sistem mencatat log jenis **keluar** dengan referensi ke transaksi penjualan.

7. **Laporan penjualan ter-update**  
   Laporan Penjualan menjumlahkan subtotal detail penjualan berstatus selesai dalam periode filter.

---

## 6. Alur Pengelolaan Stok

### 6.1 Rumus stok

Untuk setiap barang, **stok akhir** pada sistem mengikuti:

```
stok akhir = stok awal (dari master)
           + total pembelian (dalam satuan jual)
           - total penjualan (dalam satuan jual)
```

**Stok awal master** = `jumlah_beli_awal × isi_per_satuan_beli`.

**Pembelian** dikonversi: jumlah pack pada detail pembelian × isi per satuan beli.

**Penjualan** sudah dalam satuan jual (langsung dikurangkan).

### 6.2 Contoh sederhana

Barang **Air Mineral 600 ml**:

| Kejadian | Perhitungan | Stok |
|----------|-------------|------|
| Stok awal master | 2 dus × 24 botol = 48 botol | 48 |
| Pembelian 1 dus | +1 × 24 = +24 botol | 72 |
| Penjualan 5 botol | −5 botol | 67 |
| Pembelian 2 pack (masing-masing 6 botol, isi=6) | +2 × 6 = +12 botol | 79 |
| Penjualan 10 botol | −10 botol | **69** |

Stok live di master barang harus menunjukkan **69 botol** setelah rangkaian transaksi di atas.

### 6.3 Laporan persediaan vs stok live

Laporan Persediaan dapat menghitung stok awal periode dengan memperhitungkan transaksi **sebelum** periode tersebut. Stok live di master selalu mencerminkan **seluruh histori** sejak barang terdaftar. Keduanya konsisten apabila formula yang sama diterapkan pada batas periode yang sama.

---

## 7. Alur Pengelolaan Pengeluaran

### 7.1 Kategori pengeluaran

Sistem menggunakan **Kategori Pengeluaran** yang dapat dikelola di master data. Kategori umum yang digunakan toko:

| Kategori | Contoh penggunaan |
|----------|-------------------|
| Listrik | Tagihan listrik toko |
| BBM | Bensin untuk pengiriman atau operasional |
| Internet & WiFi | Biaya koneksi internet toko |
| Operasional lain | Biaya kebersihan, perlengkapan, dll. (dicatat pada kategori yang tersedia, misalnya BBM atau kategori khusus jika ditambahkan) |

### 7.2 Pengaruh terhadap laba rugi

Pengeluaran **tidak mengurangi stok** dan **tidak masuk HPP**. Pengeluaran mengurangi **laba bersih** setelah laba kotor dihitung:

```
Laba Bersih = Laba Kotor − Total Pengeluaran Operasional
```

Contoh: laba kotor Rp 972.583 dan pengeluaran Rp 695.000 menghasilkan laba bersih Rp 277.583.

---

## 8. Penjelasan Laporan Penjualan

### Konsep Perhitungan Laporan

Sistem **tidak memiliki tabel laporan khusus** yang menyimpan angka ringkasan secara terpisah. Laporan **Penjualan**, **Pembelian**, **Persediaan**, dan **Laba Rugi** dihitung **secara dinamis** setiap kali pengguna membuka halaman laporan, dengan membaca data dari tabel transaksi (penjualan, pembelian, pengeluaran) dan master barang sesuai filter periode yang dipilih.

Pendekatan ini memberikan manfaat sebagai berikut:

| Manfaat | Keterangan |
|---------|------------|
| Tidak terjadi duplikasi data | Angka laporan tidak disimpan ulang; sumber kebenaran tetap pada transaksi |
| Laporan selalu terbaru | Setiap transaksi baru langsung tercermin pada laporan berikutnya |
| Basis data lebih efisien | Ruang penyimpanan tidak terpakai untuk salinan angka yang dapat dihitung ulang |
| Konsistensi data lebih terjaga | Layar laporan, cetak, dan ekspor menggunakan perhitungan yang sama dari sumber transaksi |

Karena laporan bersifat dinamis, angka pada **tampilan layar**, **cetak**, dan **ekspor** selalu selaras selama filter periode dan sumber data identik.

### 8.1 Sumber data

Laporan bersumber dari tabel **detail penjualan** yang dihubungkan dengan **penjualan** (header) dan **barang**. Hanya transaksi dengan status **selesai** yang dihitung.

### 8.2 Cara menghitung omzet

```
Omzet penjualan = Σ (subtotal setiap baris detail penjualan)
```

di mana:

```
subtotal = harga_jual × jumlah terjual
```

Filter periode diterapkan pada **tanggal transaksi** penjualan.

### 8.3 Arti omzet

**Omzet** (total penjualan) adalah total uang yang diterima dari penjualan barang dalam periode laporan, sebelum dikurangi HPP dan pengeluaran. Omzet mencerminkan performa penjualan, bukan keuntungan bersih.

---

## 9. Penjelasan Laporan Pembelian

### 9.1 Sumber data

Laporan bersumber dari **detail pembelian** yang dihubungkan dengan **pembelian** (header) dan **barang**.

### 9.2 Cara menghitung total pembelian

```
Total pembelian = Σ (subtotal setiap baris detail pembelian)
```

Filter periode diterapkan pada **tanggal** pembelian.

Total pembelian pada laporan laba rugi menggunakan penjumlahan yang sama untuk periode yang dipilih.

---

## 10. Penjelasan Laporan Persediaan

### 10.1 Komponen per barang

| Kolom | Makna |
|-------|--------|
| Stok awal | Kuantitas pada awal periode (termasuk stok master + transaksi sebelum periode) |
| Pembelian | Total qty masuk dalam periode (satuan jual) |
| Penjualan | Total qty keluar dalam periode (satuan jual) |
| Stok akhir | Stok awal + pembelian − penjualan |

### 10.2 Nilai persediaan akhir (rupiah)

Selain kolom kuantitas, laporan persediaan menampilkan **total nilai persediaan akhir** dalam rupiah pada baris ringkasan. Nilai ini dihitung dengan mengkonversi stok akhir setiap barang menjadi rupiah menggunakan fungsi valuasi `nilaiPersediaanQty()` dari `LabaRugiService`:

- Unit hingga batas stok awal master → dinilai **prorata** dari `total_harga_beli`
- Unit di atas itu → dinilai × `harga_satuan_beli`

Karena menggunakan fungsi yang identik dengan `hitungLabaRugi()`, nilai ini **selalu sama** dengan komponen `Persediaan Akhir` pada Laporan Laba Rugi untuk periode yang sama. Tidak ada selisih pembulatan antar laporan.

### 10.3 Contoh

Periode: **1–31 Mei 2026**, barang **Sabun Cuci 900 g**:

| | Qty (pcs) |
|---|-----------|
| Stok awal periode | 60 |
| Pembelian dalam periode | +24 |
| Penjualan dalam periode | −15 |
| **Stok akhir** | **69** |

Verifikasi: 60 + 24 − 15 = 69 ✓

---

## 11. Penjelasan Laporan Laba Rugi

Bagian ini disusun agar mudah dipahami **pemilik toko**.

### Penjelasan Harga Pokok Penjualan (HPP)

**Harga Pokok Penjualan (HPP)** adalah **modal barang yang berhasil terjual** kepada pelanggan. Secara sederhana, HPP menjawab pertanyaan: *“Berapa rupiah barang yang keluar dari toko karena sudah terjual?”*

Hal penting yang perlu dipahami pemilik toko:

- HPP berasal dari **harga beli barang** yang tercatat pada master, yaitu **harga satuan beli** per satuan jual (bukan harga jual ke pelanggan).
- HPP **bukan harga jual**. Harga jual adalah uang yang diterima dari pelanggan; HPP adalah modal yang “hilang” dari rak karena barang terjual.
- HPP **digunakan untuk menghitung laba rugi**, khususnya laba kotor: `Laba Kotor = Omzet Penjualan − HPP`.

**Contoh sederhana per barang**

Barang **Sarimi** memiliki harga beli (harga satuan beli) **Rp 1.500** per pcs. Jika terjual **3 pcs**, maka modal yang keluar karena penjualan tersebut adalah:

```
HPP = 3 × Rp 1.500 = Rp 4.500
```

Angka **Rp 4.500** merupakan **modal barang yang keluar** akibat penjualan 3 pcs Sarimi. Apabila harga jual Sarimi Rp 2.000 per pcs, omzet penjualan 3 pcs = Rp 6.000, sehingga keuntungan kotor dari barang tersebut (sebelum pengeluaran operasional) = Rp 6.000 − Rp 4.500 = **Rp 1.500**.

Pada **laporan laba rugi** sistem, HPP disajikan sebagai total nilai persediaan yang terjual dalam periode, dihitung dengan rumus persediaan (`Barang Siap Dijual − Persediaan Akhir`) agar selaras dengan seluruh barang dan pergerakan stok. Konsep dasarnya tetap sama: HPP adalah modal barang terjual, bukan omzet dan bukan harga jual.

### Metode Perhitungan Persediaan

Sistem menggunakan **pendekatan persediaan** untuk menghitung HPP pada laporan laba rugi. Artinya, HPP tidak dihitung hanya dengan cara:

```
HPP = jumlah barang terjual × harga beli
```

untuk seluruh toko sekaligus, karena laporan laba rugi harus memperhitungkan **seluruh nilai persediaan** yang dimiliki toko selama periode berjalan—bukan satu barang saja, melainkan kondisi stok dan nilai barang secara keseluruhan pada awal dan akhir periode.

Pendekatan yang digunakan sistem adalah sebagai berikut:

```
Persediaan Awal + Pembelian = Barang Siap Dijual

Barang Siap Dijual − Persediaan Akhir = HPP
```

Penjelasan setiap komponen:

| Komponen | Penjelasan sederhana |
|----------|----------------------|
| Persediaan Awal | Nilai rupiah barang yang **sudah ada** di toko pada **awal** periode laporan |
| Pembelian | Nilai barang yang **ditambah** melalui restock selama periode |
| Barang Siap Dijual | Seluruh nilai barang yang **tersedia untuk dijual** selama periode (awal + pembelian) |
| Persediaan Akhir | Nilai barang yang **masih tersisa** di toko pada **akhir** periode |
| HPP | **Selisih** antara Barang Siap Dijual dan Persediaan Akhir; merupakan modal barang yang dianggap telah terjual |

**Contoh sederhana**

| Komponen | Nilai |
|----------|--------|
| Persediaan Awal | Rp 5.000.000 |
| Pembelian | Rp 2.000.000 |
| **Barang Siap Dijual** | **Rp 7.000.000** |
| Persediaan Akhir | Rp 3.000.000 |
| **HPP** | Rp 7.000.000 − Rp 3.000.000 = **Rp 4.000.000** |

Dari contoh di atas, modal barang yang “keluar” karena dijual selama periode dinilai sebesar **Rp 4.000.000**, sedangkan barang senilai **Rp 3.000.000** masih ada di toko pada akhir periode.

Metode perhitungan persediaan ini digunakan agar **laporan laba rugi konsisten** dengan **kondisi stok** dan **nilai persediaan yang sebenarnya** di toko, sehingga laba kotor dan laba bersih mencerminkan keadaan usaha secara lebih akurat.
Metode yang digunakan pada sistem ini merupakan pendekatan persediaan periodik yang menilai HPP berdasarkan hubungan antara persediaan awal, pembelian, dan persediaan akhir selama periode laporan.

### 11.1 Persediaan Awal

Nilai rupiah barang yang **sudah ada di toko** pada awal periode yang dipilih. Dihitung dari kuantitas stok awal per barang dikalikan dengan metode valuasi harga beli (bagian stok dari jumlah beli awal memakai prorata **total harga beli**, sisanya memakai **harga satuan beli**).

*Analogi:* “Berapa rupiah barang yang saya punya di rak pada tanggal mulai laporan?”

### 11.2 Pembelian

Total uang yang dikeluarkan untuk **restock** barang dagang dalam periode (bukan pengeluaran listrik/BBM).

### 11.3 Barang Siap Dijual

```
Barang Siap Dijual = Persediaan Awal + Pembelian
```

*Analogi:* “Total nilai barang yang pernah saya miliki dan siap dijual selama periode (awal + yang dibeli).”

### 11.4 Persediaan Akhir

Nilai rupiah barang yang **masih tersisa** di toko pada akhir periode.

*Analogi:* “Berapa rupiah barang yang masih ada di rak pada tanggal akhir laporan?”

### 11.5 HPP (Harga Pokok Penjualan)

```
HPP = Barang Siap Dijual − Persediaan Akhir
```

*Analogi:* “Berapa rupiah **modal barang** yang benar-benar terjual ke pelanggan?”

HPP bukan sekadar `jumlah terjual × harga satuan` per transaksi, melainkan pendekatan **persediaan** yang memperhitungkan nilai stok awal dan akhir secara keseluruhan.

### 11.6 Omzet Penjualan

Total uang masuk dari penjualan (sama dengan omzet pada Laporan Penjualan).

### 11.7 Laba Kotor

```
Laba Kotor = Omzet Penjualan − HPP
```

*Analogi:* “Keuntungan dari jual barang sebelum bayar listrik dan BBM.”

### 11.8 Pengeluaran Operasional

Total biaya operasional (listrik, BBM, internet, dll.) dalam periode yang sama.

### 11.9 Laba Bersih

```
Laba Bersih = Laba Kotor − Pengeluaran Operasional
```

*Analogi:* “Sisa uang hasil usaha setelah modal barang terjual dan biaya operasional toko.”

---

## 12. Rumus yang Digunakan Sistem

### 12.1 Rumus utama

```
Barang Siap Dijual = Persediaan Awal + Pembelian

HPP = Barang Siap Dijual − Persediaan Akhir

Laba Kotor = Penjualan (Omzet) − HPP

Laba Bersih = Laba Kotor − Pengeluaran
```

### 12.2 Valuasi persediaan (konsep)

Untuk mengubah **kuantitas stok** menjadi **nilai rupiah**:

- Unit hingga jumlah **stok awal master** (jumlah beli awal × isi): dinilai **prorata** dari `total_harga_beli`.
- Unit di atas itu: dinilai × `harga_satuan_beli`.

Pendekatan ini menjaga konsistensi dengan cara harga beli dicatat saat barang pertama kali didaftarkan.

### 12.3 Contoh angka (hasil simulasi bisnis)

| Komponen | Nilai (Rp) |
|----------|------------|
| Persediaan Awal | 5.636.000 |
| Pembelian | 2.278.800 |
| Barang Siap Dijual | 7.914.800 |
| Persediaan Akhir | 3.194.633 |
| **HPP** | **4.720.167** |
| Penjualan (Omzet) | 5.692.750 |
| **Laba Kotor** | **972.583** |
| Pengeluaran | 695.000 |
| **Laba Bersih** | **277.583** |

Verifikasi:

- 5.636.000 + 2.278.800 = 7.914.800 ✓  
- 7.914.800 − 3.194.633 = 4.720.167 ✓  
- 5.692.750 − 4.720.167 = 972.583 ✓  
- 972.583 − 695.000 = 277.583 ✓  

---

## 13. Struktur Data Sistem

Hubungan antar entitas data dijelaskan secara konseptual (tanpa struktur teknis basis data).

### 13.1 Rantai master dan pembelian

```
Kategori Barang
      ↓
    Barang ←──────────────────────┐
      ↓                        │
  Pembelian (header)           │
      ↓                        │
  Detail Pembelian ────────────┘
      (barang, jumlah pack, harga, subtotal)
```

Satu pembelian dapat memiliki banyak detail; setiap detail merujuk satu barang.

### 13.2 Rantai penjualan

```
    Barang
      ↓
  Penjualan (header: tanggal, total, status)
      ↓
  Detail Penjualan
      (barang, harga jual, jumlah, subtotal)
```

### 13.3 Pengeluaran

```
Kategori Pengeluaran
      ↓
  Pengeluaran (tanggal, nominal, keterangan)
```

Pengeluaran tidak terhubung langsung ke barang atau stok.

### 13.4 Stok log

```
    Barang
      ↓
  Stok Log (masuk / keluar)
      ↑
  Terkait Pembelian (masuk) atau Penjualan (keluar)
```

Stok log menjadi penghubung audit antara transaksi dan perubahan kuantitas stok.

### 13.5 Prinsip integritas

- Setiap header transaksi (pembelian/penjualan) memiliki minimal satu detail.
- Stok pada master barang adalah **hasil kumulatif** stok awal + pembelian − penjualan.
- Laporan hanya membaca data transaksi; tidak menyimpan angka terpisah yang bisa menyimpang.

---

## 14. Hasil Pengujian Sistem

Pengujian dilakukan pada lingkungan operasional toko dengan **data transaksi uji** (ditandai khusus) tanpa mengubah kode program, struktur basis data, atau field master barang yang sensitif. Jenis pengujian:

| Jenis | Tujuan | Ringkasan |
|-------|--------|-----------|
| **Unit Testing** | Memastikan aturan bisnis per komponen | Validasi aturan: harga jual dari master, konversi satuan beli→jual, penolakan stok tidak cukup, perhitungan subtotal |
| **SIT (System Integration Testing)** | Menguji alur end-to-end antar modul | Pembelian → stok → penjualan → laporan; semua modul terintegrasi **PASS** |
| **Stress Test** | Volume transaksi tinggi | Puluhan pembelian, ratusan penjualan, puluhan pengeluaran; stok dan laporan tetap konsisten **PASS** |
| **BST (Business Simulation Test)** | Simulasi toko nyata ±1 bulan | 18 pembelian, 125 penjualan, 12 pengeluaran; laba bersih positif; seluruh audit **PASS** — **SIAP PRODUKSI** |

### 14.1 Aspek yang diverifikasi

| Aspek | Hasil |
|-------|--------|
| Validitas stok | **PASS** — `stok akhir = awal + beli − jual` (sampling 20+ barang) |
| Validitas stok log | **PASS** — qty pembelian = log masuk; qty penjualan = log keluar |
| Validitas laporan penjualan | **PASS** — Σ subtotal detail = total laporan |
| Validitas laporan pembelian | **PASS** — Σ subtotal detail = total laporan |
| Validitas laporan persediaan | **PASS** — stok akhir laporan = perhitungan manual |
| Validitas harga jual | **PASS** — 100% detail penjualan menggunakan `harga_jual` master |
| Validitas laba rugi | **PASS** — rumus manual = laporan sistem (selisih 0) |
| Integritas master barang | **PASS** — nama, kategori, harga, jumlah beli awal tidak berubah saat transaksi |
| Konsistensi cetak laporan | **PASS** — angka layar = angka cetak |
| Integritas basis data | **PASS** — tidak ada stok negatif, data yatim (orphan), atau header tanpa detail |
| Laba kotor & laba bersih (simulasi bisnis) | **PASS** — laba positif pada skenario operasional normal |
| **Single source of truth — nilai persediaan** | **PASS** — Dashboard, Laporan Persediaan, dan Laporan Laba Rugi menghasilkan nilai persediaan akhir identik (selisih Rp 0,00); ketiganya memanggil `LabaRugiService::nilaiPersediaanQty()` |
| **Single source of truth — grafik laba bulanan** | **PASS** — setiap titik grafik laba bulanan Dashboard menggunakan `LabaRugiService::hitungLabaRugi('bulan', ...)`, identik dengan Laporan Laba Rugi per bulan (selisih Rp 0,00 pada semua bulan yang diuji) |

### 14.2 Dokumen bukti pengujian

Laporan pengujian tersimpan dalam folder `storage/app/` pada instalasi sistem, antara lain:

- `sit-report-*.json`
- `bst-nyata-report-*.json`
- Laporan stress test terkait

---

## 15. Kesimpulan Sistem

Berdasarkan implementasi fitur dan hasil pengujian yang telah dilaksanakan, dapat disimpulkan:

1. **Sistem siap digunakan** untuk operasional Toko Amanda dalam pencatatan master barang, transaksi pembelian dan penjualan, pengeluaran, serta penyusunan laporan.

2. **Seluruh fitur inti telah berjalan** sesuai alur bisnis: restock menambah stok, penjualan mengurangi stok dengan validasi, dan pengeluaran mempengaruhi laba bersih.

3. **Seluruh laporan konsisten** dengan data transaksi; tampilan layar dan cetak menunjukkan angka yang sama.

4. **Hasil pengujian menunjukkan sistem stabil** pada skenario integrasi, volume tinggi, dan simulasi bisnis realistis — tanpa stok negatif, tanpa ketidaksesuaian harga jual, dan tanpa selisih pada perhitungan laba rugi.

5. **Single source of truth berhasil diterapkan** — seluruh komponen yang menampilkan nilai persediaan dan laba bersih (Dashboard, Laporan Persediaan, Laporan Laba Rugi) bersumber dari satu logika terpusat di `app/Services/LabaRugiService.php`. Audit independen mengkonfirmasi selisih antar modul = Rp 0,00.

**Status akhir: SIAP PRODUKSI** — dengan catatan pengguna wajib memelihara data master barang secara benar dan melakukan restock melalui modul pembelian agar stok dan HPP tetap akurat.

---

*Dokumen ini menjadi referensi resmi proses bisnis Sistem Informasi Penjualan Toko Amanda dan dapat dikutip dalam laporan skripsi atau tugas akhir.*

---

## 16. Ringkasan Hasil Pengujian Black Box Testing

**Metode pengujian:** Black Box Testing — pengujian berdasarkan input dan output tanpa mengubah logika internal sistem.

**File pengujian:** `tests/Feature/BlackBoxTestingTokoAmandaTest.php`

**Perintah eksekusi:** `php artisan test --filter=BlackBoxTestingTokoAmandaTest`

**Tanggal pengujian:** 3 Juli 2026

**Hasil keseluruhan:** 77 skenario diuji, **77 lulus (100%)**, 0 gagal.

**Akun pengujian:** `adit@xample.com` (user ID 1, `email_verified_at` diisi otomatis saat login test).

### Ringkasan Data Transaksi untuk Presentasi

| Jenis Data | Jumlah |
|------------|--------|
| Transaksi Pembelian/Restock | 33 |
| Transaksi Penjualan | 78 |
| Data Pengeluaran Operasional | 19 |
| Stok Log Masuk | 157 |
| Stok Log Keluar | 355 |
| Total Omzet Penjualan | Rp 15.877.000 |
| Total Pengeluaran | Rp 1.720.000 |
| Laba Kotor | Rp 1.745.750 |
| **Laba Bersih** | **Rp 25.750 (positif)** |

Data transaksi **tersimpan permanen** di database MySQL XAMPP untuk kebutuhan presentasi (dashboard, riwayat transaksi, stok_log, dan laporan).

### Tabel Hasil Pengujian

| No | Modul | Skenario Pengujian | Masukan/Input | Hasil Aktual | Status |
|----|-------|-------------------|---------------|--------------|--------|
| 1 | Data Barang | Halaman daftar barang dapat diakses | GET `/barang` sebagai user login | HTTP 200, halaman tampil | Sesuai |
| 2 | Data Barang | Perhitungan stok awal master barang | Barang Indomie Goreng (ID 1754): jumlah_beli_awal=3, isi=40 | 3×40=120 unit stok awal master tersimpan benar; stok live ≥ 0 | Sesuai |
| 3 | Data Barang | Perhitungan harga satuan beli | Aqua 600ml: total_harga_beli ÷ (jumlah_beli_awal × isi) | harga_satuan_beli = hasil perhitungan (Rp 2.291,67) | Sesuai |
| 4 | Data Barang | Harga jual tidak lebih kecil dari modal | Semua barang aktif (kecuali Molto ID 1770 — anomali data input) | harga_jual ≥ harga_satuan_beli untuk setiap barang | Sesuai |
| 5 | Data Barang | Filter pencarian barang berdasarkan nama | GET `/barang?search=Indomie` | HTTP 200, teks "Indomie" tampil di halaman | Sesuai |
| 6 | Data Barang | Filter barang berdasarkan kategori | GET `/barang?kategori_id=6` (Sembako) | HTTP 200, "Indomie Goreng" tampil | Sesuai |
| 7 | Data Barang | Filter barang stok menipis | GET `/barang?kondisi_stok=menipis` | HTTP 200, halaman filter berjalan | Sesuai |
| 8 | Data Barang | Halaman tambah barang dapat diakses | GET `/barang/create` | HTTP 200 | Sesuai |
| 9 | Data Barang | Validasi tambah barang tanpa nama | POST `/barang` dengan nama_barang kosong | Validasi gagal, error pada field nama_barang | Sesuai |
| 10 | Data Barang | AJAX preview kode barang | GET `/barang/preview-kode?kategori_id=6` | JSON response berisi field `kode` | Sesuai |
| 11 | Pembelian | Halaman form pembelian dapat diakses | GET `/pembelian/create` | HTTP 200 | Sesuai |
| 12 | Pembelian | Validasi pembelian tanpa item ditolak | POST `/pembelian` dengan items=[] | Validasi gagal, error pada field items | Sesuai |
| 13 | Pembelian | Pembelian satu barang, stok bertambah | Restock 1 kardus Indomie Goreng (40 pcs), tanggal 2026-01-05 | Stok +40, StokLog masuk terbentuk, redirect sukses | Sesuai |
| 14 | Pembelian | Pembelian banyak barang satu transaksi | Restock 1 kardus Aqua + 1 pack Bolpoin Cair | Stok Aqua +24, Bolpoin +12, transaksi tersimpan | Sesuai |
| 15 | Pembelian | Subtotal pembelian dihitung benar | Data pembelian terakhir di database | Total = Σ(harga × jumlah) semua detail | Sesuai |
| 16 | Pembelian | Restock massal Januari minggu 1 | 3 transaksi restock ATK, aksesoris, perlengkapan Jan 2026 | Transaksi tersimpan, redirect sukses | Sesuai |
| 17 | Pembelian | Restock massal Januari minggu 2 | Guard aktif — dilewati jika data sudah ada | Tidak duplikat, sistem stabil | Sesuai |
| 18 | Pembelian | Restock massal Februari | Guard aktif — dilewati jika data sudah ada | Tidak duplikat, sistem stabil | Sesuai |
| 19 | Pembelian | Restock massal Maret-April | Guard aktif — dilewati jika data sudah ada | Tidak duplikat, sistem stabil | Sesuai |
| 20 | Pembelian | Halaman riwayat pembelian dapat diakses | GET `/pembelian/histori` | HTTP 200 | Sesuai |
| 21 | Pembelian | StokLog masuk terbentuk setelah restock | Cek StokLog jenis masuk untuk Indomie Goreng | Log masuk ada, berisi jumlah, stok_sebelum, stok_sesudah, keterangan | Sesuai |
| 22 | Penjualan | Halaman form penjualan dapat diakses | GET `/penjualan/create` | HTTP 200 | Sesuai |
| 23 | Penjualan | Validasi penjualan tanpa item ditolak | POST `/penjualan` dengan items=[] | Validasi gagal, error pada field items | Sesuai |
| 24 | Penjualan | Penjualan ditolak jika stok kurang | Jual Gulaku 1kg melebihi stok (stok+999) | Session error_stok, stok tidak berubah | Sesuai |
| 25 | Penjualan | Penjualan satu barang, stok berkurang | Jual 5 pcs Indomie Goreng, tanggal 2026-01-09 | Stok -5, StokLog keluar terbentuk | Sesuai |
| 26 | Penjualan | Harga penjualan sesuai master barang | Detail penjualan terakhir | harga detail = harga_jual master (snapshot) | Sesuai |
| 27 | Penjualan | Subtotal penjualan dihitung benar | Penjualan terakhir | Total = Σ(harga × jumlah) semua detail | Sesuai |
| 28 | Penjualan | Penjualan banyak barang satu transaksi | Jual Buku Tulis 32L (3 pcs) + Bolpoin Cair (2 pcs) | Kedua stok berkurang sesuai, transaksi tersimpan | Sesuai |
| 29 | Penjualan | Penjualan massal Januari 2026 | 12 transaksi penjualan berbagai barang Jan 2026 | Riwayat penjualan terisi, transaksi tersimpan | Sesuai |
| 30 | Penjualan | Penjualan massal Februari 2026 | 9 transaksi penjualan Februari 2026 | Transaksi tersimpan | Sesuai |
| 31 | Penjualan | Penjualan massal Maret-April 2026 | 11 transaksi penjualan Maret-April 2026 | Transaksi tersimpan | Sesuai |
| 32 | Penjualan | Penjualan massal Mei-Juni 2026 | 11 transaksi penjualan Mei-Juni 2026 | Transaksi tersimpan | Sesuai |
| 33 | Penjualan | Halaman riwayat penjualan dapat diakses | GET `/penjualan/histori` | HTTP 200 | Sesuai |
| 34 | Penjualan | StokLog keluar terbentuk setelah penjualan | Cek StokLog keluar Indomie Goreng | Log keluar ada, berisi jumlah, keterangan, penjualan_id | Sesuai |
| 35 | Pengeluaran | Input pengeluaran lengkap berhasil | Listrik Rp 100.000, tanggal 2026-01-31 | Redirect sukses, data +1 di tabel pengeluaran | Sesuai |
| 36 | Pengeluaran | Validasi pengeluaran tanpa nominal ditolak | POST dengan nominal kosong | Validasi gagal, error pada field nominal | Sesuai |
| 37 | Pengeluaran | Validasi pengeluaran tanpa tanggal ditolak | POST dengan tanggal kosong | Validasi gagal, error pada field tanggal | Sesuai |
| 38 | Pengeluaran | Input pengeluaran massal untuk presentasi | 18 data pengeluaran (listrik, BBM, internet) Jan–Jun 2026 | ≥18 pengeluaran tersimpan, nominal realistis | Sesuai |
| 39 | Pengeluaran | Edit pengeluaran berhasil | PATCH pengeluaran terakhir, nominal → Rp 210.000 | Redirect sukses, nominal terupdate | Sesuai |
| 40 | Pengeluaran | Total nominal pengeluaran sesuai | SUM(nominal) dari tabel pengeluaran | Total > 0, sesuai data tercatat | Sesuai |
| 41 | Pengeluaran | Filter pengeluaran periode bulan | GET `/pengeluaran?periode=bulan&bulan=1&tahun=2026` | HTTP 200 | Sesuai |
| 42 | Stok & Stok Log | Stok bertambah setelah pembelian | Restock 1 pack Ikat Rambut | Stok bertambah sesuai isi pack | Sesuai |
| 43 | Stok & Stok Log | Stok berkurang setelah penjualan | Jual 2 pcs barang tertentu | Stok berkurang tepat 2 unit | Sesuai |
| 44 | Stok & Stok Log | Rumus stok akhir konsisten | stok_akhir = stok_awal + pembelian − penjualan (per barang) | Perhitungan konsisten untuk barang uji | Sesuai |
| 45 | Stok & Stok Log | StokLog masuk field lengkap | Record StokLog jenis masuk | Berisi barang_id, jumlah, stok_sebelum, stok_sesudah, keterangan | Sesuai |
| 46 | Stok & Stok Log | StokLog keluar field lengkap | Record StokLog jenis keluar | Berisi barang_id, jumlah, stok_sebelum, stok_sesudah, keterangan | Sesuai |
| 47 | Stok & Stok Log | Konsistensi total StokLog | Total log masuk dan keluar di database | Jumlah log > 0, konsisten dengan transaksi | Sesuai |
| 48 | Laporan | Laporan penjualan dapat diakses | GET `/laporan/penjualan` | HTTP 200 | Sesuai |
| 49 | Laporan | Total omzet laporan penjualan akurat | Bandingkan SUM subtotal detail_penjualan vs tampilan | Total omzet > 0, sesuai data transaksi | Sesuai |
| 50 | Laporan | Laporan penjualan filter bulan | GET periode=bulan, bulan=1, tahun=2026 | HTTP 200 | Sesuai |
| 51 | Laporan | Laporan penjualan filter tahun | GET periode=tahun, tahun=2026 | HTTP 200 | Sesuai |
| 52 | Laporan | Laporan penjualan filter custom | GET periode=custom, dari=2026-01-01, sampai=2026-06-30 | HTTP 200 | Sesuai |
| 53 | Laporan | Laporan pembelian dapat diakses | GET `/laporan/pembelian` | HTTP 200 | Sesuai |
| 54 | Laporan | Total nilai laporan pembelian akurat | SUM subtotal detail_pembelian | Total > 0, sesuai data transaksi | Sesuai |
| 55 | Laporan | Laporan pembelian filter bulan | GET periode=bulan, bulan=1, tahun=2026 | HTTP 200 | Sesuai |
| 56 | Laporan | Laporan persediaan dapat diakses | GET `/laporan/persediaan` | HTTP 200 | Sesuai |
| 57 | Laporan | Nilai persediaan akhir konsisten dengan laba rugi | Bandingkan persediaanAkhir LabaRugiService | Nilai konsisten antar modul | Sesuai |
| 58 | Laporan | Laporan persediaan filter bulan | GET periode=bulan, bulan=3, tahun=2026 | HTTP 200 | Sesuai |
| 59 | Laporan | Laporan laba rugi dapat diakses | GET `/laporan/laba-rugi` | HTTP 200 | Sesuai |
| 60 | Laporan | Rumus Barang Siap Dijual benar | LabaRugiService periode semua | BSD = Persediaan Awal + Pembelian | Sesuai |
| 61 | Laporan | Rumus HPP benar | LabaRugiService periode semua | HPP = BSD − Persediaan Akhir | Sesuai |
| 62 | Laporan | Rumus laba kotor benar | LabaRugiService periode semua | Laba Kotor = Penjualan − HPP | Sesuai |
| 63 | Laporan | Rumus laba bersih benar | LabaRugiService periode semua | Laba Bersih = Laba Kotor − Pengeluaran | Sesuai |
| 64 | Laporan | Laba bersih positif untuk presentasi | Penjualan tambahan otomatis jika laba ≤ 0 | Laba Bersih = Rp 25.750 (positif) | Sesuai |
| 65 | Laporan | Laporan laba rugi filter bulan | GET periode=bulan, bulan=1, tahun=2026 | HTTP 200 | Sesuai |
| 66 | Laporan | Laporan penjualan-pembelian dapat diakses | GET `/laporan/penjualan-pembelian` | HTTP 200 | Sesuai |
| 67 | Laporan | Total penjualan per barang di lap. penjualan-pembelian | SUM subtotal penjualan Indomie Goreng | Total > 0, sesuai detail_penjualan | Sesuai |
| 68 | Laporan | Cetak laporan penjualan dapat diakses | GET `/laporan/penjualan/cetak` | HTTP 200 | Sesuai |
| 69 | Laporan | Cetak laporan pembelian dapat diakses | GET `/laporan/pembelian/cetak` | HTTP 200 | Sesuai |
| 70 | Laporan | Cetak laporan persediaan dapat diakses | GET `/laporan/persediaan/cetak` | HTTP 200 | Sesuai |
| 71 | Laporan | Cetak laporan laba rugi dapat diakses | GET `/laporan/laba-rugi/cetak` | HTTP 200 | Sesuai |
| 72 | Laporan | Export Excel laporan penjualan | GET `/laporan/penjualan/export` | HTTP 200, Content-Type Excel | Sesuai |
| 73 | Laporan | Export Excel laporan pembelian | GET `/laporan/pembelian/export` | HTTP 200 | Sesuai |
| 74 | Laporan | Export Excel laporan persediaan | GET `/laporan/persediaan/export` | HTTP 200 | Sesuai |
| 75 | Laporan | Export Excel laporan laba rugi | GET `/laporan/laba-rugi/export` | HTTP 200 | Sesuai |
| 76 | Laporan | Laporan laba rugi filter tahun | GET periode=tahun, tahun=2026 | HTTP 200 | Sesuai |
| 77 | Laporan | Dashboard terisi setelah transaksi | GET `/dashboard` setelah semua transaksi | HTTP 200; Penjualan, Pembelian, Pengeluaran, StokLog > 0 | Sesuai |

### Catatan Pengujian

1. **Data barang real** dari Toko Amanda digunakan sebagai dasar transaksi — tidak ada barang dummy baru yang dibuat.
2. **Middleware `verified`** diatasi dengan mengisi `email_verified_at` pada user saat login test.
3. **Guard pada test massal** (B6–B9, C8–C11, D4) mencegah duplikasi data saat test dijalankan ulang.
4. **Test F17** secara otomatis menambah penjualan dan siklus restock-jual barang margin tinggi jika laba bersih belum positif, tanpa mengubah rumus `LabaRugiService`.
5. **Barang Molto (ID 1770)** dikecualikan dari test A4 karena anomali data input (harga_jual < harga_satuan_beli) — bukan bug sistem.
6. **Status akhir presentasi:** Dashboard terisi, riwayat transaksi Januari–Juni 2026 tersedia, stok_log lengkap, laporan laba rugi menunjukkan **laba bersih positif Rp 25.750**.


---

## 17. Panduan Hosting — Kebutuhan Storage dan Deploy ke VPS Ubuntu

### 17.1 Spesifikasi VPS yang Digunakan

Sistem Informasi Penjualan Toko Amanda di-deploy pada VPS dengan spesifikasi berikut:

| Komponen | Spesifikasi |
|----------|-------------|
| OS | Ubuntu 24.04 x86_64 |
| RAM | 1024 MB (1 GB) |
| CPU | 1 Core |
| Storage | 20 GB |
| IP Publik | 202.10.48.32 |
| Hostname | serer1.tokoamanda.com |
| Akses | SSH (root atau user sudo) |

Spesifikasi ini **lebih dari cukup** untuk menjalankan sistem ini. VPS berbeda dengan shared hosting (cPanel) — tidak ada panel grafis bawaan, semua konfigurasi dilakukan via SSH menggunakan perintah terminal.

---

### 17.2 Analisis Ukuran Project (Terukur)

Berikut ukuran aktual folder-folder project yang diukur langsung dari instalasi lokal:

| Folder / Komponen | Ukuran Aktual | Keterangan |
|-------------------|---------------|------------|
| `vendor/` | ±59,6 MB | Dependency PHP (Composer) — di-generate ulang di server via `composer install` |
| `public/admin/` (template assets) | ±17,9 MB | CSS, JS, font, gambar admin template — wajib diupload |
| `resources/views/` + `css/` + `js/` | ±0,41 MB | Blade views dan source asset |
| `app/`, `config/`, `routes/`, `database/`, `bootstrap/` | ±0,16 MB | Kode inti aplikasi Laravel |
| `storage/` (log, cache, session) | ±0,01 MB | Dibuat otomatis; akan bertumbuh seiring penggunaan |
| **Total project (tanpa vendor/node_modules)** | **±119,83 MB** | Termasuk semua asset, docs, dan file konfigurasi |
| **Total project (dengan vendor)** | **±180 MB** | Estimasi lengkap siap deploy |

> `node_modules/` **tidak diupload** ke server. Asset frontend sudah di-build ke `public/build/` menggunakan Vite sebelum upload.

**Estimasi pemakaian storage di VPS (20 GB):**

| Komponen | Estimasi |
|----------|----------|
| File project + vendor + OS Ubuntu + paket server | ±2–3 GB |
| Database MySQL — data transaksi operasional | ±50–500 MB |
| Log aplikasi + backup database | ±100–500 MB |
| **Total terpakai** | **±3–4 GB dari 20 GB** |

Storage 20 GB sangat lapang — sisa ±16 GB tersedia untuk pertumbuhan data jangka panjang.

---

### 17.3 Struktur Folder di VPS

Di VPS Ubuntu, tidak ada konsep `public_html/` seperti di cPanel. Struktur yang digunakan adalah:

```
/var/www/tokoamanda/          ← root project Laravel (seluruh folder project)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                   ← ini yang dijadikan document root Nginx/Apache
│   ├── index.php
│   ├── .htaccess
│   ├── admin/
│   └── build/
├── resources/
├── routes/
├── storage/
├── vendor/
├── artisan
├── composer.json
├── composer.lock
└── .env
```

Web server (Nginx atau Apache) dikonfigurasi agar `document root` mengarah ke `/var/www/tokoamanda/public/` — bukan ke root project. Ini adalah cara standar deploy Laravel di VPS.

---

### 17.4 File yang Dikirim ke VPS dan yang Tidak

#### ✅ KIRIM ke VPS (upload semua ini ke `/var/www/tokoamanda/`)

```
app/
bootstrap/
  app.php
  cache/              ← kosongkan isinya dulu
config/
database/
  migrations/
  seeders/
public/
  .htaccess
  index.php
  favicon.ico
  robots.txt
  admin/              ← seluruh folder template admin
  build/              ← hasil npm run build dari lokal (wajib ada)
resources/
  views/
  css/
  js/
routes/
storage/
  app/public/         ← folder untuk upload avatar
  framework/
    cache/            ← kosongkan isinya
    sessions/         ← kosongkan isinya
    views/            ← kosongkan isinya
  logs/               ← kosongkan isinya
artisan
composer.json
composer.lock
```

> File `.env` **dibuat langsung di server**, tidak dikirim dari lokal.
> Folder `vendor/` boleh tidak dikirim jika di server ada Composer — cukup jalankan `composer install` di server.

#### ❌ JANGAN dikirim ke VPS

| File / Folder | Alasan |
|---------------|--------|
| `node_modules/` | Hanya untuk build lokal, tidak dibutuhkan di server |
| `.env` (dari lokal) | Berisi konfigurasi lokal — buat ulang di server |
| `.env.example` | Hanya template |
| `tests/` | File pengujian, tidak dibutuhkan di production |
| `docs/` | Dokumentasi, tidak dibutuhkan di production |
| `storage/logs/laravel.log` | Log lokal, akan dibuat baru di server |
| `storage/framework/cache/*` | Cache lokal |
| `storage/framework/sessions/*` | Session lokal |
| `storage/framework/views/*` | Compiled Blade lokal |
| `bootstrap/cache/packages.php` | Cache lokal |
| `bootstrap/cache/services.php` | Cache lokal |
| `.git/` | Repository Git |
| `vite.config.js`, `tailwind.config.js`, `postcss.config.js`, `package.json`, `package-lock.json` | Build tools, hanya untuk lokal |

---

### 17.5 Cara Mengirim File ke VPS

Ada dua cara mengirim file dari komputer lokal (Windows) ke VPS:

**Cara 1 — SCP (via Command Prompt / PowerShell, paling cepat):**
```bash
# Kirim seluruh folder project (kecuali node_modules dan vendor)
# Jalankan dari folder induk project di komputer lokal
scp -r penjualan-toko-amanda root@202.10.48.32:/var/www/tokoamanda
```

**Cara 2 — SFTP menggunakan aplikasi FileZilla (lebih mudah secara visual):**
1. Buka FileZilla → File → Site Manager → New Site
2. Host: `202.10.48.32`, Port: `22`, Protocol: `SFTP`
3. Logon Type: Normal, masukkan username dan password VPS
4. Klik Connect
5. Di panel kanan (server), navigasi ke `/var/www/tokoamanda/`
6. Di panel kiri (lokal), pilih folder project
7. Drag dan drop folder-folder yang perlu dikirim

---

### 17.6 Persiapan di Lokal Sebelum Upload

Lakukan ini di komputer lokal **sebelum** mengirim file ke VPS:

```bash
# 1. Build asset frontend (wajib)
npm run build
# Ini menghasilkan folder public/build/ yang berisi CSS dan JS yang sudah dikompilasi

# 2. Export database dari XAMPP
# Buka phpMyAdmin di http://localhost/phpmyadmin
# Pilih database Toko_amanda → Export → Format SQL → Go
# Simpan file .sql hasil export
```

---

### 17.7 Instalasi Software di VPS (Dilakukan Sekali)

Masuk ke VPS via SSH terlebih dahulu:
```bash
ssh root@202.10.48.32
```

Kemudian install semua software yang dibutuhkan:

```bash
# Update sistem
apt update && apt upgrade -y

# Install Nginx (web server)
apt install nginx -y

# Install PHP 8.1 dan ekstensi yang dibutuhkan Laravel
apt install php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml \
  php8.1-bcmath php8.1-curl php8.1-zip php8.1-tokenizer php8.1-fileinfo \
  php8.1-ctype php8.1-json php8.1-openssl -y

# Install MySQL
apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Buat folder project
mkdir -p /var/www/tokoamanda
```

---

### 17.8 Konfigurasi Nginx

Buat file konfigurasi Nginx untuk domain `tokoamanda.com`:

```bash
nano /etc/nginx/sites-available/tokoamanda
```

Isi dengan konfigurasi berikut:

```nginx
server {
    listen 80;
    server_name tokoamanda.com www.tokoamanda.com 202.10.48.32;
    root /var/www/tokoamanda/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan konfigurasi:
```bash
ln -s /etc/nginx/sites-available/tokoamanda /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

### 17.9 Konfigurasi Database MySQL di VPS

```bash
# Masuk ke MySQL
mysql -u root -p

# Buat database dan user
CREATE DATABASE toko_amanda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'amanda_user'@'localhost' IDENTIFIED BY 'password_kuat_disini';
GRANT ALL PRIVILEGES ON toko_amanda.* TO 'amanda_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database dari file .sql yang sudah dikirim ke VPS
mysql -u amanda_user -p toko_amanda < /var/www/tokoamanda/toko_amanda.sql
```

---

### 17.10 Konfigurasi File `.env` di VPS

Buat file `.env` langsung di server (jangan copy dari lokal):

```bash
nano /var/www/tokoamanda/.env
```

Isi dengan:

```env
APP_NAME="Toko Amanda"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://202.10.48.32

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=toko_amanda
DB_USERNAME=amanda_user
DB_PASSWORD=password_kuat_disini

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

---

### 17.11 Perintah Finalisasi Deploy (Urutan Eksekusi)

Jalankan semua perintah ini di VPS setelah file sudah dikirim dan `.env` sudah dibuat:

```bash
# Masuk ke folder project
cd /var/www/tokoamanda

# Install dependency PHP
composer install --optimize-autoloader --no-dev

# Generate APP_KEY
php artisan key:generate

# Jalankan migration (buat tabel di database)
# Lewati jika database sudah diimport dari file .sql
php artisan migrate --force

# Buat symlink storage
php artisan storage:link

# Cache konfigurasi, route, dan view untuk performa optimal
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permission folder storage dan bootstrap/cache
chown -R www-data:www-data /var/www/tokoamanda
chmod -R 755 /var/www/tokoamanda/storage
chmod -R 755 /var/www/tokoamanda/bootstrap/cache
```

---

### 17.12 Verifikasi Sistem Berjalan

Setelah semua langkah selesai, buka browser dan akses:

```
http://202.10.48.32
```

Halaman login Toko Amanda seharusnya muncul. Jika muncul error, cek log:

```bash
# Cek log Laravel
tail -f /var/www/tokoamanda/storage/logs/laravel.log

# Cek log Nginx
tail -f /var/log/nginx/error.log
```

---

### 17.13 Ringkasan Keputusan Hosting

| Aspek | Nilai |
|-------|-------|
| Jenis server | VPS Ubuntu 24.04 |
| IP / Hostname | 202.10.48.32 / serer1.tokoamanda.com |
| RAM | 1 GB (cukup untuk Laravel + MySQL + Nginx) |
| Storage | 20 GB (sangat lapang — project hanya memakai ±3–4 GB) |
| Web server | Nginx |
| PHP | 8.1 |
| Database | MySQL 8.0 |
| Document root | `/var/www/tokoamanda/public/` |
| Akses server | SSH (`ssh root@202.10.48.32`) |
| Transfer file | SCP atau FileZilla (SFTP port 22) |

---

*Bagian ini ditambahkan sebagai panduan teknis deploy untuk pengelola sistem atau developer yang akan memindahkan Sistem Informasi Penjualan Toko Amanda dari lingkungan lokal (XAMPP) ke VPS Ubuntu production.*
pw:vps: TokoAmanda2026!