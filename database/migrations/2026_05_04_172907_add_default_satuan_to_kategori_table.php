<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori', function (Blueprint $table) {
            $table->string('default_satuan_beli', 20)->default('pack')->after('nama_kategori');
            $table->string('default_satuan_jual', 20)->default('pcs')->after('default_satuan_beli');
        });
    }

    public function down(): void
    {
        Schema::table('kategori', function (Blueprint $table) {
            $table->dropColumn(['default_satuan_beli', 'default_satuan_jual']);
        });
    }
};
