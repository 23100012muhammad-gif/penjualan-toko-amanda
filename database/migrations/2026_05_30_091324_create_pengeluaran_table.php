<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('kategori_pengeluaran_id')
                  ->constrained('kategori_pengeluaran')
                  ->onDelete('restrict');
            $table->decimal('nominal', 14, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->timestamps();

            $table->index('tanggal');
            $table->index('kategori_pengeluaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
