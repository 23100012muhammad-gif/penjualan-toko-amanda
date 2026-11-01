<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 30)->unique(); // contoh: TRX-20250501-001
            $table->date('tanggal');
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('bayar', 14, 2)->default(0);    // uang yang dibayarkan pembeli
            $table->decimal('kembalian', 14, 2)->default(0);
            $table->enum('status', ['selesai', 'batal'])->default('selesai');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->timestamps();

            // Index untuk laporan harian & bulanan
            $table->index('tanggal');
            $table->index('user_id');
            $table->index(['tanggal', 'status']); // query laporan dengan filter status
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
