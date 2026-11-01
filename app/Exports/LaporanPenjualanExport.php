<?php

namespace App\Exports;

use App\Models\DetailPenjualan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPenjualanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected string  $labelPeriode;
    protected ?string $dari;
    protected ?string $sampai;
    protected ?string $keyword;
    protected string  $periode;
    protected ?int    $bulan;
    protected ?int    $tahun;
    protected ?int    $kategoriId;

    public function __construct(
        string  $periode,
        ?string $dari,
        ?string $sampai,
        ?string $keyword,
        string  $labelPeriode,
        ?int    $bulan,
        ?int    $tahun,
        ?int    $kategoriId = null
    ) {
        $this->periode      = $periode;
        $this->dari         = $dari;
        $this->sampai       = $sampai;
        $this->keyword      = $keyword;
        $this->labelPeriode = $labelPeriode;
        $this->bulan        = $bulan;
        $this->tahun        = $tahun;
        $this->kategoriId   = $kategoriId;
    }

    public function query()
    {
        $dari       = $this->dari;
        $sampai     = $this->sampai;
        $keyword    = $this->keyword;
        $periode    = $this->periode;
        $bulan      = $this->bulan;
        $tahun      = $this->tahun;
        $kategoriId = $this->kategoriId;

        return DetailPenjualan::query()
            ->join('penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
            ->join('barang', 'barang.id', '=', 'detail_penjualan.barang_id')
            ->join('kategori', 'kategori.id', '=', 'barang.kategori_id')
            ->where('penjualan.status', 'selesai')
            ->when($keyword, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('barang.kode_barang', 'like', '%' . $keyword . '%')
                       ->orWhere('barang.nama_barang', 'like', '%' . $keyword . '%')
                )
            )
            ->when($kategoriId, fn($q) => $q->where('barang.kategori_id', $kategoriId))
            ->when($periode === 'bulan' && $bulan && $tahun,
                fn($q) => $q->whereMonth('penjualan.tanggal_transaksi', $bulan)->whereYear('penjualan.tanggal_transaksi', $tahun)
            )
            ->when($periode === 'tahun' && $tahun,
                fn($q) => $q->whereYear('penjualan.tanggal_transaksi', $tahun)
            )
            ->when($dari,   fn($q) => $q->where('penjualan.tanggal_transaksi', '>=', $dari))
            ->when($sampai, fn($q) => $q->where('penjualan.tanggal_transaksi', '<=', $sampai))
            ->select(
                'detail_penjualan.*',
                'penjualan.tanggal_transaksi as tgl_transaksi',
                'barang.kode_barang',
                'barang.nama_barang',
                'barang.satuan_jual',
                'kategori.nama_kategori'
            )
            ->orderBy('kategori.nama_kategori')
            ->orderBy('barang.nama_barang')
            ->orderBy('penjualan.tanggal_transaksi', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Kode Barang', 'Nama Barang', 'Kategori', 'Harga Jual', 'Satuan Jual', 'Jumlah Jual', 'Total Harga'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            \Carbon\Carbon::parse($row->tgl_transaksi)->format('d-m-Y'),
            $row->kode_barang,
            $row->nama_barang,
            $row->nama_kategori ?? '-',
            (float) $row->harga,
            strtoupper($row->satuan_jual ?? '-'),
            (int) $row->jumlah,
            (float) $row->subtotal,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2A3F54']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}
