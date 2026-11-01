<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_barang'           => ['required', 'string', 'max:150'],
            'kategori_id'           => ['required', 'exists:kategori,id'],
            'tanggal_stok_awal'     => ['required', 'date'],
            'satuan_beli'           => ['required', 'string', 'max:20'],
            'isi_per_satuan_beli'   => ['required', 'integer', 'min:1'],
            'satuan_jual'           => ['required', 'string', 'max:20'],
            'total_harga_beli'      => ['required', 'numeric', 'min:1'],
            'persentase_keuntungan' => ['required', 'numeric', 'min:0'],
            'jumlah_beli'           => ['nullable', 'integer', 'min:0'],
            'harga_jual'            => ['nullable', 'numeric', 'min:0'],
            'harga_jual_manual'     => ['nullable', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_barang.required'           => 'Nama barang wajib diisi.',
            'kategori_id.required'           => 'Kategori wajib dipilih.',
            'kategori_id.exists'             => 'Kategori tidak valid.',
            'satuan_beli.required'           => 'Satuan beli wajib dipilih.',
            'satuan_jual.required'           => 'Satuan jual wajib dipilih.',
            'total_harga_beli.required'      => 'Total harga beli wajib diisi.',
            'total_harga_beli.min'           => 'Total harga beli harus lebih dari 0.',
            'isi_per_satuan_beli.required'   => 'Isi per satuan wajib diisi.',
            'isi_per_satuan_beli.min'        => 'Isi per satuan minimal 1.',
            'persentase_keuntungan.required' => 'Persentase keuntungan wajib diisi.',
            'persentase_keuntungan.min'      => 'Persentase keuntungan tidak boleh negatif.',
        ];
    }
}
