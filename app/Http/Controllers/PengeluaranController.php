<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', 'semua');
        $bulan   = $request->input('bulan') ? (int) $request->input('bulan') : null;
        $tahun   = $request->input('tahun') ? (int) $request->input('tahun') : null;
        $dari    = null;
        $sampai  = null;

        if ($periode === 'hari') {
            $dari   = Carbon::today()->toDateString();
            $sampai = Carbon::today()->toDateString();
        } elseif ($periode === 'bulan') {
            if (!$tahun) $tahun = (int) Carbon::now()->format('Y');
            if (!$bulan) $bulan = (int) Carbon::now()->format('n');
            $dari   = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $sampai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        } elseif ($periode === 'tahun') {
            if (!$tahun) $tahun = (int) Carbon::now()->format('Y');
            $dari   = "{$tahun}-01-01";
            $sampai = "{$tahun}-12-31";
        } elseif ($periode === 'custom') {
            $dari   = $request->tanggal_dari ?: null;
            $sampai = $request->tanggal_sampai ?: ($dari ? $dari : null);
        }

        $namaBulan    = $bulan ? Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') : '';
        $labelPeriode = match ($periode) {
            'hari'   => 'Hari Ini (' . Carbon::today()->format('d-m-Y') . ')',
            'bulan'  => ($namaBulan ? $namaBulan . ' ' : '') . ($tahun ?? Carbon::now()->year),
            'tahun'  => 'Tahun ' . ($tahun ?? Carbon::now()->year),
            'custom' => $dari
                ? Carbon::parse($dari)->format('d-m-Y') . ' s/d ' . Carbon::parse($sampai ?? $dari)->format('d-m-Y')
                : 'Custom',
            default  => 'Semua Data',
        };

        $baseQuery = function ($q) use ($dari, $sampai, $request) {
            $q->when($request->keyword, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('keterangan', 'like', '%' . $request->keyword . '%')
                      ->orWhereHas('kategoriPengeluaran', fn ($q2) =>
                          $q2->where('nama_kategori', 'like', '%' . $request->keyword . '%')
                      );
                });
            })
            ->when($dari, fn ($q) => $q->where('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->where('tanggal', '<=', $sampai));
        };

        $pengeluaran = Pengeluaran::with(['kategoriPengeluaran', 'user'])
            ->tap($baseQuery)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends($request->query());

        $summaryIds = Pengeluaran::tap($baseQuery)->pluck('id');
        $totalTransaksi = $summaryIds->count();
        $totalNominalPengeluaran = Pengeluaran::whereIn('id', $summaryIds)->sum('nominal');

        $tahunList = collect(range((int) date('Y'), 2020));

        return view('pengeluaran.index', compact(
            'pengeluaran',
            'totalTransaksi',
            'totalNominalPengeluaran',
            'periode',
            'bulan',
            'tahun',
            'labelPeriode',
            'tahunList'
        ));
    }

    public function create()
    {
        $kategori = KategoriPengeluaran::where('status', 'aktif')
            ->orderBy('nama_kategori')
            ->get();

        return view('pengeluaran.create', compact('kategori'));
    }

    public function store(StorePengeluaranRequest $request)
    {
        Pengeluaran::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $kategori = KategoriPengeluaran::where('status', 'aktif')
            ->orWhere('id', $pengeluaran->kategori_pengeluaran_id)
            ->orderBy('nama_kategori')
            ->get();

        return view('pengeluaran.edit', compact('pengeluaran', 'kategori'));
    }

    public function update(UpdatePengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $pengeluaran->update($request->validated());

        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }
}
