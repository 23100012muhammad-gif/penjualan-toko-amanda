<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['satuan_beli', 'isi_per_satuan_beli']);
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->string('satuan_beli', 20)->default('pcs')->after('satuan');
            $table->integer('isi_per_satuan_beli')->default(1)->after('satuan_beli');
        });
    }
};
