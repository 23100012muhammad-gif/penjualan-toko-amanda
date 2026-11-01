<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Barang::with('kategori')
                     ->orderBy('kategori_id')
                     ->orderBy('persentase_keuntungan')
                     ->orderBy('nama_barang')
                     ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Tgl. Stok Awal',
            'Satuan Beli',
            'Unit (Isi)',
            'Total Harga Beli',
            'Satuan Jual',
            'Harga Satuan Beli',
            'Profit (%)',
            'Harga Jual',
            'Stok',
        ];
    }

    public function map($b): array
    {
        return [
            $b->kode_barang,
            $b->nama_barang,
            $b->kategori->nama_kategori ?? '-',
            $b->tanggal_stok_awal ? $b->tanggal_stok_awal->format('Y-m-d') : '-',
            strtoupper($b->satuan_beli),
            $b->isi_per_satuan_beli,
            (float) $b->total_harga_beli,
            strtoupper($b->satuan_jual),
            (float) $b->harga_satuan_beli,
            (float) $b->persentase_keuntungan,
            (float) $b->harga_jual,
            $b->stok,
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
}
