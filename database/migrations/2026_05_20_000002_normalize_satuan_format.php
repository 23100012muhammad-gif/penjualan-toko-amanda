<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalkan format satuan dari KAPITAL/lowercase → ucfirst (Pcs, Botol, Pack, dst).
     * Hanya menyentuh barang.satuan_beli, barang.satuan_jual,
     * kategori.default_satuan_beli, kategori.default_satuan_jual.
     * Tidak menyentuh transaksi, stok, histori.
     */
    public function up(): void
    {
        $peta = [
            'PCS'     => 'Pcs',     'pcs'     => 'Pcs',
            'BOTOL'   => 'Botol',   'botol'   => 'Botol',
            'BUNGKUS' => 'Bungkus', 'bungkus' => 'Bungkus',
            'SACHET'  => 'Sachet',  'sachet'  => 'Sachet',
            'PACK'    => 'Pack',    'pack'    => 'Pack',
            'KARDUS'  => 'Kardus',  'kardus'  => 'Kardus',
            'DUS'     => 'Dus',     'dus'     => 'Dus',
            'KARTON'  => 'Karton',  'karton'  => 'Karton',
            'LUSIN'   => 'Lusin',   'lusin'   => 'Lusin',
            'KG'      => 'Kg',      'kg'      => 'Kg',
            'GRAM'    => 'Gram',    'gram'    => 'Gram',
            'LITER'   => 'Liter',   'liter'   => 'Liter',
            'ML'      => 'Ml',      'ml'      => 'Ml',
        ];

        foreach ($peta as $lama => $baru) {
            DB::table('barang')->where('satuan_beli', $lama)->update(['satuan_beli' => $baru]);
            DB::table('barang')->where('satuan_jual', $lama)->update(['satuan_jual' => $baru]);
            DB::table('kategori')->where('default_satuan_beli', $lama)->update(['default_satuan_beli' => $baru]);
            DB::table('kategori')->where('default_satuan_jual', $lama)->update(['default_satuan_jual' => $baru]);
        }
    }

    public function down(): void
    {
        // Tidak di-rollback — perubahan format label tidak merusak data
    }
};
