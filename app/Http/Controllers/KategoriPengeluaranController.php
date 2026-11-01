<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriPengeluaranRequest;
use App\Http\Requests\UpdateKategoriPengeluaranRequest;
use App\Models\KategoriPengeluaran;

class KategoriPengeluaranController extends Controller
{
    public function index()
    {
        $kategori = KategoriPengeluaran::latest()->paginate(15);
        return view('master.kategori_pengeluaran.index', compact('kategori'));
    }

    public function create()
    {
        return view('master.kategori_pengeluaran.create');
    }

    public function store(StoreKategoriPengeluaranRequest $request)
    {
        KategoriPengeluaran::create($request->validated());

        return redirect()->route('kategori-pengeluaran.index')
                         ->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function edit(KategoriPengeluaran $kategoriPengeluaran)
    {
        return view('master.kategori_pengeluaran.edit', compact('kategoriPengeluaran'));
    }

    public function update(UpdateKategoriPengeluaranRequest $request, KategoriPengeluaran $kategoriPengeluaran)
    {
        $kategoriPengeluaran->update($request->validated());

        return redirect()->route('kategori-pengeluaran.index')
                         ->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }
}
