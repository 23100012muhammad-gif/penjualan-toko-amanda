<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanLabaRugiExport implements FromArray, WithStyles, ShouldAutoSize, WithTitle
{
    protected string $labelPeriode;
    protected float  $persediaanAwal;
    protected float  $pembelian;
    protected float  $barangSiapDijual;
    protected float  $persediaanAkhir;
    protected float  $hpp;
    protected float  $penjualan;
    protected float  $labaKotor;
    protected float  $pengeluaran;
    protected float  $labaBersih;

    public function __construct(
        string $labelPeriode,
        float  $persediaanAwal,
        float  $pembelian,
        float  $barangSiapDijual,
        float  $persediaanAkhir,
        float  $hpp,
        float  $penjualan,
        float  $labaKotor,
        float  $pengeluaran,
        float  $labaBersih
    ) {
        $this->labelPeriode     = $labelPeriode;
        $this->persediaanAwal   = $persediaanAwal;
        $this->pembelian        = $pembelian;
        $this->barangSiapDijual = $barangSiapDijual;
        $this->persediaanAkhir  = $persediaanAkhir;
        $this->hpp              = $hpp;
        $this->penjualan        = $penjualan;
        $this->labaKotor        = $labaKotor;
        $this->pengeluaran      = $pengeluaran;
        $this->labaBersih       = $labaBersih;
    }

    public function array(): array
    {
        return [
            ['LAPORAN LABA RUGI', ''],
            ['Toko Amanda', ''],
            ['Periode: ' . $this->labelPeriode, ''],
            ['', ''],
            ['Komponen', 'Nilai (Rp)'],
            ['', ''],
            ['PENJUALAN / OMZET', ''],
            ['Penjualan / Omzet', $this->penjualan],
            ['', ''],
            ['HARGA POKOK PENJUALAN', ''],
            ['Persediaan Awal (+)', $this->persediaanAwal],
            ['Pembelian', $this->pembelian],
            ['Barang Siap Dijual', $this->barangSiapDijual],
            ['Barang Siap Dijual (-)', $this->barangSiapDijual],
            ['Persediaan Akhir', $this->persediaanAkhir],
            ['HPP', $this->hpp],
            ['', ''],
            ['LABA KOTOR', ''],
            ['Penjualan / Omzet (-)', $this->penjualan],
            ['HPP', $this->hpp],
            ['Laba Kotor', $this->labaKotor],
            ['', ''],
            ['LABA BERSIH', ''],
            ['Laba Kotor (-)', $this->labaKotor],
            ['Biaya Operasional', $this->pengeluaran],
            ['Laba Bersih', $this->labaBersih],
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        // Judul
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
        ]);
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['size' => 11],
        ]);

        // Baris header tabel
        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2A3F54']],
        ]);

        // Heading section: baris 7, 10, 18, 23
        foreach ([7, 10, 18, 23] as $row) {
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EFF7']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->mergeCells("A{$row}:B{$row}");
        }

        // Baris hasil akhir tiap section: HPP=16, Laba Kotor=21, Laba Bersih=26
        foreach ([16, 21, 26] as $row) {
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F4FF']],
            ]);
            $sheet->getStyle("B{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ]);
        }

        // Barang Siap Dijual subtotal: baris 13
        $sheet->getStyle('A13:B13')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Format angka kolom B untuk semua baris data
        $sheet->getStyle('B8:B26')->getNumberFormat()->setFormatCode('#,##0');

        // Alignment kanan untuk kolom B
        $sheet->getStyle('B5:B26')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }
}
