<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Models\Kategori;

class KategoriController extends Controller
{
    /**
     * Generate prefix 3 huruf dari nama kategori secara otomatis.
     *
     * Algoritma:
     * 1. Ambil huruf pertama setiap kata → maks 3 huruf
     * 2. Jika kata hanya 1, ambil 3 huruf pertama kata itu
     * 3. Jika prefix sudah dipakai, tambah angka di akhir (SBK → SB1 → SB2 ...)
     */
    private function generatePrefixKategori(string $namaKategori, ?int $ignoreId = null): string
    {
        $kata  = preg_split('/\s+/', trim($namaKategori));
        $huruf = array_map(fn($k) => strtoupper(preg_replace('/[^A-Za-z]/', '', $k)), $kata);
        $huruf = array_filter($huruf); // buang kosong
        $huruf = array_values($huruf);

        // Ambil huruf pertama tiap kata, maks 3
        if (count($huruf) >= 3) {
            $base = $huruf[0][0] . $huruf[1][0] . $huruf[2][0];
        } elseif (count($huruf) === 2) {
            $base = $huruf[0][0] . substr($huruf[1], 0, 2);
        } else {
            $base = strtoupper(substr($huruf[0] ?? 'XXX', 0, 3));
        }

        $base = str_pad(strtoupper($base), 3, 'X');

        // Cek keunikan, jika sudah ada tambah angka di posisi ke-3
        $candidate = $base;
        $query = Kategori::where('kode_prefix', $candidate);
        if ($ignoreId) $query->where('id', '!=', $ignoreId);

        if (!$query->exists()) {
            return $candidate;
        }

        // Coba SB1, SB2, ... SB9, lalu SA1, SA2, dst
        $prefix2 = substr($base, 0, 2);
        for ($i = 1; $i <= 9; $i++) {
            $candidate = $prefix2 . $i;
            $q = Kategori::where('kode_prefix', $candidate);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);
            if (!$q->exists()) return $candidate;
        }

        // Fallback: prefix2 + huruf A-Z
        foreach (range('A', 'Z') as $c) {
            $candidate = $prefix2 . $c;
            if ($candidate === $base) continue;
            $q = Kategori::where('kode_prefix', $candidate);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);
            if (!$q->exists()) return $candidate;
        }

        // Last resort: random 3 huruf unik
        do {
            $candidate = strtoupper(substr(md5(uniqid()), 0, 3));
        } while (Kategori::where('kode_prefix', $candidate)->exists());

        return $candidate;
    }

    public function index()
    {
        $kategori = Kategori::withCount('barang')->latest()->paginate(10);
        return view('master.kategori.index', compact('kategori'));
    }

    public function create()
    {
        return view('master.kategori.create');
    }

    public function store(StoreKategoriRequest $request)
    {
        $data                = $request->validated();
        $data['kode_prefix'] = $this->generatePrefixKategori($data['nama_kategori']);

        Kategori::create($data);

        return redirect()->route('kategori.index')
                         ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori)
    {
        return view('master.kategori.edit', compact('kategori'));
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori)
    {
        $data = $request->validated();

        // Regenerate prefix hanya jika nama berubah
        if ($data['nama_kategori'] !== $kategori->nama_kategori) {
            $data['kode_prefix'] = $this->generatePrefixKategori($data['nama_kategori'], $kategori->id);
        }

        $kategori->update($data);

        return redirect()->route('kategori.index')
                         ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->barang()->exists()) {
            return redirect()->route('kategori.index')
                             ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki data barang.');
        }

        $kategori->delete();
        return redirect()->route('kategori.index')
                         ->with('success', 'Kategori berhasil dihapus.');
    }
}
