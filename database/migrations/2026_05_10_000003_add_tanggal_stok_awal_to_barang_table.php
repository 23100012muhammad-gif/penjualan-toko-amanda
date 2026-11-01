<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->date('tanggal_stok_awal')->nullable()->after('kategori_id');
        });

        // Fallback data lama: isi dengan tanggal created_at masing-masing barang
        DB::statement("UPDATE barang SET tanggal_stok_awal = DATE(created_at) WHERE tanggal_stok_awal IS NULL");
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('tanggal_stok_awal');
        });
    }
};
