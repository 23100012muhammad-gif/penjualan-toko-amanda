<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengeluaranRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'kategori_pengeluaran_id' => [
                'required',
                'integer',
                Rule::exists('kategori_pengeluaran', 'id')->where('status', 'aktif'),
            ],
            'nominal' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'kategori_pengeluaran_id.required' => 'Kategori pengeluaran wajib dipilih.',
            'kategori_pengeluaran_id.exists' => 'Kategori pengeluaran tidak valid atau tidak aktif.',
            'nominal.required' => 'Nominal wajib diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal harus lebih dari 0.',
        ];
    }
}
