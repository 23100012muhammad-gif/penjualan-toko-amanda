# Sistem Informasi Penjualan dan Stok Barang – Toko Amanda

Aplikasi berbasis web yang dikembangkan untuk membantu proses pengelolaan penjualan dan persediaan barang pada Toko Amanda. Sistem ini dibuat sebagai proyek Tugas Akhir pada Program Studi D3 Sistem Informasi, Universitas Tiga Serangkai.

## Fitur Utama

- Pengelolaan data barang dan stok.
- Pengelolaan data penjualan.
- Pengelolaan data pembelian barang.
- Pengelolaan data pengeluaran operasional.
- Pengelolaan data pengguna.
- Pembuatan laporan terkait transaksi dan persediaan barang.
- Laporan penjualan, pembelian, persediaan, pengeluaran, laba rugi.

## Teknologi yang Digunakan

- PHP
- Laravel
- MySQL
- Bootstrap
- HTML, CSS, dan JavaScript

## Persyaratan

- PHP 7.4 atau versi yang sesuai dengan konfigurasi proyek.
- Composer
- MySQL / MariaDB
- Web server (XAMPP direkomendasikan untuk lingkungan lokal).

## Instalasi dan Menjalankan Proyek

1. Clone repository:
   ```bash
   git clone https://github.com/23100012muhammad-gif/penjualan-toko-amanda.git
   ```

2. Masuk ke direktori proyek:
   ```bash
   cd penjualan-toko-amanda
   ```

3. Install dependency:
   ```bash
   composer install
   ```

4. Salin file `.env.example` menjadi `.env`, kemudian sesuaikan konfigurasi database.

5. Buat database MySQL sesuai nama database pada konfigurasi `.env`.

6. Generate application key:
   ```bash
   php artisan key:generate
   ```

7. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```

8. Jalankan aplikasi:
   ```bash
   php artisan serve
   ```

Aplikasi dapat diakses melalui `http://127.0.0.1:8000`.

## Catatan

Proyek ini dikembangkan sebagai bagian dari Tugas Akhir Program Studi D3 Sistem Informasi. Repository ini ditujukan untuk dokumentasi dan portofolio pengembangan aplikasi web.
