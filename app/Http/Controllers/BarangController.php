<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use App\Models\Kategori;
use App\Imports\BarangImport;
use App\Exports\BarangExport;
use App\Exports\BarangTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    /**
     * Generate kode barang berdasarkan prefix kategori.
     * Format: PREFIX + nomor urut 4 digit per kategori.
     * Contoh: SBK0001, SBK0002, MNM0001
     */
    private function generateKodeBarang(Kategori $kategori): string
    {
        if (empty($kategori->kode_prefix)) {
            throw new \RuntimeException("Kategori '{$kategori->nama_kategori}' belum memiliki kode prefix. Atur prefix di Master Data › Kategori.");
        }

        $prefix = strtoupper($kategori->kode_prefix);

        $last = Barang::where('kode_barang', 'like', $prefix . '%')
                      ->orderBy('kode_barang', 'desc')
                      ->value('kode_barang');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung harga_satuan_beli dan harga_jual dari input.
     *
     * Rumus benar:
     *   total_unit       = jumlah_beli × isi_per_satuan_beli
     *   harga_satuan_beli = total_harga_beli ÷ total_unit
     *   harga_jual        = harga_satuan_beli × (1 + persen/100)
     */
    private function hitungHarga(float $totalHargaBeli, int $jumlahBeli, int $isi, float $persen, ?float $hargaJualManual = null): array
    {
        $jumlahBeli  = max($jumlahBeli, 1);
        $isi         = max($isi, 1);
        $totalUnit   = $jumlahBeli * $isi;
        $hargaSatuan = round($totalHargaBeli / $totalUnit, 2);

        // Jika user mengisi harga jual manual, pakai langsung (dibulatkan)
        $hargaJual = $hargaJualManual !== null
            ? (int) round($hargaJualManual)
            : (int) round($hargaSatuan * (1 + $persen / 100));

        return [
            'harga_satuan_beli' => $hargaSatuan,
            'harga_jual'        => $hargaJual,
            'harga_beli'        => $totalHargaBeli,
        ];
    }

    public function previewKode(Request $request)
    {
        $kategori = Kategori::find($request->kategori_id);
        if (!$kategori || !$kategori->kode_prefix) {
            return response()->json(['kode' => '—']);
        }
        return response()->json(['kode' => $this->generateKodeBarang($kategori)]);
    }

    public function index()
    {
        $keyword     = request('search');
        $kategoriId  = request('kategori_id');
        $kondisiStok = request('kondisi_stok');
        $filterProfit = request('filter_profit');
        $sorting      = request('sorting', 'default');

        $query = Barang::with('kategori')
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($query) use ($keyword) {
                    $query->where('nama_barang', 'like', "%{$keyword}%")
                          ->orWhere('kode_barang', 'like', "%{$keyword}%");
                });
            })
            ->when($kategoriId,  fn($q) => $q->where('kategori_id', $kategoriId))
            ->when($kondisiStok === 'aman',    fn($q) => $q->where('stok', '>', 5))
            ->when($kondisiStok === 'menipis', fn($q) => $q->whereBetween('stok', [1, 5]))
            ->when($kondisiStok === 'habis',   fn($q) => $q->where('stok', 0))
            ->when($filterProfit === 'rendah',  fn($q) => $q->where('persentase_keuntungan', '<', 15))
            ->when($filterProfit === 'sedang',  fn($q) => $q->whereBetween('persentase_keuntungan', [15, 25]))
            ->when($filterProfit === 'tinggi',  fn($q) => $q->where('persentase_keuntungan', '>', 25));

        // Sorting — grouping kategori tetap dipertahankan sebagai urutan pertama
        // kecuali saat sorting eksplisit dipilih
        if ($sorting === 'nama_az') {
            $query->orderBy('nama_barang');
        } elseif ($sorting === 'harga_tertinggi') {
            $query->orderBy('harga_jual', 'desc');
        } elseif ($sorting === 'harga_terendah') {
            $query->orderBy('harga_jual', 'asc');
        } elseif ($sorting === 'profit_tertinggi') {
            $query->orderBy('persentase_keuntungan', 'desc');
        } elseif ($sorting === 'stok_terbanyak') {
            $query->orderBy('stok', 'desc');
        } elseif ($sorting === 'terbaru') {
            $query->orderBy('created_at', 'desc');
        } else {
            // Default: grouping kategori → profit → nama
            $query->orderBy('kategori_id')
                  ->orderBy('persentase_keuntungan')
                  ->orderBy('nama_barang');
        }

        $totalSemuaBarang = (clone $query)->count();
        $totalModalAwal   = (clone $query)->sum('total_harga_beli');
        $totalStokAwal    = (clone $query)->get()->sum(fn($item) => ($item->jumlah_beli_awal ?? 0) * ($item->isi_per_satuan_beli ?? 0));

        $barang = $query->paginate(25)->withQueryString();

        $semuaKategori = Kategori::orderBy('nama_kategori')->get();
        $semuaBarang   = Barang::select('barang.kode_barang', 'barang.nama_barang', 'kategori.nama_kategori')
                            ->join('kategori', 'kategori.id', '=', 'barang.kategori_id')
                            ->orderBy('barang.nama_barang')
                            ->get();

        return view('master.barang.index', compact(
            'barang', 'keyword', 'kategoriId', 'kondisiStok',
            'filterProfit', 'sorting', 'semuaKategori', 'semuaBarang',
            'totalSemuaBarang', 'totalModalAwal', 'totalStokAwal'
        ));
    }

    public function create()
    {
        $kategori   = Kategori::orderBy('nama_kategori')->get();
        $satuanKategori = $kategori->mapWithKeys(fn($k) => [
            $k->id => [
                'satuan_beli'  => $k->default_satuan_beli,
                'satuan_jual'  => $k->default_satuan_jual,
                'kode_prefix'  => $k->kode_prefix,
            ]
        ]);
        return view('master.barang.create', compact('kategori', 'satuanKategori'));
    }

    public function store(StoreBarangRequest $request)
    {
        $data     = $request->validated();
        $kategori = Kategori::findOrFail($data['kategori_id']);

        if (empty($kategori->kode_prefix)) {
            return back()->withInput()
                         ->with('error', "Kategori '{$kategori->nama_kategori}' belum memiliki kode prefix. Atur terlebih dahulu di Master Data › Kategori.");
        }

        $jumlahBeli = max((int) $request->input('jumlah_beli', 1), 1);
        $isi        = strtolower($data['satuan_beli']) === 'pcs' ? 1 : max((int) $data['isi_per_satuan_beli'], 1);
        $stok       = $jumlahBeli * $isi;

        $hargaJualManual = $request->input('harga_jual_manual') == '1'
            ? (float) $request->input('harga_jual')
            : null;

        $harga = $this->hitungHarga(
            (float) $data['total_harga_beli'],
            $jumlahBeli,
            $isi,
            (float) $data['persentase_keuntungan'],
            $hargaJualManual
        );

        Barang::create([
            'kode_barang'           => $this->generateKodeBarang($kategori),
            'nama_barang'           => $data['nama_barang'],
            'kategori_id'           => $data['kategori_id'],
            'tanggal_stok_awal'     => $data['tanggal_stok_awal'],
            'jumlah_beli_awal'      => $jumlahBeli,
            'satuan_beli'           => $data['satuan_beli'],
            'isi_per_satuan_beli'   => $isi,
            'total_harga_beli'      => $data['total_harga_beli'],
            'harga_satuan_beli'     => $harga['harga_satuan_beli'],
            'persentase_keuntungan' => $data['persentase_keuntungan'],
            'harga_beli'            => $harga['harga_beli'],
            'satuan_jual'           => $data['satuan_jual'],
            'harga_jual'            => $harga['harga_jual'],
            'stok'                  => $stok,
            'aktif'                 => true,
        ]);

        return redirect()->route('barang.index')
                         ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        $satuanKategori = $kategori->mapWithKeys(fn($k) => [
            $k->id => [
                'satuan_beli' => $k->default_satuan_beli,
                'satuan_jual' => $k->default_satuan_jual,
                'kode_prefix' => $k->kode_prefix,
            ]
        ]);
        return view('master.barang.edit', compact('barang', 'kategori', 'satuanKategori'));
    }

    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        $data  = $request->validated();
        $isi   = strtolower($data['satuan_beli']) === 'pcs' ? 1 : max((int) $data['isi_per_satuan_beli'], 1);
        // Saat edit, jumlah_beli tidak diinput ulang.
        // Hitung mundur dari stok existing untuk mendapat jumlah_beli efektif.
        $jumlahBeliEfektif = max((int) ceil($barang->stok / $isi), 1);
        $hargaJualManual = $request->input('harga_jual_manual') == '1'
            ? (float) $request->input('harga_jual')
            : null;

        $harga = $this->hitungHarga(
            (float) $data['total_harga_beli'],
            $jumlahBeliEfektif,
            $isi,
            (float) $data['persentase_keuntungan'],
            $hargaJualManual
        );

        $barang->update([
            'nama_barang'           => $data['nama_barang'],
            'kategori_id'           => $data['kategori_id'],
            'tanggal_stok_awal'     => $data['tanggal_stok_awal'],
            'satuan_beli'           => $data['satuan_beli'],
            'isi_per_satuan_beli'   => max((int) $data['isi_per_satuan_beli'], 1),
            'total_harga_beli'      => $data['total_harga_beli'],
            'harga_satuan_beli'     => $harga['harga_satuan_beli'],
            'persentase_keuntungan' => $data['persentase_keuntungan'],
            'harga_beli'            => $harga['harga_beli'],
            'satuan_jual'           => $data['satuan_jual'],
            'harga_jual'            => $harga['harga_jual'],
            'stok'                  => $data['stok'],
        ]);

        return redirect()->route('barang.index')
                         ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        if ($barang->detailPenjualan()->exists()) {
            return redirect()->route('barang.index')
                             ->with('error', 'Barang tidak dapat dihapus karena sudah tercatat dalam transaksi.');
        }
        $barang->delete();
        return redirect()->route('barang.index')
                         ->with('success', 'Barang berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $import = new BarangImport();
        Excel::import($import, $request->file('file'));

        $pesan = "Import selesai: {$import->berhasil} barang berhasil ditambahkan.";
        if ($import->gagal > 0) {
            $pesan .= " {$import->gagal} baris dilewati karena tidak valid.";
        }

        return redirect()->route('barang.index')
                         ->with('success', $pesan)
                         ->with('import_errors', $import->errors);
    }

    public function export()
    {
        $filename = 'data-barang-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new BarangExport(), $filename);
    }

    public function template()
    {
        return Excel::download(new BarangTemplateExport(), 'template-import-barang.xlsx');
    }
}
