<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['Indomie Goreng', 'Makanan', '2025-01-01', 3, 40, 80000, 2500],
            ['Rinso 900gr',    'Sabun',   '2025-01-01', 5, 12, 48000, 1000],
            ['Aqua 600ml',     'Minuman', '2025-01-01', 2, 24, 36000, 2000],
        ];
    }

    public function headings(): array
    {
        return [
            'nama_barang',
            'kategori',
            'tanggal_stok_awal',
            'jumlah_beli',
            'isi_per_satuan',
            'total_harga_beli',
            'harga_jual',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF26B99A']],
            ],
        ];
    }
}
