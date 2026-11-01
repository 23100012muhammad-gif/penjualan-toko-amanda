<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_kategori'       => ['required', 'string', 'max:100', 'unique:kategori,nama_kategori'],
            'default_satuan_beli' => ['required', 'string', 'max:20'],
            'default_satuan_jual' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
            'default_satuan_beli.required' => 'Satuan beli default wajib dipilih.',
            'default_satuan_jual.required' => 'Satuan jual default wajib dipilih.',
        ];
    }
}
