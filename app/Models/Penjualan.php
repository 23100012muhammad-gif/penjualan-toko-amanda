<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    protected $table    = 'penjualan';
    protected $fillable = [
        'kode_transaksi',
        'tanggal_transaksi',
        'total',
        'status',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'total'             => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }
}
