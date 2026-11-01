<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')
                  ->constrained('penjualan')
                  ->onDelete('cascade'); // hapus detail jika transaksi dihapus
            $table->foreignId('barang_id')
                  ->constrained('barang')
                  ->onDelete('restrict'); // barang tidak bisa dihapus jika ada di transaksi
            $table->decimal('harga', 12, 2);   // snapshot harga saat transaksi
            $table->integer('jumlah');
            $table->decimal('subtotal', 14, 2); // harga × jumlah
            $table->timestamps();

            // Index untuk join query
            $table->index('penjualan_id');
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};
