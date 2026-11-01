# Spesifikasi UML dan ERD Sistem Informasi Penjualan dan Stok Barang Toko Amanda

**Versi:** 1.0  
**Tanggal pemindaian:** Juni 2026  
**Sumber analisis:** Kode project Laravel + `docs/DOKUMENTASI_SISTEM_TOKO_AMANDA.md` (v1.3)  
**Catatan metode:** Spesifikasi ini disusun berdasarkan implementasi yang benar-benar ada di repository. Fitur yang tidak ditemukan di kode diberi label **perlu verifikasi** atau **tidak ditemukan**.

---

## 1. Ringkasan Hasil Pemindaian Project

### 1.1 Framework dan Teknologi

| Aspek | Implementasi |
|-------|----------------|
| Framework | Laravel **10.x** (`composer.json`: `"laravel/framework": "^10.10"`) |
| PHP | ^8.1 |
| Autentikasi | Laravel Breeze (login, register, reset password, verifikasi email — register khusus role pemilik) |
| Ekspor Excel | Maatwebsite Excel (`maatwebsite/excel`) |
| Frontend | Blade + Bootstrap + Chart.js (dashboard) |

### 1.2 Modul Utama yang Ditemukan

| Modul | Controller | Keterangan |
|-------|------------|------------|
| Autentikasi | `Auth\*Controller` | Login, register (khusus role pemilik), logout, reset password, verifikasi email |
| Dashboard | `DashboardController` | Agregasi omzet, laba bersih, grafik, barang terlaris, stok menipis |
| Master Kategori Barang | `KategoriController` | CRUD kategori + kode prefix + default satuan |
| Master Barang | `BarangController` | CRUD, import/export Excel, template, preview kode AJAX |
| Pembelian / Restock | `PembelianController` | Create, store, histori |
| Penjualan | `PenjualanController` | Create, store, histori |
| Kategori Pengeluaran | `KategoriPengeluaranController` | Index, create, store, edit, update (tanpa delete) |
| Pengeluaran | `PengeluaranController` | Index, create, store, edit, update (tanpa delete) |
| Laporan | `LaporanController` | Penjualan, pembelian, persediaan, laba rugi, penjualan-pembelian + cetak + export |
| Pengaturan Akun | `AccountController` | Edit profil, password, avatar, hapus akun |
| Perhitungan Laba Rugi | `LabaRugiService` | Service terpusat untuk laba rugi dan valuasi persediaan |

### 1.3 Aktor / Pengguna

| Aktor | Status |
|-------|--------|
| **Pemilik Toko (Owner)** | Pemilik sistem yang dapat melihat dashboard, memantau riwayat transaksi, mengakses semua laporan keuangan (termasuk laba rugi), dan mengelola data pengguna (CRUD user). |
| **Admin** | Pengelola operasional toko yang dapat melihat dashboard, mengelola data kategori & barang, mencatat pembelian/restock & pengeluaran, serta melihat riwayat pembelian/penjualan. |
| **Kasir** | Petugas penjualan yang khusus mencatat transaksi penjualan, melihat riwayat penjualan, dan mengelola profil pribadi. |
| **Sistem** | Aktor pendukung (validasi, perhitungan, pencatatan stok log otomatis, dan pengecekan otorisasi middleware). |

**Diterapkan** di kode: Middleware role dan otorisasi hak akses berbasis peran (`pemilik`, `admin`, `kasir`) di file `routes/web.php` dan `app/Models/User.php`.

### 1.4 Tabel Database yang Ditemukan

**Tabel bisnis inti:**

| Tabel | Model Eloquent | Relasi ke `users` |
|-------|----------------|-------------------|
| `users` | `User` | — |
| `kategori` | `Kategori` | — |
| `barang` | `Barang` | — |
| `pembelian` | `Pembelian` | `user_id` nullable (SET NULL) |
| `detail_pembelian` | `DetailPembelian` | — |
| `penjualan` | `Penjualan` | `user_id` (RESTRICT) |
| `detail_penjualan` | `DetailPenjualan` | — |
| `kategori_pengeluaran` | `KategoriPengeluaran` | — |
| `pengeluaran` | `Pengeluaran` | `user_id` (RESTRICT) |
| `stok_log` | `StokLog` | `user_id` (RESTRICT) |

**Tabel pendukung Laravel (infrastruktur):**

| Tabel | Fungsi |
|-------|--------|
| `password_reset_tokens` | Reset password |
| `personal_access_tokens` | Sanctum API token |
| `failed_jobs` | Queue gagal |

**Tidak ada tabel:** laporan penjualan, laporan pembelian, laporan persediaan, laporan laba rugi (semua dihitung dinamis).

**Catatan `pembelian.user_id`:** kolom dibuat nullable dengan onDelete SET NULL — data pembelian lama tetap aman dengan nilai NULL, dan jika user dihapus data pembelian tidak ikut terhapus.

### 1.5 Relasi Utama Antar Tabel

```
kategori (1) ──< (N) barang
barang (1) ──< (N) detail_pembelian
pembelian (1) ──< (N) detail_pembelian
barang (1) ──< (N) detail_penjualan
penjualan (1) ──< (N) detail_penjualan
users (1) ──< (N) penjualan
users (1) ──< (N) pengeluaran
users (1) ──< (N) stok_log
users (0..1) ──< (N) pembelian  ← nullable, SET NULL
kategori_pengeluaran (1) ──< (N) pengeluaran
barang (1) ──< (N) stok_log
penjualan (0..1) ──< (N) stok_log (nullable FK, khusus jenis keluar)
```

### 1.6 Alur Bisnis Utama

```
Master Barang (stok awal)
    → Pembelian/Restock (+stok, stok_log masuk)
    → Penjualan (−stok, stok_log keluar)
    → Pengeluaran operasional (laba bersih)
    → Laporan (read-only, agregasi dinamis)
```

### 1.7 Daftar File Penting (Dasar Analisis)

| Kategori | File |
|----------|------|
| Route | `routes/web.php`, `routes/auth.php` |
| Controller | `DashboardController.php`, `BarangController.php`, `KategoriController.php`, `PembelianController.php`, `PenjualanController.php`, `PengeluaranController.php`, `KategoriPengeluaranController.php`, `LaporanController.php`, `AccountController.php`, `Auth/AuthenticatedSessionController.php` |
| Model | `User.php`, `Kategori.php`, `Barang.php`, `Pembelian.php`, `DetailPembelian.php`, `Penjualan.php`, `DetailPenjualan.php`, `KategoriPengeluaran.php`, `Pengeluaran.php`, `StokLog.php` |
| Service | `app/Services/LabaRugiService.php` |
| Export | `app/Exports/LaporanPenjualanExport.php`, `LaporanPembelianExport.php`, `LaporanPersediaanExport.php`, `LaporanPenjualanPembelianExport.php`, `LaporanLabaRugiExport.php`, `BarangExport.php`, `BarangTemplateExport.php` |
| Import | `app/Imports/BarangImport.php` |
| Migration | `database/migrations/*.php` (29 file) |
| View utama | `resources/views/dashboard.blade.php`, `master/barang/*`, `master/kategori/*`, `transaksi/*`, `pengeluaran/*`, `laporan/*`, `account/*`, `auth/*` |
| Dokumentasi | `docs/DOKUMENTASI_SISTEM_TOKO_AMANDA.md` |
| Seeder | `database/seeders/DatabaseSeeder.php` (kosong — tidak ada data seed aktif) |

---

## 2. Aktor Sistem

### 2.1 Aktor Manusia

**1. Pemilik Toko (Owner)**
- Memantau performa toko melalui Dashboard.
- Mengakses semua Laporan Keuangan (Penjualan, Pembelian, Persediaan, Laba Rugi, Perbandingan Penjualan-Pembelian).
- Mengelola data user/pengguna aplikasi (CRUD User).
- Melihat riwayat transaksi (Penjualan & Pembelian/Restock).
- Dilarang mencatat transaksi (Penjualan, Pembelian, Pengeluaran) atau mengelola data master (Barang & Kategori).

**2. Admin**
- Memantau performa toko melalui Dashboard.
- Mengelola data master barang dan kategori barang.
- Mencatat transaksi pembelian/restock barang & pengeluaran operasional.
- Melihat riwayat transaksi (Penjualan & Pembelian).
- Dilarang mengakses menu Kelola User dan menu Laporan Keuangan.

**3. Kasir**
- Mencatat transaksi penjualan barang.
- Melihat riwayat penjualan.
- Mengelola pengaturan profil pribadi (avatar, nama, password).
- Dilarang mengakses Dashboard, master data barang/kategori, transaksi pembelian, pengeluaran, kelola user, dan laporan keuangan.

### 2.2 Aktor Sistem

**Sistem (otomatis)**
- Validasi form (Form Request + validasi inline controller).
- Pengecekan otorisasi peran via middleware (`RoleMiddleware`).
- Perhitungan subtotal, total transaksi, stok, HPP, laba rugi.
- Pencatatan `stok_log` otomatis saat pembelian/penjualan.
- Agregasi laporan dan dashboard dari query database.

### 2.3 Role Lain (Admin/Kasir)

| Role | Status |
|------|--------|
| Pemilik (Owner) | **Aktif** |
| Admin | **Aktif** |
| Kasir | **Aktif** |

**Kesimpulan:** Diagram use case menggunakan **tiga aktor manusia** dengan pembatasan hak akses yang jelas berbasis middleware.

### 2.4 Verifikasi Email

- Route verifikasi email **ada** (`routes/auth.php`, middleware `verified` pada route bisnis).
- Model `User` **tidak** mengimplementasikan `MustVerifyEmail` (di-comment).
- **Perlu verifikasi** apakah middleware `verified` benar-benar memblokir user yang belum verifikasi email di lingkungan produksi.

---

## 3. Use Case Diagram

### 3.1 Batas Sistem

**Sistem Informasi Penjualan dan Stok Barang Berbasis Web — Toko Amanda**

Mencakup modul web di dalam middleware `auth` + `verified`. Di luar batas: payment gateway eksternal, integrasi supplier, aplikasi mobile terpisah.

### 3.2 Daftar Aktor

| Aktor | Tipe |
|-------|------|
| Pemilik/Pengelola Toko | Primary |
| Sistem | Secondary (opsional di diagram) |

### 3.3 Relasi Include / Extend (Rekomendasi)

| Relasi | Use Case Parent | Use Case Child | Alasan |
|--------|-----------------|----------------|--------|
| `<<include>>` | Semua use case setelah login | Login | Akses modul memerlukan autentikasi |
| `<<extend>>` | Melihat Laporan X | Mencetak Laporan X | Cetak opsional setelah melihat |
| `<<extend>>` | Melihat Laporan X | Mengekspor Laporan X | Export opsional setelah melihat |

### 3.4 Daftar Use Case Lengkap

| No | Use Case | Aktor | Deskripsi | Route / Controller | Ditemukan |
|----|----------|-------|-----------|-------------------|-----------|
| UC-01 | Login | Semua Aktor | Masuk ke sistem dengan email & password | `POST /login` → `AuthenticatedSessionController@store` | **Ya** |
| UC-02 | Register (Pemilik) | Pemilik | Mendaftar akun baru dengan peran Pemilik | `GET/POST /register` → `RegisteredUserController` | **Ya** |
| UC-03 | Verifikasi Email | Semua Aktor | Memverifikasi alamat email | `routes/auth.php` → `VerifyEmailController` | **Ya** |
| UC-04 | Reset Password | Semua Aktor | Lupa password via email | `PasswordResetLinkController`, `NewPasswordController` | **Ya** |
| UC-05 | Logout | Semua Aktor | Keluar dari sistem | `POST /logout` → `AuthenticatedSessionController@destroy` | **Ya** |
| UC-06 | Melihat Dashboard | Pemilik, Admin | Monitoring omzet, laba, grafik, stok menipis | `GET /dashboard` → `DashboardController@index` | **Ya** |
| UC-07 | Mengelola Kategori Barang | Admin | CRUD kategori barang | `Route::resource('kategori')` → `KategoriController` | **Ya** |
| UC-08 | Mengelola Data Barang | Admin | CRUD master barang + stok awal | `Route::resource('barang')` → `BarangController` | **Ya** |
| UC-09 | Import Barang | Admin | Import Excel ke master barang | `POST barang/import` → `BarangController@import` | **Ya** |
| UC-10 | Export Barang | Admin | Export master barang ke Excel | `GET barang/export` → `BarangController@export` | **Ya** |
| UC-11 | Download Template Barang | Admin | Template import barang | `GET barang/template` → `BarangController@template` | **Ya** |
| UC-12 | Mencatat Pembelian/Restock | Admin | Restock barang, tambah stok | `POST pembelian` → `PembelianController@store` | **Ya** |
| UC-13 | Melihat Riwayat Pembelian | Pemilik, Admin | Histori transaksi pembelian | `GET pembelian/histori` → `PembelianController@histori` | **Ya** |
| UC-14 | Mencatat Penjualan | Admin, Kasir | Transaksi penjualan ke pelanggan | `POST penjualan` → `PenjualanController@store` | **Ya** |
| UC-15 | Melihat Riwayat Penjualan | Pemilik, Admin, Kasir | Histori transaksi penjualan | `GET penjualan/histori` → `PenjualanController@histori` | **Ya** |
| UC-16 | Mengelola Kategori Pengeluaran | Admin | CRUD kategori biaya operasional | `kategori-pengeluaran` resource → `KategoriPengeluaranController` | **Ya** (tanpa delete) |
| UC-17 | Mencatat Pengeluaran Operasional | Admin | Input biaya listrik, BBM, dll. | `pengeluaran` resource → `PengeluaranController` | **Ya** |
| UC-18 | Melihat Stok Log | Pemilik, Admin | UI riwayat pergerakan stok | — | **Tidak** — data `stok_log` tercatat otomatis, **tidak ada menu/route/view** |
| UC-19 | Melihat Laporan Penjualan | Pemilik | Laporan omzet per periode | `laporan.penjualan` → `LaporanController@penjualan` | **Ya** |
| UC-20 | Melihat Laporan Pembelian | Pemilik | Laporan nilai pembelian | `laporan.pembelian` → `LaporanController@pembelian` | **Ya** |
| UC-21 | Melihat Laporan Persediaan | Pemilik | Pergerakan stok per barang | `laporan.persediaan` → `LaporanController@persediaan` | **Ya** |
| UC-22 | Melihat Laporan Laba Rugi | Pemilik | PA, HPP, laba kotor, laba bersih | `laporan.laba-rugi` → `LaporanController@labaRugi` | **Ya** |
| UC-23 | Melihat Laporan Penjualan-Pembelian | Pemilik | Rekap per barang | `laporan.penjualan-pembelian` → `LaporanController@penjualanPembelian` | **Ya** |
| UC-24 | Mencetak Laporan | Pemilik | Cetak PDF/browser print | `laporan.*.cetak` → `LaporanController@cetak*` | **Ya** (5 jenis laporan) |
| UC-25 | Mengekspor Laporan Excel | Pemilik | Download .xlsx | `laporan.*.export` → `LaporanController@exportExcel*` | **Ya** (5 jenis laporan) |
| UC-26 | Mengatur Profil/Akun | Semua Aktor | Edit nama, email, password, avatar (tanpa Hapus Akun) | `account.*` → `AccountController` | **Ya** (Tombol hapus akun di-disable) |
| UC-27 | Preview Kode Barang (AJAX) | Admin | Preview kode otomatis per kategori | `GET barang/preview-kode` → `BarangController@previewKode` | **Ya** |
| UC-28 | Mengelola Pengguna (User) | Pemilik | CRUD pengguna aplikasi | `Route::resource('users')` → `UserController` | **Ya** |

### 3.5 Paket Use Case (Grouping untuk Diagram)

```
[Autentikasi]        UC-01 s/d UC-05
[Dashboard]          UC-06
[Master Data]        UC-07 s/d UC-11, UC-27
[Transaksi]          UC-12 s/d UC-15
[Pengeluaran]        UC-16, UC-17
[Laporan]            UC-19 s/d UC-25
[Pengaturan Akun]    UC-26
[Kelola User]        UC-28
[Stok Log - backend] UC-18 (otomatis, tanpa UI)
```

---

## 4. Sequence Diagram

### 4.1 Sequence 1 — Login Pengguna

**Lifeline:** Pemilik/Pengelola → Browser → `AuthenticatedSessionController` → `LoginRequest` → `User` (Model/DB) → Session

| Step | Pesan | Keterangan |
|------|-------|------------|
| 1 | GET `/login` | Tampilkan form (`AuthenticatedSessionController@create`) |
| 2 | POST `/login` (email, password) | `AuthenticatedSessionController@store` |
| 3 | Validasi | `LoginRequest` — rate limit, credentials |
| 4 | Autentikasi | `Auth::attempt()` terhadap tabel `users` |
| 5 | Regenerasi session | `session()->regenerate()` |
| 6 | Redirect | Ke `/dashboard` (`RouteServiceProvider::HOME`) |

**Validasi gagal:** kembali ke form dengan error.

**File:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, `app/Http/Requests/Auth/LoginRequest.php`

---

### 4.2 Sequence 2 — Mengelola Data Barang

**Contoh alur: Tambah Barang**

**Lifeline:** Admin → Browser → `BarangController` → `StoreBarangRequest` → `Kategori` → `Barang` → DB

| Step | Pesan |
|------|-------|
| 1 | GET `/barang/create` → `BarangController@create` — load kategori |
| 2 | POST `/barang` — input: nama, kategori, tanggal_stok_awal, jumlah_beli, satuan, isi, total_harga_beli, persentase, harga_jual |
| 3 | Validasi `StoreBarangRequest` |
| 4 | Cek `kode_prefix` kategori |
| 5 | `generateKodeBarang()` |
| 6 | `hitungHarga()` → harga_satuan_beli, harga_jual |
| 7 | Hitung `stok = jumlah_beli × isi_per_satuan_beli` |
| 8 | `Barang::create(...)` → INSERT `barang` |
| 9 | Redirect ke `barang.index` dengan flash success |

**Alur lain:** edit (`update`), hapus (`destroy`), import (`BarangImport`), export (`BarangExport`).

**File:** `BarangController.php`, `StoreBarangRequest.php`, `UpdateBarangRequest.php`, `BarangImport.php`, `BarangExport.php`

---

### 4.3 Sequence 3 — Pembelian/Restock Barang

**Lifeline:** Admin → `PembelianController` → `Barang` → DB Transaction → `Pembelian` → `DetailPembelian` → `StokLog`

| Step | Pesan | Detail |
|------|-------|--------|
| 1 | GET `/pembelian/create` | Load barang aktif |
| 2 | POST `/pembelian` | Validasi: tanggal, items[], barang_id, jumlah (satuan beli) |
| 3 | Loop items — hitung total | harga pack × jumlah; harga dari `total_harga_beli` atau `harga_satuan_beli` |
| 4 | `Pembelian::create` | Header: kode PB, tanggal, total, catatan |
| 5 | Per item: `DetailPembelian::create` | harga, jumlah pack, subtotal |
| 6 | Konversi stok | `tambahStok = jumlah × isi_per_satuan_beli` |
| 7 | `barang->increment('stok', tambahStok)` | Update `barang.stok` |
| 8 | `StokLog::create` | jenis=`masuk`, jumlah, stok_sebelum/sesudah, keterangan, user_id |
| 9 | Commit transaction | |
| 10 | Redirect | ke `pembelian.create` |

**Laporan:** tidak disimpan terpisah — `LaporanController@pembelian` dan `hitungPersediaan()` membaca `detail_pembelian` secara dinamis saat dibuka.

**File:** `PembelianController.php` (method `store`, `create`, `histori`)

---

### 4.4 Sequence 4 — Penjualan Barang

**Lifeline:** Kasir/Admin → `PenjualanController` → `Barang` → DB Transaction → `Penjualan` → `DetailPenjualan` → `StokLog`

| Step | Pesan | Detail |
|------|-------|--------|
| 1 | GET `/penjualan/create` | Barang `aktif=true` AND `stok > 0` |
| 2 | POST `/penjualan` | Validasi tanggal, items |
| 3 | Pre-validasi stok | Loop: cek barang exists, jumlah > 0, jumlah ≤ stok |
| 4 | Jika error stok | `back()` dengan `error_stok` |
| 5 | Hitung total | `Σ (barang.harga_jual × jumlah)` |
| 6 | `Penjualan::create` | kode TRX, tanggal_transaksi, total, status=selesai, user_id |
| 7 | Per item: `DetailPenjualan::create` | harga=`barang.harga_jual`, jumlah, subtotal |
| 8 | `barang->decrement('stok', jumlah)` | Satuan jual |
| 9 | `StokLog::create` | jenis=`keluar`, penjualan_id, user_id |
| 10 | Commit + redirect | |

**Laporan dinamis:** penjualan, persediaan, laba rugi ter-update saat query dijalankan.

**File:** `PenjualanController.php`

---

### 4.5 Sequence 5 — Pencatatan Pengeluaran Operasional

**Lifeline:** Admin → `PengeluaranController` → `StorePengeluaranRequest` → `Pengeluaran` → DB

| Step | Pesan |
|------|-------|
| 1 | GET `/pengeluaran/create` — load kategori aktif |
| 2 | POST `/pengeluaran` — tanggal, kategori_pengeluaran_id, nominal, keterangan |
| 3 | Validasi `StorePengeluaranRequest` |
| 4 | `Pengeluaran::create` + user_id |
| 5 | Redirect ke index |

**Dampak laba rugi:** `LabaRugiService::hitungLabaRugi()` SUM `pengeluaran.nominal` per periode.

**File:** `PengeluaranController.php`, `StorePengeluaranRequest.php`

---

### 4.6 Sequence 6 — Menampilkan Laporan Penjualan

**Lifeline:** Pemilik → `LaporanController` → `DetailPenjualan` + join → View

| Step | Pesan |
|------|-------|
| 1 | GET `/laporan/penjualan?periode=...` |
| 2 | `parseParams()` — periode, bulan, tahun, custom, kategori |
| 3 | `buildFilter()` — join penjualan, barang, kategori; status=selesai |
| 4 | Agregasi: count, sum jumlah, sum subtotal |
| 5 | Paginate detail rows |
| 6 | Return view `laporan.penjualan` |

**File:** `LaporanController@penjualan`, view `resources/views/laporan/penjualan.blade.php`

---

### 4.7 Sequence 7 — Menampilkan Laporan Pembelian

Sama pola dengan penjualan, sumber: `detail_pembelian` + join `pembelian`, filter tanggal pembelian.

**File:** `LaporanController@pembelian`, `buildFilterPembelian()`

---

### 4.8 Sequence 8 — Menampilkan Laporan Penjualan-Pembelian

**Lifeline:** Pemilik → `LaporanController` → `Barang` + `DetailPenjualan` + `DetailPembelian` → View

| Step | Pesan |
|------|-------|
| 1 | GET `/laporan/penjualan-pembelian?periode=...` |
| 2 | `parseParamsPenjualanPembelian()` — periode, bulan, tahun, dari, sampai, kategori_id, tampilkan |
| 3 | `hitungPenjualanPembelian()` — loop semua barang, `SUM(detail_penjualan.subtotal)` dan `SUM(detail_pembelian.subtotal)` per barang per periode |
| 4 | Hitung ringkasan: totalBarang, totalPenjualan, totalPembelian |
| 5 | Return view `laporan.penjualan-pembelian` |

**File:** `LaporanController@penjualanPembelian`, `cetakPenjualanPembelian`, `exportExcelPenjualanPembelian`, `LaporanPenjualanPembelianExport.php`

---

### 4.9 Sequence 9 — Menampilkan Laporan Persediaan

| Step | Pesan |
|------|-------|
| 1 | GET `/laporan/persediaan` + filter |
| 2 | `parseParamsPersediaan()` |
| 3 | `hitungPersediaan()` — loop semua barang, hitung stok_awal, pembelian, penjualan, stok_akhir per periode |
| 4 | `LabaRugiService::nilaiPersediaanQty()` — total nilai persediaan akhir (card UI) |
| 5 | Return view `laporan.persediaan` |

**File:** `LaporanController@persediaan`, private `hitungPersediaan()`, `LabaRugiService`

---

### 4.10 Sequence 10 — Menampilkan Laporan Laba Rugi

| Step | Pesan | Class/Method |
|------|-------|--------------|
| 1 | GET `/laporan/laba-rugi` + filter periode | |
| 2 | `parseParamsLabaRugi()` | `LaporanController` |
| 3 | `hitungLabaRugi()` → delegasi | `LabaRugiService::hitungLabaRugi()` |
| 4 | Loop barang — hitung stok awal/akhir periode | Query `detail_pembelian`, `detail_penjualan` |
| 5 | Valuasi PA & PAkhir | `nilaiPersediaanQty()` |
| 6 | SUM pembelian (subtotal detail) | |
| 7 | SUM penjualan (subtotal detail, selesai) | |
| 8 | SUM pengeluaran (nominal) | |
| 9 | Rumus: BSD=PA+Pembelian; HPP=BSD−PAkhir; LK=Penjualan−HPP; LB=LK−Pengeluaran | |
| 10 | Return view `laporan.laba-rugi` | |

**Tidak ada tabel laporan** — semua read-only computation.

**File:** `LaporanController@labaRugi`, `app/Services/LabaRugiService.php`

---

### 4.11 Sequence 11 — Menampilkan Dashboard

| Step | Pesan | Sumber |
|------|-------|--------|
| 1 | GET `/dashboard` | `DashboardController@index` |
| 2 | Omzet | SUM `detail_penjualan.subtotal` join penjualan selesai |
| 3 | Laba bersih & nilai persediaan | `LabaRugiService::hitungLabaRugi('semua')` |
| 4 | Total transaksi | COUNT penjualan selesai |
| 5 | Stok menipis | COUNT barang stok ≤ 10 |
| 6 | Grafik penjualan bulanan | GROUP BY MONTH tanggal_transaksi |
| 7 | Grafik laba bulanan | Loop 12 bulan → `hitungLabaRugi('bulan', ...)` |
| 8 | Barang terlaris TOP 5 | SUM detail_penjualan.jumlah |
| 9 | Transaksi terbaru | 10 penjualan terakhir |
| 10 | Return view `dashboard` | |

**Catatan dokumentasi vs kode:** Dokumentasi v1.3 sudah selaras — dashboard **bukan** template statis; menggunakan data real.

**File:** `DashboardController.php`, `resources/views/dashboard.blade.php`

---

## 5. Activity Diagram

### 5.1 Alur Login

| Elemen | Detail |
|--------|--------|
| **Start** | Pengguna membuka halaman login |
| **Aktivitas** | Input email & password → Submit |
| **Decision** | Kredensial valid? |
| **Ya** | Regenerasi session → Redirect dashboard → **End (sukses)** |
| **Tidak** | Tampilkan error → kembali ke form → **End (gagal)** |

---

### 5.2 Alur Pengelolaan Master Barang

| Elemen | Detail |
|--------|--------|
| **Start** | Buka menu Master Barang |
| **Decision** | Aksi? (Tambah / Edit / Hapus / Import / Export) |
| **Tambah** | Isi form → Validasi → Generate kode → Hitung harga & stok awal → Simpan `barang` → **End** |
| **Edit** | Load barang → Update field (stok tidak di-reset dari jumlah_beli input) → Simpan → **End** |
| **Hapus** | Cek relasi transaksi → Hapus jika allowed → **End** |
| **Import** | Upload Excel → `BarangImport` → **End** |

---

### 5.3 Alur Pembelian/Restock

| Elemen | Detail |
|--------|--------|
| **Start** | Buka form pembelian |
| **Aktivitas** | Pilih tanggal, tambah barang ke keranjang (satuan beli) |
| **Decision** | Keranjang kosong? → Ya: error / Tidak: lanjut |
| **Aktivitas sistem** | Begin DB transaction |
| **Aktivitas** | Simpan header `pembelian` |
| **Loop item** | Simpan `detail_pembelian` |
| **Aktivitas** | **Konversi:** `tambahStok = jumlah × isi_per_satuan_beli` |
| **Aktivitas** | **Tambah stok** `barang.stok` |
| **Aktivitas** | **Buat stok_log** jenis `masuk` |
| **Aktivitas** | Commit transaction |
| **End** | Redirect sukses |

---

### 5.4 Alur Penjualan

| Elemen | Detail |
|--------|--------|
| **Start** | Buka form penjualan |
| **Aktivitas sistem** | Load barang aktif dengan stok > 0 |
| **Aktivitas** | Pilih barang & jumlah |
| **Decision** | Barang aktif? | Hanya barang aktif+stok>0 ditampilkan di form |
| **Decision** | Stok tersedia? | Pre-check di `store()` |
| **Decision** | Jumlah > stok? | Ya → error per barang, transaksi ditolak |
| **Aktivitas** | Simpan penjualan + detail (harga dari master) |
| **Aktivitas** | Kurangi stok, buat stok_log keluar |
| **End** | Sukses atau gagal (kembali form) |

---

### 5.5 Alur Pengelolaan Pengeluaran

| Elemen | Detail |
|--------|--------|
| **Start** | Buka menu pengeluaran |
| **Decision** | Tambah / Edit |
| **Aktivitas** | Input tanggal, kategori, nominal, keterangan |
| **Validasi** | Form Request |
| **Simpan** | INSERT/UPDATE `pengeluaran` |
| **End** | Kembali ke daftar |

*Tidak ada fitur hapus pengeluaran di route.*

---

### 5.6 Alur Semua Laporan

Semua laporan dalam sistem (Penjualan, Pembelian, Persediaan, Laba Rugi, Penjualan-Pembelian) mengikuti alur umum yang sama dengan perhitungan berbeda di setiap jenisnya.

| Elemen | Detail |
|--------|--------|
| **Start** | Pengguna membuka menu Laporan |
| **Decision** | Pilih jenis laporan: Penjualan / Pembelian / Persediaan / Laba Rugi / Penjualan-Pembelian |
| **Aktivitas** | Pilih filter periode: Semua / Hari Ini / Bulan / Tahun / Custom (dari–sampai) |
| **Aktivitas** | Pilih filter tambahan jika tersedia: kategori barang, tampilkan aktif/semua |
| **Decision** | Jenis laporan? |
| **→ Penjualan** | `buildFilter()` → JOIN `detail_penjualan` + `penjualan` (status=selesai) + `barang` + `kategori` → `SUM(subtotal)`, `SUM(jumlah)`, COUNT → Tampilkan tabel detail + ringkasan omzet |
| **→ Pembelian** | `buildFilterPembelian()` → JOIN `detail_pembelian` + `pembelian` + `barang` + `kategori` → `SUM(subtotal)`, `SUM(jumlah)`, COUNT → Tampilkan tabel detail + ringkasan total pembelian |
| **→ Persediaan** | `hitungPersediaan()` → loop semua barang → hitung stok_awal periode (stok_master + beli_sebelum − jual_sebelum) → hitung beli_dalam + jual_dalam periode → stok_akhir = stok_awal + beli − jual → `LabaRugiService::nilaiPersediaanQty()` untuk total nilai rupiah → Tampilkan tabel per barang + total nilai persediaan akhir |
| **→ Laba Rugi** | `LabaRugiService::hitungLabaRugi()` → loop barang hitung PA & PAkhir → `SUM(detail_pembelian.subtotal)` → `SUM(detail_penjualan.subtotal)` status=selesai → `SUM(pengeluaran.nominal)` → Rumus: BSD=PA+Pembelian; HPP=BSD−PAkhir; LabaKotor=Penjualan−HPP; LabaBersih=LabaKotor−Pengeluaran → Tampilkan format akuntansi + penjelasan rumus |
| **→ Penjualan-Pembelian** | `hitungPenjualanPembelian()` → loop semua barang → `SUM(detail_penjualan.subtotal)` per barang → `SUM(detail_pembelian.subtotal)` per barang → Tampilkan tabel perbandingan penjualan vs pembelian per barang |
| **Aktivitas** | Hasil ditampilkan di layar (read-only, tidak ada tabel laporan di DB) |
| **Decision** | Pilih aksi lanjutan? |
| **→ Cetak** | Buka view `*-cetak.blade.php` (tanpa sidebar, siap print) → Dialog cetak browser → **End** |
| **→ Export Excel** | `LaporanController@exportExcel*` → `Maatwebsite\Excel::download()` → Download file `.xlsx` → **End** |
| **→ Tidak** | **End** |

**Catatan konsistensi:** Semua laporan membaca data transaksi secara dinamis. Angka di layar, cetak, dan export selalu identik karena menggunakan sumber query yang sama. `LabaRugiService` dipakai bersama oleh Laporan Persediaan (nilai rupiah), Laporan Laba Rugi, dan Dashboard — menjamin angka selalu konsisten antar modul.

---

### 5.7 Alur Pengelolaan Pengguna (Kelola User)

| Elemen | Detail |
|--------|--------|
| **Start** | Pemilik membuka menu Kelola User |
| **Aksi** | Tampilkan daftar pengguna (`UserController@index`) |
| **Decision** | Pilih Aksi? (Tambah / Edit / Simpan) |
| **Tambah** | Klik "Tambah User" → Buka form input (`UserController@create`) → Isi nama, email, password, dan wewenang/role (`pemilik`, `admin`, `kasir`) → Submit |
| **Edit** | Klik "Edit" pada baris user → Muat data lama (`UserController@edit`) → Ubah field yang diperlukan (opsional isi password baru) → Submit |
| **Validasi** | Sistem memproses validasi input (`store`/`update`): email wajib unik, konfirmasi password cocok, wewenang/role valid |
| **Decision** | Validasi lolos? |
| **Ya** | `User::create(...)` atau `user->update(...)` → Simpan ke tabel `users` → Redirect sukses ke daftar user → **End** |
| **Tidak** | Tampilkan pesan kesalahan input → Kembali ke form → **End** |

*Tidak ada fitur hapus pengguna (user) di rute.*

---

## 6. Class Diagram

### 6.1 Prinsip Perbedaan Class Diagram vs ERD

| Aspek | Class Diagram | ERD |
|-------|--------------|-----|
| Fokus | Struktur kelas PHP (atribut + method + relasi OOP) | Struktur tabel database (kolom + FK) |
| Tipe data | Tipe PHP (`string`, `int`, `float`, `bool`, `Date`, `Carbon`) | Tipe SQL (`varchar`, `bigint`, `decimal`, `date`, `enum`) |
| Method | Ada (operasi/behaviour kelas) | Tidak ada |
| Relasi | `hasMany`, `belongsTo`, `hasOne` (OOP) | FK constraint (`RESTRICT`, `CASCADE`, `SET NULL`) |
| Visibilitas | `+` public, `-` private, `#` protected | Tidak ada |

**Prinsip sistem ini:**
- **Entity (Model Eloquent):** mewakili tabel database, memiliki atribut dan relasi OOP.
- **Service:** `LabaRugiService` — logika perhitungan bisnis, tidak mewakili tabel.
- **Controller:** boundary class — menerima HTTP request, tidak memiliki atribut persisten.
- **Tidak ada class Laporan** — laporan dihitung dinamis, bukan entity persisten.

---

### 6.2 Class Entity (Model Eloquent)

#### User
```
+----------------------------------+
|            <<Model>>             |
|              User                |
+----------------------------------+
| - id            : int            |
| - name          : string         |
| - email         : string         |
| - avatar        : string|null    |
| - role          : string         |
| - email_verified_at : Carbon|null|
| - password      : string         |
| - remember_token: string|null    |
| - created_at    : Carbon|null    |
| - updated_at    : Carbon|null    |
+----------------------------------+
| + avatarUrl()   : string         |
| + isPemilik()   : bool           |
| + isAdmin()     : bool           |
| + isKasir()     : bool           |
| + hasRole(roles): bool           |
+----------------------------------+
```
**Relasi:** `User` 1 ──< N `Penjualan`, `User` 1 ──< N `Pengeluaran`, `User` 1 ──< N `StokLog`, `User` 1 ──< N `Pembelian` (nullable)

---

#### Kategori
```
+----------------------------------+
|            <<Model>>             |
|            Kategori              |
+----------------------------------+
| - id                : int        |
| - nama_kategori     : string     |
| - kode_prefix       : string|null|
| - default_satuan_beli : string   |
| - default_satuan_jual : string   |
| - created_at        : Carbon|null|
| - updated_at        : Carbon|null|
+----------------------------------+
| + barang()          : HasMany    |
+----------------------------------+
```
**Relasi:** `Kategori` 1 ──< N `Barang`

---

#### Barang
```
+------------------------------------------+
|               <<Model>>                  |
|                Barang                    |
+------------------------------------------+
| - id                    : int            |
| - kode_barang           : string         |
| - nama_barang           : string         |
| - kategori_id           : int            |
| - tanggal_stok_awal     : Date|null      |
| - jumlah_beli_awal      : int            |
| - satuan_beli           : string         |
| - isi_per_satuan_beli   : int            |
| - total_harga_beli      : float          |
| - harga_satuan_beli     : float          |
| - persentase_keuntungan : float          |
| - harga_beli            : float          |
| - satuan_jual           : string         |
| - harga_jual            : float          |
| - stok                  : int            |
| - aktif                 : bool           |
| - created_at            : Carbon|null    |
| - updated_at            : Carbon|null    |
+------------------------------------------+
| + kategori()            : BelongsTo      |
| + detailPenjualan()     : HasMany        |
| + detailPembelian()     : HasMany        |
+------------------------------------------+
```
**Relasi:** `Barang` N ──> 1 `Kategori`, `Barang` 1 ──< N `DetailPenjualan`, `Barang` 1 ──< N `DetailPembelian`, `Barang` 1 ──< N `StokLog`

---

#### Pembelian
```
+----------------------------------+
|            <<Model>>             |
|            Pembelian             |
+----------------------------------+
| - id              : int          |
| - kode_pembelian  : string       |
| - tanggal         : Date         |
| - total           : float        |
| - catatan         : string|null  |
| - user_id         : int|null     |
| - created_at      : Carbon|null  |
| - updated_at      : Carbon|null  |
+----------------------------------+
| + user()          : BelongsTo    |
| + detailPembelian(): HasMany     |
+----------------------------------+
```
**Relasi:** `Pembelian` 1 ──< N `DetailPembelian`, `Pembelian` N ──> 1 `User` (nullable)

---

#### DetailPembelian
```
+----------------------------------+
|            <<Model>>             |
|         DetailPembelian          |
+----------------------------------+
| - id           : int             |
| - pembelian_id : int             |
| - barang_id    : int             |
| - harga        : float           |
| - jumlah       : int             |
| - subtotal     : float           |
| - created_at   : Carbon|null     |
| - updated_at   : Carbon|null     |
+----------------------------------+
| + pembelian()  : BelongsTo       |
| + barang()     : BelongsTo       |
+----------------------------------+
```
**Relasi:** `DetailPembelian` N ──> 1 `Pembelian`, `DetailPembelian` N ──> 1 `Barang`

---

#### Penjualan
```
+----------------------------------+
|            <<Model>>             |
|            Penjualan             |
+----------------------------------+
| - id                 : int       |
| - kode_transaksi     : string    |
| - tanggal_transaksi  : Date      |
| - total              : float     |
| - status             : string    |
| - catatan            : string|null|
| - user_id            : int       |
| - created_at         : Carbon|null|
| - updated_at         : Carbon|null|
+----------------------------------+
| + user()             : BelongsTo |
| + detailPenjualan()  : HasMany   |
+----------------------------------+
```
**Relasi:** `Penjualan` 1 ──< N `DetailPenjualan`, `Penjualan` N ──> 1 `User`, `Penjualan` 1 ──< N `StokLog` (nullable)

---

#### DetailPenjualan
```
+----------------------------------+
|            <<Model>>             |
|         DetailPenjualan          |
+----------------------------------+
| - id           : int             |
| - penjualan_id : int             |
| - barang_id    : int             |
| - harga        : float           |
| - jumlah       : int             |
| - subtotal     : float           |
| - created_at   : Carbon|null     |
| - updated_at   : Carbon|null     |
+----------------------------------+
| + penjualan()  : BelongsTo       |
| + barang()     : BelongsTo       |
+----------------------------------+
```
**Catatan:** `harga` adalah snapshot `barang.harga_jual` saat transaksi — tidak berubah walau master diedit.

**Relasi:** `DetailPenjualan` N ──> 1 `Penjualan`, `DetailPenjualan` N ──> 1 `Barang`

---

#### KategoriPengeluaran
```
+----------------------------------+
|            <<Model>>             |
|       KategoriPengeluaran        |
+----------------------------------+
| - id             : int           |
| - nama_kategori  : string        |
| - keterangan     : string|null   |
| - status         : string        |
| - created_at     : Carbon|null   |
| - updated_at     : Carbon|null   |
+----------------------------------+
| + pengeluaran()  : HasMany       |
+----------------------------------+
```
**Relasi:** `KategoriPengeluaran` 1 ──< N `Pengeluaran`

---

#### Pengeluaran
```
+------------------------------------------+
|               <<Model>>                  |
|              Pengeluaran                 |
+------------------------------------------+
| - id                       : int         |
| - tanggal                  : Date        |
| - kategori_pengeluaran_id  : int         |
| - nominal                  : float       |
| - keterangan               : string|null |
| - user_id                  : int         |
| - created_at               : Carbon|null |
| - updated_at               : Carbon|null |
+------------------------------------------+
| + kategoriPengeluaran()    : BelongsTo   |
| + user()                   : BelongsTo   |
+------------------------------------------+
```
**Relasi:** `Pengeluaran` N ──> 1 `KategoriPengeluaran`, `Pengeluaran` N ──> 1 `User`

---

#### StokLog
```
+----------------------------------+
|            <<Model>>             |
|             StokLog              |
+----------------------------------+
| - id            : int            |
| - barang_id     : int            |
| - jenis         : string         |
| - jumlah        : int            |
| - stok_sebelum  : int            |
| - stok_sesudah  : int            |
| - keterangan    : string|null    |
| - penjualan_id  : int|null       |
| - user_id       : int            |
| - created_at    : Carbon|null    |
| - updated_at    : Carbon|null    |
+----------------------------------+
| + barang()      : BelongsTo      |
| + penjualan()   : BelongsTo      |
+----------------------------------+
```
**Catatan:** `jenis` bernilai `masuk` (pembelian) atau `keluar` (penjualan). Nilai `koreksi` tersedia di enum DB namun belum digunakan di kode.

**Relasi:** `StokLog` N ──> 1 `Barang`, `StokLog` N ──> 1 `Penjualan` (nullable), `StokLog` N ──> 1 `User`

---

### 6.3 Class Service

#### LabaRugiService
```
+---------------------------------------------------------------+
|                       <<Service>>                             |
|                      LabaRugiService                          |
+---------------------------------------------------------------+
| (tidak ada atribut — stateless, semua data dari DB per call)  |
+---------------------------------------------------------------+
| + nilaiPersediaanQty(                                         |
|     qty            : int,                                     |
|     unitAwalBarang : int,                                     |
|     totalHargaBeli : float,                                   |
|     hargaSatuan    : float                                    |
|   ) : int                                                     |
|                                                               |
| + batasPeriode(                                               |
|     periode : string,                                         |
|     bulan   : int|null,                                       |
|     tahun   : int|null,                                       |
|     dari    : string|null,                                    |
|     sampai  : string|null                                     |
|   ) : array                                                   |
|                                                               |
| + batasPeriodeLabaRugi(                                       |
|     periode : string,                                         |
|     bulan   : int|null,                                       |
|     tahun   : int|null,                                       |
|     dari    : string|null,                                    |
|     sampai  : string|null                                     |
|   ) : array                                                   |
|                                                               |
| + hitungLabaRugi(                                             |
|     periode : string,                                         |
|     bulan   : int|null,                                       |
|     tahun   : int|null,                                       |
|     dari    : string|null,                                    |
|     sampai  : string|null                                     |
|   ) : array {                                                 |
|     persediaanAwal, pembelian, barangSiapDijual,              |
|     persediaanAkhir, hpp, penjualan,                          |
|     labaKotor, pengeluaran, labaBersih                        |
|   }                                                           |
+---------------------------------------------------------------+
```
**Dipakai oleh:** `LaporanController`, `DashboardController`

**File:** `app/Services/LabaRugiService.php`

---

### 6.4 Class Controller

Controller adalah **boundary class** — menerima HTTP request dan mendelegasikan ke Model/Service. Tidak memiliki atribut persisten.

```
+----------------------------------------------+
|             <<Controller>>                   |
|           DashboardController                |
+----------------------------------------------+
| + index() : View                             |
+----------------------------------------------+

+----------------------------------------------+
|             <<Controller>>                   |
|             BarangController                 |
+----------------------------------------------+
| + index()      : View                        |
| + create()     : View                        |
| + store()      : RedirectResponse            |
| + edit()       : View                        |
| + update()     : RedirectResponse            |
| + destroy()    : RedirectResponse            |
| + import()     : RedirectResponse            |
| + export()     : BinaryFileResponse          |
| + template()   : BinaryFileResponse          |
| + previewKode(): JsonResponse                |
| - generateKodeBarang() : string              |
| - hitungHarga()        : array               |
+----------------------------------------------+

+----------------------------------------------+
|             <<Controller>>                   |
|           PembelianController                |
+----------------------------------------------+
| + create()  : View                           |
| + store()   : RedirectResponse               |
| + histori() : View                           |
| - generateKodePembelian() : string           |
+----------------------------------------------+

+----------------------------------------------+
|             <<Controller>>                   |
|           PenjualanController                |
+----------------------------------------------+
| + create()  : View                           |
| + store()   : RedirectResponse               |
| + histori() : View                           |
| - generateKodeTransaksi() : string           |
+----------------------------------------------+

+----------------------------------------------+
|             <<Controller>>                   |
|             UserController                   |
+----------------------------------------------+
| + index()   : View                           |
| + create()  : View                           |
| + store()   : RedirectResponse               |
| + edit()    : View                           |
| + update()  : RedirectResponse               |
| + destroy() : RedirectResponse               |
+----------------------------------------------+

+----------------------------------------------+
|             <<Controller>>                   |
|            LaporanController                 |
+----------------------------------------------+
| + penjualan()              : View            |
| + pembelian()              : View            |
| + persediaan()             : View            |
| + labaRugi()               : View            |
| + penjualanPembelian()     : View            |
| + cetak()                  : View            |
| + cetakPembelian()         : View            |
| + cetakPersediaan()        : View            |
| + cetakLabaRugi()          : View            |
| + cetakPenjualanPembelian(): View            |
| + exportExcel()            : BinaryFileResponse |
| + exportExcelPembelian()   : BinaryFileResponse |
| + exportExcelPersediaan()  : BinaryFileResponse |
| + exportExcelLabaRugi()    : BinaryFileResponse |
| + exportExcelPenjualanPembelian(): BinaryFileResponse |
+----------------------------------------------+
```

---

### 6.5 Class Export (Maatwebsite)

```
+----------------------------------------------+
|              <<Export>>                      |
|         LaporanPenjualanExport               |
+----------------------------------------------+
| - periode      : string                      |
| - dari         : string|null                 |
| - sampai       : string|null                 |
| - keyword      : string|null                 |
| - labelPeriode : string                      |
| - bulan        : int|null                    |
| - tahun        : int|null                    |
| - kategoriId   : int|null                    |
+----------------------------------------------+
| + query()    : Builder                       |
| + headings() : array                         |
| + map()      : array                         |
+----------------------------------------------+
```

| Class Export | Digunakan untuk |
|---|---|
| `LaporanPenjualanExport` | Export laporan penjualan |
| `LaporanPembelianExport` | Export laporan pembelian |
| `LaporanPersediaanExport` | Export laporan persediaan |
| `LaporanPenjualanPembelianExport` | Export laporan penjualan-pembelian |
| `LaporanLabaRugiExport` | Export laporan laba rugi |
| `BarangExport` | Export master barang |
| `BarangTemplateExport` | Template import barang |

---

### 6.6 Ringkasan Relasi Antar Class

```
User ──────────────────────────────────────────────────────────┐
  │ 1                                                           │
  ├──< N  Penjualan ──< N  DetailPenjualan >── N  Barang >── 1 Kategori
  │           │                                    │
  │           └──< N  StokLog (keluar)             ├──< N  DetailPembelian
  │                                                │
  ├──< N  Pembelian ──< N  DetailPembelian >── N  Barang
  │           │
  │           └──< N  StokLog (masuk)
  │
  ├──< N  Pengeluaran >── N  KategoriPengeluaran
  │
  └──< N  StokLog

LaporanController ──uses──> LabaRugiService
DashboardController ──uses──> LabaRugiService
```

---

## 7. ERD

### 7.1 Catatan Penamaan

| Ekspektasi umum | Nama aktual di project |
|-----------------|------------------------|
| `kategori_barang` | **`kategori`** |
| `kategori` (pengeluaran) | **`kategori_pengeluaran`** |

### 7.2 Spesifikasi Tabel

#### users

| Field | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar | |
| email | varchar unique | |
| email_verified_at | timestamp nullable | |
| password | varchar | |
| avatar | varchar nullable | migration `add_avatar_to_users` |
| role | enum('pemilik','admin','kasir') | Menentukan peran dan otorisasi akses user |
| remember_token | varchar | |
| created_at, updated_at | timestamp | |

---

#### kategori

| Field | Tipe |
|-------|------|
| id | bigint PK |
| nama_kategori | varchar(100) |
| kode_prefix | varchar(3) nullable unique |
| default_satuan_beli | varchar(20) default 'pack' |
| default_satuan_jual | varchar(20) default 'pcs' |
| timestamps | |

**Relasi:** 1 kategori → N barang (`barang.kategori_id` FK, onDelete restrict)

---

#### barang

| Field | Tipe |
|-------|------|
| id | bigint PK |
| kode_barang | varchar(20) unique |
| nama_barang | varchar(150) |
| kategori_id | FK → kategori |
| tanggal_stok_awal | date nullable |
| jumlah_beli_awal | integer default 0 |
| satuan_beli | varchar(20) |
| isi_per_satuan_beli | integer |
| total_harga_beli | decimal(14,2) |
| harga_satuan_beli | decimal(14,2) |
| persentase_keuntungan | decimal(5,2) |
| harga_beli | decimal(12,2) legacy |
| harga_jual | decimal(12,2) |
| stok | integer |
| aktif | boolean |
| timestamps | |

**Relasi:**
- N barang → 1 kategori
- 1 barang → N detail_pembelian, detail_penjualan, stok_log

---

#### pembelian

| Field | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| kode_pembelian | varchar(20) unique | Kode otomatis: PB0001, PB0002, … |
| tanggal | date | Tanggal transaksi restock |
| total | decimal(14,2) | Total nilai seluruh item pembelian |
| catatan | text nullable | Catatan tambahan |
| user_id | FK → users nullable, set null | Pengguna yang mencatat transaksi pembelian |
| timestamps | | |

**Relasi:**
- 1 pembelian → N detail_pembelian (cascade delete)
- N pembelian → 1 users (`user_id` nullable, onDelete set null)

---

#### detail_pembelian

| Field | Tipe |
|-------|------|
| id | bigint PK |
| pembelian_id | FK → pembelian |
| barang_id | FK → barang |
| harga | decimal(12,2) |
| jumlah | integer (satuan beli/pack) |
| subtotal | decimal(14,2) |
| timestamps | |

---

#### penjualan

| Field | Tipe | Catatan |
|-------|------|---------|
| id | bigint PK | |
| kode_transaksi | varchar(30) unique | |
| tanggal_transaksi | date | dipakai laporan (kolom `tanggal` lama di-drop) |
| total | decimal(14,2) | |
| status | enum(selesai,batal) | |
| catatan | text nullable | |
| user_id | FK → users | |
| timestamps | | |

**Relasi:** 1 penjualan → N detail_penjualan; 1 penjualan → N stok_log (nullable)

---

#### detail_penjualan

| Field | Tipe |
|-------|------|
| id | bigint PK |
| penjualan_id | FK → penjualan cascade |
| barang_id | FK → barang restrict |
| harga | decimal(12,2) snapshot |
| jumlah | integer satuan jual |
| subtotal | decimal(14,2) |
| timestamps | |

---

#### kategori_pengeluaran

| Field | Tipe |
|-------|------|
| id | bigint PK |
| nama_kategori | varchar(100) unique |
| keterangan | text nullable |
| status | enum(aktif,nonaktif) |
| timestamps | |

**Relasi:** 1 → N pengeluaran

---

#### pengeluaran

| Field | Tipe |
|-------|------|
| id | bigint PK |
| tanggal | date |
| kategori_pengeluaran_id | FK restrict |
| nominal | decimal(14,2) |
| keterangan | text nullable |
| user_id | FK → users |
| timestamps | |

---

#### stok_log

| Field | Tipe |
|-------|------|
| id | bigint PK |
| barang_id | FK cascade |
| jenis | enum(masuk,keluar,koreksi) |
| jumlah | integer positif |
| stok_sebelum, stok_sesudah | integer |
| keterangan | varchar(255) nullable |
| penjualan_id | FK nullable set null |
| user_id | FK restrict |
| timestamps | |

---

### 7.3 Kardinalitas Relasi (Verifikasi)

| Relasi | Kardinalitas | FK | ON DELETE | Status |
|--------|--------------|-----|-----------|--------|
| kategori — barang | 1 : N | barang.kategori_id | RESTRICT | **Ya** |
| barang — detail_pembelian | 1 : N | detail_pembelian.barang_id | RESTRICT | **Ya** |
| pembelian — detail_pembelian | 1 : N | detail_pembelian.pembelian_id | CASCADE | **Ya** |
| barang — detail_penjualan | 1 : N | detail_penjualan.barang_id | RESTRICT | **Ya** |
| penjualan — detail_penjualan | 1 : N | detail_penjualan.penjualan_id | CASCADE | **Ya** |
| kategori_pengeluaran — pengeluaran | 1 : N | pengeluaran.kategori_pengeluaran_id | RESTRICT | **Ya** |
| barang — stok_log | 1 : N | stok_log.barang_id | CASCADE | **Ya** |
| users — penjualan | 1 : N | penjualan.user_id | RESTRICT | **Ya** |
| users — pengeluaran | 1 : N | pengeluaran.user_id | RESTRICT | **Ya** |
| users — stok_log | 1 : N | stok_log.user_id | RESTRICT | **Ya** |
| users — pembelian | 1 : N (opsional) | pembelian.user_id nullable | SET NULL | **Ya** — ditambahkan |
| penjualan — stok_log | 1 : N (opsional) | stok_log.penjualan_id nullable | SET NULL | **Ya** — hanya jenis keluar |

### 7.4 Entitas Laporan (Non-Tabel)

| Laporan | Sumber data | Class perhitungan |
|---------|-------------|-------------------|
| Penjualan | detail_penjualan + penjualan | `LaporanController::buildFilter()` |
| Pembelian | detail_pembelian + pembelian | `buildFilterPembelian()` |
| Persediaan | barang + transaksi | `LaporanController::hitungPersediaan()` |
| Laba Rugi | barang + transaksi + pengeluaran | `LabaRugiService::hitungLabaRugi()` |
| Penjualan-Pembelian | detail transaksi | `hitungPenjualanPembelian()` |

**Kesimpulan ERD:** Jangan gambarkan tabel laporan — cukup catatan "computed view".

---

## 8. Alur Data dan Konsistensi Laporan

```
[Master Barang]
    │ harga_jual, harga_beli, stok awal
    ▼
[Pembelian] ──► detail_pembelian ──► barang.stok ↑ ──► stok_log (masuk)
    │
[Penjualan] ──► detail_penjualan ──► barang.stok ↓ ──► stok_log (keluar)
    │                    │
    │                    └──► Laporan Penjualan (SUM subtotal, dinamis)
    └──► Laporan Pembelian (SUM subtotal, dinamis)

[Pengeluaran] ──► Laba Rugi (laba bersih ↓)

[Semua transaksi + master] ──► Laporan Persediaan (qty per periode)
                             ──► LabaRugiService (valuasi rupiah PA/PAkhir/HPP)
                             ──► Dashboard (agregasi sama dengan laporan)
```

**Prinsip konsistensi:**
- Harga jual transaksi = `barang.harga_jual` saat store.
- Omzet dashboard = SUM subtotal penjualan selesai (sama dengan laporan penjualan mode semua).
- Laba bersih dashboard = `LabaRugiService` mode semua (sama dengan laporan laba rugi).
- Nilai persediaan akhir card laporan persediaan = `nilaiPersediaanQty()` (sama metode dengan PAkhir laba rugi per barang).

---

## 9. Rekomendasi Diagram Final untuk BAB III

### 9.1 Diagram Wajib

| Diagram | Jumlah | Fokus |
|---------|--------|-------|
| **Use Case Diagram** | 1 | Seluruh modul, aktor Pemilik/Pengelola |
| **ERD** | 1 | 10 tabel bisnis + relasi FK |
| **Class Diagram** | 1 | Model + LabaRugiService + Controller utama |

### 9.2 Sequence Diagram — Wajib (Pilih 4–5)

| Prioritas | Alur | Alasan |
|-----------|------|--------|
| **Wajib 1** | Penjualan barang | Core business + stok + validasi |
| **Wajib 2** | Pembelian/restock | Core business + stok log |
| **Wajib 3** | Laporan laba rugi | Menunjukkan LabaRugiService + rumus |
| **Wajib 4** | Login | Standar BAB III |
| Opsional 5 | Dashboard | Menunjukkan integrasi agregasi |
| Opsional 6 | Pengeluaran | Melengkapi laba bersih |

### 9.3 Activity Diagram — Wajib (Pilih 3–4)

| Prioritas | Alur |
|-----------|------|
| **Wajib 1** | Penjualan (decision stok) |
| **Wajib 2** | Pembelian/restock |
| **Wajib 3** | Laporan laba rugi |
| Opsional 4 | Login / Master barang |

### 9.4 Rekomendasi Set Minimal BAB III

```
1 Use Case Diagram
1 ERD
1 Class Diagram
4 Sequence Diagram (Login, Penjualan, Pembelian, Laba Rugi)
3 Activity Diagram (Penjualan, Pembelian, Laba Rugi)
─────────────────────────
Total: 10 diagram
```

---

## 10. Catatan Ketidaksesuaian atau Hal yang Perlu Dikonfirmasi

| No | Temuan | Sumber | Status |
|----|--------|--------|--------|
| 1 | **Stok Log** — dicatat otomatis, **tidak ada halaman/menu** untuk melihat riwayat | Migration + controller vs sidebar | Dokumentasi menyebutkan fitur audit; UI **tidak ada** |
| 2 | **Stok log jenis `koreksi`** — enum ada, **tidak digunakan** di kode | Migration `stok_log` | Perlu verifikasi rencana fitur |
| 3 | **Role admin/kasir** — ada | `users.role` column | Telah diimplementasikan dengan 3 role: pemilik, admin, kasir |
| 4 | **Verifikasi email** — middleware `verified` aktif, `MustVerifyEmail` di-comment | User model + routes | Perlu verifikasi perilaku aktual |
| 5 | **Nama tabel `kategori`** bukan `kategori_barang` | Migration | Gunakan nama aktual di ERD |
| 6 | **Laporan Penjualan-Pembelian** | Route + controller | **Ditemukan di implementasi** — pastikan disebut di BAB III |
| 7 | **LabaRugiService** | `app/Services/` | **Ditemukan di implementasi** — dokumentasi v1.3 sudah update dashboard; pastikan BAB III menyebut service ini |
| 8 | **Export Laba Rugi** | `laporan.laba-rugi.export` | **Ditemukan di implementasi** |
| 9 | **Hapus pengeluaran** | Route resource `only` | **Tidak ada** — hanya create/edit |
| 10 | **Hapus kategori pengeluaran** | Route | **Tidak ada** |
| 11 | **DatabaseSeeder kosong** | `DatabaseSeeder.php` | Tidak ada data awal bawaan |
| 12 | **Penjualan status `batal`** | Enum di migration | **Perlu verifikasi** — tidak ditemukan flow batal di controller (semua store = selesai) |
| 13 | **Format Akuntansi Laba Rugi** | View `laba-rugi.blade.php` | **Ditemukan di implementasi** — tambahan UI, bukan tabel baru |

---

## 11. Bahan Prompt Diagram

### 11.1 Prompt Use Case Diagram

```
Buat Use Case Diagram PlantUML untuk "Sistem Informasi Penjualan dan Stok Barang Toko Amanda".

Aktor: Pemilik (Owner), Admin, Kasir.

Paket dan use case:
- Autentikasi (Semua Aktor): Login, Register (Pemilik), Logout, Reset Password, Verifikasi Email
- Dashboard (Pemilik, Admin): Melihat Dashboard
- Master (Admin): CRUD Kategori Barang, CRUD Barang, Import Barang, Export Barang, Template Barang, Preview Kode
- Transaksi:
  - Pembelian (Admin): Mencatat Pembelian, Riwayat Pembelian
  - Penjualan: Mencatat Penjualan (Admin, Kasir), Riwayat Penjualan (Pemilik, Admin, Kasir)
- Pengeluaran (Admin): CRUD Kategori Pengeluaran (tanpa delete), CRUD Pengeluaran (tanpa delete)
- Laporan (Pemilik): Penjualan, Pembelian, Persediaan, Laba Rugi, Penjualan-Pembelian — masing-masing dengan extend Cetak dan Export Excel
- Akun (Semua Aktor): Kelola Profil (tanpa hapus akun)
- Kelola User (Pemilik): CRUD User

Jangan masukkan "Melihat Stok Log" (tidak ada UI).
Relasi include: semua use case post-login include Login.
Gunakan bahasa Indonesia untuk label.
```

---

### 11.2 Prompt Sequence Diagram

```
Buat Sequence Diagram PlantUML untuk alur "Penjualan Barang" pada sistem Laravel Toko Amanda.

Aktor: Pemilik/Pengelola Toko.
Lifeline: Browser, PenjualanController, Barang Model, Database, Penjualan, DetailPenjualan, StokLog.

Alur:
1. GET penjualan/create - load barang aktif stok>0
2. POST penjualan - validasi items
3. Loop validasi stok setiap barang
4. Jika stok tidak cukup - return error
5. DB transaction: create Penjualan (status selesai, user_id)
6. Per item: create DetailPenjualan (harga=barang.harga_jual), decrement barang.stok, create StokLog jenis keluar
7. Commit, redirect sukses

Laporan dihitung dinamis - tidak ada insert ke tabel laporan.
```

```
Buat Sequence Diagram PlantUML untuk "Laporan Laba Rugi".

Lifeline: Pemilik, LaporanController, LabaRugiService, Database (barang, detail_pembelian, detail_penjualan, pengeluaran).

Alur:
1. GET laporan/laba-rugi + filter periode
2. parseParamsLabaRugi()
3. LabaRugiService.hitungLabaRugi()
4. Loop barang: hitung stok awal/akhir periode, nilaiPersediaanQty untuk PA dan PAkhir
5. SUM pembelian, penjualan selesai, pengeluaran
6. Hitung: BSD=PA+Pembelian, HPP=BSD-PAkhir, LabaKotor=Penjualan-HPP, LabaBersih=LabaKotor-Pengeluaran
7. Return view laba-rugi
```

---

### 11.3 Prompt Activity Diagram

```
Buat Activity Diagram PlantUML untuk "Alur Penjualan Barang Toko Amanda".

Start -> Buka form penjualan -> Pilih barang (hanya aktif & stok>0) -> Input jumlah
Decision: stok cukup?
  Tidak -> Tampilkan error -> End gagal
  Ya -> Simpan penjualan & detail -> Kurangi stok -> Buat stok_log keluar -> End sukses

Decision points wajib: barang aktif (filter form), stok tersedia, jumlah <= stok.
```

```
Buat Activity Diagram PlantUML untuk "Alur Pembelian/Restock".

Start -> Form pembelian -> Input barang (satuan beli) -> Validasi
-> Simpan pembelian header -> Simpan detail_pembelian
-> Konversi qty ke satuan jual (jumlah x isi_per_satuan_beli)
-> Tambah barang.stok -> Buat stok_log masuk -> End
```

---

### 11.4 Prompt Class Diagram

```
Buat Class Diagram PlantUML untuk sistem Toko Amanda.

Entity (Model Eloquent):
- User (id, name, email, role, password, avatar, +isPemilik, +isAdmin, +isKasir, +hasRole)
- Kategori / table kategori (id, nama_kategori, kode_prefix, default_satuan_beli, default_satuan_jual)
- Barang (id, kode_barang, nama_barang, kategori_id, jumlah_beli_awal, total_harga_beli, harga_satuan_beli, harga_jual, stok, aktif, ...)
- Pembelian, DetailPembelian
- Penjualan, DetailPenjualan
- KategoriPengeluaran, Pengeluaran
- StokLog (jenis: masuk|keluar|koreksi)

Service:
- LabaRugiService (+nilaiPersediaanQty, +hitungLabaRugi, +batasPeriode)

Controller (boundary):
- BarangController, PembelianController, PenjualanController, LaporanController, DashboardController, UserController

Relasi:
Kategori 1--* Barang
Pembelian 1--* DetailPembelian *--1 Barang
Penjualan 1--* DetailPenjualan *--1 Barang
User 1--* Penjualan, Pengeluaran, StokLog
Barang 1--* StokLog

Jangan buat class untuk Laporan (dinamis).
```

---

### 11.5 Prompt ERD

```
Buat ERD PlantUML (entity-relationship) untuk database Toko Amanda.

Tabel:
users, kategori, barang, pembelian, detail_pembelian, penjualan, detail_penjualan, kategori_pengeluaran, pengeluaran, stok_log

Relasi:
kategori ||--o{ barang : kategori_id
pembelian ||--o{ detail_pembelian : pembelian_id
barang ||--o{ detail_pembelian : barang_id
penjualan ||--o{ detail_penjualan : penjualan_id
barang ||--o{ detail_penjualan : barang_id
users ||--o{ penjualan : user_id
users ||--o{ pengeluaran : user_id
users ||--o{ stok_log : user_id
kategori_pengeluaran ||--o{ pengeluaran : kategori_pengeluaran_id
barang ||--o{ stok_log : barang_id
penjualan ||--o| stok_log : penjualan_id nullable

Catatan: laporan penjualan/pembelian/persediaan/laba rugi TIDAK punya tabel - dihitung dinamis.
Gunakan nama tabel aktual: "kategori" bukan "kategori_barang".
Tampilkan PK dan FK utama pada setiap entitas.
```

---

*Dokumen ini siap digunakan sebagai bahan penyusunan diagram UML dan ERD pada BAB III Tugas Akhir. Semua referensi file dapat diverifikasi langsung di repository project.*

---

## 12. Flowchart Sistem

### 12.1 Flowchart Utama — Alur Bisnis Keseluruhan

```
        ┌─────────────────────────────┐
        │           START             │
        └──────────────┬──────────────┘
                       │
        ┌──────────────▼──────────────┐
        │   Pengguna membuka sistem   │
        └──────────────┬──────────────┘
                       │
        ┌──────────────▼──────────────┐
        │    Sudah login?             │
        └──────┬───────────────┬──────┘
               │ Tidak         │ Ya
               ▼               ▼
        ┌─────────────┐  ┌─────────────────────┐
        │  Form Login │  │ Role check (Sistem) │
        │  (email +   │  └────┬───────────┬────┘
        │  password)  │       │ Kasir     │ Pemilik/Admin
        └──────┬──────┘       ▼           ▼
               │         ┌─────────┐ ┌─────────────┐
               │         │  Form   │ │  Dashboard  │
               │         │Penjualan│ │ (omzet,laba)│
               │         └────┬────┘ └──────┬──────┘
               │              │             │
               └──────────────┼─────────────┘
                              │
        ┌─────────────────────▼───────────────────────┐
        │           Menu Utama Sistem                 │
        │  [Master Data] [Transaksi] [Pengeluaran]    │
        │  [Laporan]     [Akun]      [Kelola User]    │
        └──┬──────────┬──────────┬──────────┬─────────┘
           │          │          │          │
           ▼          ▼          ▼          ▼
      Master     Transaksi  Pengeluaran  Laporan
      Data       (lihat 12.3) (lihat 12.5) (lihat 12.6)
    (lihat 12.2)
```

---

### 12.2 Flowchart Master Data

```
    ┌─────────────────────────────┐
    │       Menu Master Data      │
    └──────┬──────────────┬───────┘
           │              │
           ▼              ▼
    ┌─────────────┐  ┌───────────────┐
    │  Kategori   │  │    Barang     │
    │  Barang     │  │               │
    └──────┬──────┘  └───────┬───────┘
           │                 │
    ┌──────▼──────┐   ┌──────▼──────────────────┐
    │ Tambah /    │   │  Tambah Barang           │
    │ Edit /      │   │  - Pilih kategori        │
    │ Hapus       │   │  - Input nama, satuan    │
    │ Kategori    │   │  - Input harga beli      │
    └──────┬──────┘   │  - Input jumlah beli     │
           │          │  - Input isi per satuan  │
           │          │  - Input % keuntungan    │
           │          │    atau harga jual manual│
           │          └──────────┬───────────────┘
           │                     │
           │          ┌──────────▼───────────────┐
           │          │  Sistem generate:         │
           │          │  kode_barang otomatis     │
           │          │  harga_satuan_beli        │
           │          │  harga_jual               │
           │          │  stok = jumlah × isi      │
           │          └──────────┬───────────────┘
           │                     │
           │          ┌──────────▼───────────────┐
           │          │  Simpan ke tabel barang  │
           │          └──────────────────────────┘
           │
    ┌──────▼──────────────────────┐
    │  Kategori prefix dipakai    │
    │  sebagai awalan kode barang │
    └─────────────────────────────┘
```

---

### 12.3 Flowchart Transaksi Pembelian / Restock

```
    ┌──────────────────────────────┐
    │      Buka Form Pembelian     │
    └───────────────┬──────────────┘
                    │
    ┌───────────────▼──────────────┐
    │  Input tanggal pembelian     │
    │  Pilih barang dari daftar    │
    │  Input jumlah (satuan beli)  │
    │  Tambah ke keranjang         │
    └───────────────┬──────────────┘
                    │
    ┌───────────────▼──────────────┐
    │    Keranjang kosong?         │
    └────────┬─────────────┬───────┘
             │ Ya           │ Tidak
             ▼              ▼
    ┌──────────────┐  ┌─────────────────────────┐
    │ Tombol simpan│  │  Klik Simpan Restock     │
    │ disabled     │  └────────────┬────────────┘
    └──────────────┘               │
                    ┌──────────────▼──────────────┐
                    │     Validasi input:          │
                    │  - tanggal wajib diisi       │
                    │  - items min 1               │
                    │  - barang_id valid           │
                    │  - jumlah ≥ 1               │
                    └──────────────┬──────────────┘
                                   │
                    ┌──────────────▼──────────────┐
                    │     Validasi lolos?          │
                    └──────────┬──────────┬────────┘
                               │ Tidak    │ Ya
                               ▼          ▼
                    ┌──────────────┐  ┌───────────────────────┐
                    │ Kembali form │  │   DB::transaction()   │
                    │ + pesan error│  └───────────┬───────────┘
                    └──────────────┘              │
                                   ┌──────────────▼──────────────┐
                                   │  Hitung total pembelian     │
                                   │  Σ (harga_pack × jumlah)    │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │  Simpan header Pembelian    │
                                   │  kode_pembelian = PBxxxx    │
                                   │  tanggal, total, user_id    │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │  Loop setiap item:          │
                                   │  Simpan DetailPembelian     │
                                   │  (harga, jumlah, subtotal)  │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │  Konversi ke satuan jual:   │
                                   │  tambahStok = jumlah × isi  │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │  barang.stok += tambahStok  │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │  Catat StokLog              │
                                   │  jenis = masuk              │
                                   │  stok_sebelum, stok_sesudah │
                                   │  keterangan = kode PB       │
                                   │  user_id = Auth::id()       │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │     Commit transaction      │
                                   └──────────────┬──────────────┘
                                                  │
                                   ┌──────────────▼──────────────┐
                                   │  Redirect + flash success   │
                                   └─────────────────────────────┘
```

---

### 12.4 Flowchart Transaksi Penjualan

```
    ┌──────────────────────────────┐
    │      Buka Form Penjualan     │
    │  Sistem load barang:         │
    │  aktif = true AND stok > 0   │
    └───────────────┬──────────────┘
                    │
    ┌───────────────▼──────────────┐
    │  Input tanggal transaksi     │
    │  Pilih barang dari daftar    │
    │  Input jumlah (satuan jual)  │
    │  Tambah ke keranjang         │
    └───────────────┬──────────────┘
                    │
    ┌───────────────▼──────────────┐
    │    Klik Simpan Transaksi     │
    └───────────────┬──────────────┘
                    │
    ┌───────────────▼──────────────┐
    │   Validasi form (Laravel)    │
    │   tanggal, items min 1       │
    └──────────────┬───────────────┘
                   │
    ┌──────────────▼───────────────┐
    │  Pre-check stok per barang:  │
    │  jumlah ≤ barang.stok?       │
    └───────────┬──────────────┬───┘
                │ Ada yg gagal  │ Semua lolos
                ▼               ▼
    ┌─────────────────┐  ┌──────────────────────────┐
    │ back() + pesan  │  │     DB::transaction()    │
    │ error per barang│  └──────────────┬───────────┘
    └─────────────────┘                 │
                        ┌───────────────▼──────────────┐
                        │  Hitung total:               │
                        │  Σ (harga_jual × jumlah)     │
                        └───────────────┬──────────────┘
                                        │
                        ┌───────────────▼──────────────┐
                        │  Simpan header Penjualan     │
                        │  kode_transaksi = TRXxxxx    │
                        │  tanggal_transaksi, total    │
                        │  status = selesai            │
                        │  user_id = Auth::id()        │
                        └───────────────┬──────────────┘
                                        │
                        ┌───────────────▼──────────────┐
                        │  Loop setiap item:           │
                        │  Simpan DetailPenjualan      │
                        │  harga = barang.harga_jual   │
                        │  (snapshot saat transaksi)   │
                        └───────────────┬──────────────┘
                                        │
                        ┌───────────────▼──────────────┐
                        │  barang.stok -= jumlah       │
                        └───────────────┬──────────────┘
                                        │
                        ┌───────────────▼──────────────┐
                        │  Catat StokLog               │
                        │  jenis = keluar              │
                        │  penjualan_id, user_id       │
                        └───────────────┬──────────────┘
                                        │
                        ┌───────────────▼──────────────┐
                        │     Commit transaction       │
                        └───────────────┬──────────────┘
                                        │
                        ┌───────────────▼──────────────┐
                        │  Redirect + flash success    │
                        └──────────────────────────────┘
```

---

### 12.5 Flowchart Pengeluaran Operasional

```
    ┌──────────────────────────────┐
    │     Buka Menu Pengeluaran    │
    └───────────────┬──────────────┘
                    │
    ┌───────────────▼──────────────┐
    │     Pilih aksi               │
    └──────────┬───────────┬───────┘
               │ Tambah     │ Edit
               ▼            ▼
    ┌──────────────────┐  ┌──────────────────────┐
    │  Form Tambah     │  │  Load data lama      │
    │  - tanggal       │  │  Edit nominal /      │
    │  - kategori      │  │  tanggal / kategori  │
    │  - nominal       │  └──────────┬───────────┘
    │  - keterangan    │             │
    └──────────┬───────┘             │
               └──────────┬──────────┘
                           │
    ┌──────────────────────▼──────────────┐
    │        Validasi Form Request        │
    └──────────────┬──────────────┬───────┘
                   │ Gagal         │ Lolos
                   ▼               ▼
    ┌──────────────────┐  ┌────────────────────────┐
    │ Kembali + error  │  │  Simpan ke pengeluaran │
    └──────────────────┘  │  user_id = Auth::id()  │
                          └────────────┬───────────┘
                                       │
                          ┌────────────▼───────────┐
                          │  Redirect ke daftar    │
                          │  pengeluaran           │
                          └────────────────────────┘

    Catatan: Pengeluaran mengurangi laba bersih pada
    Laporan Laba Rugi melalui LabaRugiService.
    Tidak memengaruhi stok barang.
```

---

### 12.6 Flowchart Laporan

```
    ┌──────────────────────────────────────┐
    │         Menu Laporan                 │
    └───┬──────┬──────┬──────┬─────┬───────┘
        │      │      │      │     │
        ▼      ▼      ▼      ▼     ▼
    Penjualan Pembelian Persediaan Laba  Penjualan-
                                  Rugi  Pembelian

    ──── Alur umum semua laporan ────

    ┌──────────────────────────────────┐
    │  Buka halaman laporan            │
    └──────────────┬───────────────────┘
                   │
    ┌──────────────▼───────────────────┐
    │  Pilih filter periode:           │
    │  Semua / Hari / Bulan /          │
    │  Tahun / Custom (dari-sampai)    │
    └──────────────┬───────────────────┘
                   │
    ┌──────────────▼───────────────────┐
    │  Sistem hitung dinamis:          │
    │  (tidak ada tabel laporan)       │
    │                                  │
    │  Penjualan:                      │
    │    SUM(detail_penjualan.subtotal)│
    │    status = selesai              │
    │                                  │
    │  Pembelian:                      │
    │    SUM(detail_pembelian.subtotal)│
    │                                  │
    │  Persediaan:                     │
    │    stok_awal + beli - jual       │
    │    per barang per periode        │
    │                                  │
    │  Laba Rugi:                      │
    │    LabaRugiService::             │
    │    hitungLabaRugi()              │
    │    PA + Pembelian = BSD          │
    │    BSD - PAkhir = HPP            │
    │    Penjualan - HPP = LabaKotor  │
    │    LabaKotor - Pengeluaran       │
    │    = LabaBersih                  │
    └──────────────┬───────────────────┘
                   │
    ┌──────────────▼───────────────────┐
    │     Tampil di layar              │
    └──────────────┬───────────────────┘
                   │
    ┌──────────────▼───────────────────┐
    │     Pilih aksi lanjutan?         │
    └──────┬────────────────┬──────────┘
           │ Cetak           │ Export Excel
           ▼                 ▼
    ┌────────────────┐  ┌──────────────────┐
    │  Buka tampilan │  │  Download .xlsx  │
    │  cetak khusus  │  │  via Maatwebsite │
    │  (tanpa sidebar│  │  Excel           │
    │  siap print)   │  └──────────────────┘
    └────────────────┘
```

---

### 12.7 Flowchart Dashboard

```
    ┌──────────────────────────────────┐
    │     Pengguna buka /dashboard     │
    └──────────────┬───────────────────┘
                   │
    ┌──────────────▼───────────────────┐
    │  DashboardController@index       │
    └──────────────┬───────────────────┘
                   │
         ┌─────────┴──────────┐
         │  Query paralel:    │
         ├────────────────────┤
         │ 1. Omzet total     │
         │    SUM subtotal    │
         │    penjualan selesai│
         ├────────────────────┤
         │ 2. Laba bersih +   │
         │    Nilai persediaan│
         │    LabaRugiService │
         │    mode 'semua'    │
         ├────────────────────┤
         │ 3. Total transaksi │
         │    COUNT penjualan │
         │    selesai         │
         ├────────────────────┤
         │ 4. Stok menipis    │
         │    barang.stok ≤ 10│
         ├────────────────────┤
         │ 5. Grafik penjualan│
         │    bulanan (12 bln)│
         │    GROUP BY MONTH  │
         ├────────────────────┤
         │ 6. Grafik laba     │
         │    bulanan (12 bln)│
         │    LabaRugiService │
         │    loop per bulan  │
         ├────────────────────┤
         │ 7. Barang terlaris │
         │    TOP 5 SUM qty   │
         ├────────────────────┤
         │ 8. Transaksi       │
         │    terbaru (10)    │
         └────────────────────┘
                   │
    ┌──────────────▼───────────────────┐
    │  Return view dashboard           │
    │  - Kartu statistik               │
    │  - Grafik Chart.js               │
    │  - Tabel barang terlaris         │
    │  - Daftar stok menipis           │
    │  - Tabel transaksi terbaru       │
    └──────────────────────────────────┘
```

---

### 12.8 Flowchart Login & Autentikasi

```
    ┌──────────────────────────────┐
    │            START             │
    └──────────────┬───────────────┘
                   │
    ┌──────────────▼───────────────┐
    │   GET /login                 │
    │   Tampilkan form login       │
    └──────────────┬───────────────┘
                   │
    ┌──────────────▼───────────────┐
    │   POST /login                │
    │   Input: email + password    │
    └──────────────┬───────────────┘
                   │
    ┌──────────────▼───────────────┐
    │   LoginRequest:              │
    │   Rate limit + credentials  │
    └──────────┬───────────────┬───┘
               │ Gagal          │ Lolos
               ▼                ▼
    ┌──────────────────┐  ┌─────────────────────┐
    │  Kembali ke form │  │  Auth::attempt()     │
    │  + pesan error   │  │  vs tabel users      │
    └──────────────────┘  └──────────┬──────────┘
                                     │
                          ┌──────────▼──────────┐
                          │  Kredensial valid?  │
                          └────────┬────────┬───┘
                                   │ Tidak   │ Ya
                                   ▼         ▼
                          ┌──────────┐  ┌──────────────────┐
                          │  Error   │  │  session()->     │
                          │  kembali │  │  regenerate()    │
                          │  ke form │  └────────┬─────────┘
                          └──────────┘           │
                                      ┌──────────▼─────────┐
                                      │  Redirect ke       │
                                      │  /dashboard        │
                                      └────────────────────┘

    Semua route bisnis dilindungi middleware:
    ['auth', 'verified'] serta middleware Peran (Role)
    → Belum login  : redirect ke /login
    → Kasir login  : /dashboard dialihkan ke /penjualan/create (hanya akses penjualan & riwayat)
    → Admin login  : blokir akses ke /users (kelola user) dan /laporan (laporan keuangan)
    → Pemilik login: blokir akses pencatatan transaksi dan master data barang/kategori
```

---

### 12.9 Flowchart Alur Stok (Rangkuman)

```
    ┌──────────────────────────────────────────┐
    │              Master Barang               │
    │  stok awal = jumlah_beli_awal × isi      │
    └───────────────────┬──────────────────────┘
                        │
              ┌─────────┴──────────┐
              │                    │
              ▼                    ▼
    ┌──────────────────┐  ┌──────────────────────┐
    │   PEMBELIAN      │  │     PENJUALAN        │
    │   (Restock)      │  │                      │
    │                  │  │  Validasi:           │
    │  tambahStok =    │  │  jumlah ≤ stok?      │
    │  jumlah ×        │  │                      │
    │  isi_per_satuan  │  │  Tidak → Tolak       │
    │                  │  │  Ya    → Lanjut      │
    │  stok += tambah  │  │                      │
    └────────┬─────────┘  │  stok -= jumlah      │
             │             └──────────┬───────────┘
             │                        │
             ▼                        ▼
    ┌──────────────────┐  ┌──────────────────────┐
    │  StokLog: masuk  │  │  StokLog: keluar     │
    │  stok_sebelum    │  │  stok_sebelum        │
    │  stok_sesudah    │  │  stok_sesudah        │
    │  keterangan: PB  │  │  keterangan: TRX     │
    │  user_id         │  │  penjualan_id        │
    └──────────────────┘  │  user_id             │
                          └──────────────────────┘
                                    │
              ┌─────────────────────┘
              │
              ▼
    ┌──────────────────────────────────────────┐
    │         Rumus stok akhir:                │
    │                                          │
    │  stok = stok_awal                        │
    │       + Σ(detail_pembelian × isi)        │
    │       - Σ(detail_penjualan.jumlah)       │
    └──────────────────────────────────────────┘
              │
              ▼
    ┌──────────────────────────────────────────┐
    │  Dipakai oleh:                           │
    │  - Laporan Persediaan (stok per periode) │
    │  - LabaRugiService (valuasi HPP)         │
    │  - Dashboard (stok menipis ≤ 10)         │
    └──────────────────────────────────────────┘
```

---

*Flowchart disusun berdasarkan implementasi nyata kode project — seluruh alur dapat diverifikasi langsung dari Controller, Model, Migration, dan Service yang ada di repository.*

---

### 12.10 Catatan Alur Registrasi dan Manajemen Pengguna

Berdasarkan implementasi hak akses dan manajemen pengguna terbaru pada sistem Toko Amanda:
1. **Registrasi Publik (`/register`)**: Halaman ini disediakan khusus untuk pendaftaran awal akun **Pemilik (Owner)** baru. Siapa pun yang melakukan pendaftaran melalui halaman publik ini secara otomatis akan diberikan peran (*role*) sebagai `pemilik`.
2. **Pembuatan Akun Staf (Admin & Kasir)**: Peran pengguna lainnya seperti **Admin** dan **Kasir** tidak dapat mendaftarkan diri secara mandiri secara publik. Pemilik toko yang telah terdaftar dan login harus masuk ke menu **Kelola User** (`UserController`) untuk membuatkan akun bagi staf mereka secara internal. Hal ini dilakukan demi keamanan sistem agar tidak ada pihak luar yang mendaftar secara tidak sah sebagai kasir atau admin.
3. **Fungsionalitas Halaman Kelola User (Pemilik)**: Halaman Kelola User (`/users`) yang diakses oleh Pemilik berfungsi sebagai modul kontrol administratif utama untuk manajemen akun karyawan. Melalui modul ini:
    * **Pendaftaran Internal**: Pemilik dapat membuat akun baru dengan menentukan Nama, Email, Password, serta Peran wewenang (`pemilik`, `admin`, atau `kasir`) secara spesifik.
    * **Pembaruan Data**: Pemilik dapat mengubah informasi akun karyawan (Nama, Email, Peran) serta melakukan reset password staf secara langsung jika diperlukan.
    * **Perlindungan Integritas Data (Tanpa Fitur Hapus)**: Sistem secara sengaja menonaktifkan fitur penghapusan pengguna (`destroy` route dinonaktifkan). Hal ini bertujuan untuk melindungi riwayat transaksi penjualan (`penjualan.user_id`), transaksi restock (`pembelian.user_id`), pengeluaran (`pengeluaran.user_id`), dan catatan log audit pergerakan barang (`stok_log.user_id`) agar data pencatat historis tetap utuh dan valid untuk audit akuntansi.
