<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')
                  ->constrained('barang')
                  ->onDelete('cascade');
            $table->enum('jenis', ['masuk', 'keluar', 'koreksi']);
            // masuk   = pembelian/restok dari supplier
            // keluar  = terjual (otomatis dari transaksi)
            // koreksi = penyesuaian manual stok fisik
            $table->integer('jumlah');          // selalu positif
            $table->integer('stok_sebelum');    // snapshot stok sebelum perubahan
            $table->integer('stok_sesudah');    // snapshot stok setelah perubahan
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('penjualan_id')
                  ->nullable()
                  ->constrained('penjualan')
                  ->onDelete('set null'); // null jika transaksi dihapus
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->timestamps();

            // Index untuk riwayat per barang dan audit
            $table->index('barang_id');
            $table->index(['barang_id', 'created_at']);
            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_log');
    }
};
