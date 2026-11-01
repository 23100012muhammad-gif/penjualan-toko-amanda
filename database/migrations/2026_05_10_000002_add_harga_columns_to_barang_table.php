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
            $table->decimal('total_harga_beli', 14, 2)->default(0)->after('isi_per_satuan_beli');
            $table->decimal('harga_satuan_beli', 14, 2)->default(0)->after('total_harga_beli');
            $table->decimal('persentase_keuntungan', 5, 2)->default(0)->after('harga_satuan_beli');
        });

        // Mapping data lama:
        // harga_beli lama = total_harga_beli (harga per satuan beli dari supplier)
        // harga_satuan_beli = harga_beli / isi_per_satuan_beli
        // persentase_keuntungan = dihitung mundur dari harga_jual vs harga_satuan_beli
        // LEAST(999.99, ...) untuk mencegah overflow DECIMAL(5,2)
        DB::statement("
            UPDATE barang
            SET
                total_harga_beli     = harga_beli,
                harga_satuan_beli    = CASE
                    WHEN isi_per_satuan_beli > 0
                    THEN ROUND(harga_beli / isi_per_satuan_beli, 2)
                    ELSE harga_beli
                END,
                persentase_keuntungan = CASE
                    WHEN harga_beli > 0 AND isi_per_satuan_beli > 0
                    THEN LEAST(999.99, GREATEST(0, ROUND(
                        ((harga_jual - (harga_beli / isi_per_satuan_beli))
                        / (harga_beli / isi_per_satuan_beli)) * 100
                    , 2)))
                    ELSE 0
                END
        ");
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['total_harga_beli', 'harga_satuan_beli', 'persentase_keuntungan']);
        });
    }
};
