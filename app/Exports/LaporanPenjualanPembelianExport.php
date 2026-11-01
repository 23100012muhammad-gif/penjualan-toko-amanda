<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPenjualanPembelianExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
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
                (float) $row['total_penjualan'],
                (float) $row['total_pembelian'],
            ];
        }

        return $result;
    }

    public function headings(): array
    {
        return ['No', 'Kode Barang', 'Nama Barang', 'Kategori', 'Total Penjualan (Rp)', 'Total Pembelian (Rp)'];
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
        return 'Laporan Penjualan-Pembelian';
    }
}
