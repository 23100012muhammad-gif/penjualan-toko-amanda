<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang', 20)->unique();
            $table->string('nama_barang', 150);
            $table->foreignId('kategori_id')
                  ->constrained('kategori')
                  ->onDelete('restrict'); // kategori tidak bisa dihapus jika masih ada barang
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->string('satuan', 20)->default('pcs'); // pcs, kg, liter, dus, dll
            $table->boolean('aktif')->default(true);      // soft disable barang tanpa hapus
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('nama_barang');
            $table->index('kategori_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
