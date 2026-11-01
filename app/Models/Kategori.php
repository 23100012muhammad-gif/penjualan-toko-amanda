<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table    = 'kategori';
    protected $fillable = [
        'nama_kategori',
        'kode_prefix',
        'default_satuan_beli',
        'default_satuan_jual',
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
