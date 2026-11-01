<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPersediaanExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected array  $rows;
    protected string $labelPeriode;

    public function __construct(array $rows, string $labelPeriode)
    {
        $this->rows         = $rows;
        $this->labelPeriode = $labelPeriode;
    }

    public function array(): array
    {
        $result = [];
        $no     = 1;

        foreach ($this->rows as $row) {
            $result[] = [
                $no++,
                $row['kode_barang'],
                $row['nama_barang'],
                $row['nama_kategori'] ?? '-',
                $row['stok_awal'],
                $row['pembelian'],
                $row['penjualan'],
                $row['stok_akhir'],
            ];
        }

        return $result;
    }

    public function headings(): array
    {
        return ['No', 'Kode Barang', 'Nama Barang', 'Kategori', 'Stok Awal', 'Pembelian', 'Penjualan', 'Stok Akhir'];
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
        return 'Laporan Persediaan';
    }
}
