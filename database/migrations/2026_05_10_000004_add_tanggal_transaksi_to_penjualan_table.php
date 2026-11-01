<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->date('tanggal_transaksi')->nullable()->after('kode_transaksi');
        });

        // Fallback data lama: gunakan kolom tanggal yang sudah ada
        DB::statement("UPDATE penjualan SET tanggal_transaksi = tanggal WHERE tanggal_transaksi IS NULL");
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('tanggal_transaksi');
        });
    }
};
