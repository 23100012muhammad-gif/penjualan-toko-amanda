<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MariaDB lama tidak support renameColumn via Blueprint
        // Gunakan raw SQL: CHANGE COLUMN
        DB::statement('ALTER TABLE barang CHANGE COLUMN `satuan` `satuan_beli` VARCHAR(20) NOT NULL DEFAULT \'pcs\'');

        Schema::table('barang', function (Blueprint $table) {
            $table->string('satuan_jual', 20)->default('pcs')->after('satuan_beli');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('satuan_jual');
        });

        DB::statement('ALTER TABLE barang CHANGE COLUMN `satuan_beli` `satuan` VARCHAR(20) NOT NULL DEFAULT \'pcs\'');
    }
};
