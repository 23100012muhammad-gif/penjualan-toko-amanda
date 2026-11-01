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
            $table->integer('jumlah_beli_awal')->default(0)->after('tanggal_stok_awal');
        });

        // Isi data lama: hitung mundur dari stok ÷ isi_per_satuan_beli
        // Ini hanya untuk data yang sudah ada sebelum kolom ini dibuat
        DB::statement("
            UPDATE barang
            SET jumlah_beli_awal = CASE
                WHEN isi_per_satuan_beli > 0
                THEN CEIL(stok / isi_per_satuan_beli)
                ELSE stok
            END
        ");
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('jumlah_beli_awal');
        });
    }
};
