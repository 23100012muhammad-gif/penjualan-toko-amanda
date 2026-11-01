<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            // Berapa satuan_jual dalam 1 satuan_beli
            // Contoh: 1 PACK = 12 PCS → isi_per_satuan_beli = 12
            $table->integer('isi_per_satuan_beli')->default(1)->after('satuan_beli');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('isi_per_satuan_beli');
        });
    }
};
