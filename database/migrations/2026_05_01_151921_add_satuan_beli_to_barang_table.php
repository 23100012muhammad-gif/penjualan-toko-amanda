<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Satuan saat beli dari supplier (kardus, lusin, dus, dll)
            $table->string('satuan_beli', 20)->default('pcs')->after('satuan');
            // Berapa satuan jual (eceran) dalam 1 satuan beli
            // Contoh: 1 kardus = 24 botol → isi_per_satuan_beli = 24
            $table->integer('isi_per_satuan_beli')->default(1)->after('satuan_beli');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['satuan_beli', 'isi_per_satuan_beli']);
        });
    }
};
