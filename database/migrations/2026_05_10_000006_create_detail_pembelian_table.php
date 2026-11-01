<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')
                  ->constrained('pembelian')
                  ->onDelete('cascade');
            $table->foreignId('barang_id')
                  ->constrained('barang')
                  ->onDelete('restrict');
            $table->decimal('harga', 12, 2);
            $table->integer('jumlah');
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();

            $table->index('pembelian_id');
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian');
    }
};
