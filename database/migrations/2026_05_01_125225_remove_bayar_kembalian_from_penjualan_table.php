<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom bayar dan kembalian dari tabel penjualan.
     * Kolom ini di luar ruang lingkup sistem (tidak ada fitur kasir/pembayaran).
     */
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['bayar', 'kembalian']);
        });
    }

    /**
     * Kembalikan kolom jika migration di-rollback.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->decimal('bayar', 14, 2)->default(0)->after('total');
            $table->decimal('kembalian', 14, 2)->default(0)->after('bayar');
        });
    }
};
