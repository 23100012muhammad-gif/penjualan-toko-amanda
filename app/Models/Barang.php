<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table    = 'barang';
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori_id',
        'tanggal_stok_awal',
        'jumlah_beli_awal',
        'satuan_beli',
        'isi_per_satuan_beli',
        'total_harga_beli',
        'harga_satuan_beli',
        'persentase_keuntungan',
        'harga_beli',   // tetap ada untuk kompatibilitas data lama
        'satuan_jual',
        'harga_jual',
        'stok',
        'aktif',
    ];

    protected $casts = [
        'tanggal_stok_awal'     => 'date',
        'total_harga_beli'      => 'decimal:2',
        'harga_satuan_beli'     => 'decimal:2',
        'persentase_keuntungan' => 'decimal:2',
        'harga_beli'            => 'decimal:2',
        'harga_jual'            => 'decimal:2',
        'aktif'                 => 'boolean',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'barang_id');
    }

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'barang_id');
    }
}
