<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom tanggal yang sudah digantikan oleh tanggal_transaksi.
     * Seluruh sistem sudah dimigrasikan ke tanggal_transaksi sebelum migration ini dijalankan.
     */
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }

    /**
     * Rollback: kembalikan kolom tanggal dan isi ulang dari tanggal_transaksi.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->date('tanggal')->nullable()->after('kode_transaksi');
        });

        \Illuminate\Support\Facades\DB::statement(
            'UPDATE penjualan SET tanggal = tanggal_transaksi WHERE tanggal IS NULL'
        );
    }
};
