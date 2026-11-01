<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BarangImport implements ToCollection, WithHeadingRow
{
    public int   $berhasil = 0;
    public int   $gagal    = 0;
    public array $errors   = [];

    private function generateKode(Kategori $kategori): string
    {
        $prefix = strtoupper($kategori->kode_prefix);
        $last   = Barang::where('kode_barang', 'like', $prefix . '%')
                        ->orderBy('kode_barang', 'desc')
                        ->value('kode_barang');
        $next   = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $baris = $index + 2;

            $namaBarang  = trim($row['nama_barang']                                    ?? '');
            $namaKat     = trim($row['kategori']                                        ?? '');
            $stokBeli    = $row['jumlah_beli']  ?? $row['stok_beli']                   ?? null;
            $isi         = $row['isi_per_satuan'] ?? $row['unit'] ?? $row['isi_satuan'] ?? null;
            $totalHarga  = $row['total_harga_beli']                                    ?? null;
            $hargaJual   = $row['harga_jual']                                          ?? null;

            // Validasi
            if ($namaBarang === '') {
                $this->errors[] = "Baris {$baris}: nama_barang wajib diisi."; $this->gagal++; continue;
            }
            if ($namaKat === '') {
                $this->errors[] = "Baris {$baris}: kategori wajib diisi."; $this->gagal++; continue;
            }
            if (!is_numeric($isi) || (int) $isi < 1) {
                $this->errors[] = "Baris {$baris}: isi_per_satuan harus angka >= 1."; $this->gagal++; continue;
            }
            if (!is_numeric($totalHarga) || (float) $totalHarga <= 0) {
                $this->errors[] = "Baris {$baris}: total_harga_beli harus angka > 0."; $this->gagal++; continue;
            }
            if (!is_numeric($hargaJual) || (float) $hargaJual <= 0) {
                $this->errors[] = "Baris {$baris}: harga_jual harus angka > 0."; $this->gagal++; continue;
            }

            $isiInt      = (int) $isi;
            $stokBeliInt = is_numeric($stokBeli) && (int) $stokBeli > 0 ? (int) $stokBeli : 1;
            $totalHargaF = (float) $totalHarga;
            $hargaJualF  = (float) $hargaJual;
            // Rumus: modal per unit = total_harga / (stok_beli × isi)
            $totalUnit   = $stokBeliInt * $isiInt;
            $hargaSatuan = round($totalHargaF / $totalUnit, 2);
            // Hitung balik persentase dari harga_jual yang diinput
            $persenF     = $hargaSatuan > 0
                ? round((($hargaJualF - $hargaSatuan) / $hargaSatuan) * 100, 2)
                : 0;
            $stokTotal   = $stokBeliInt * $isiInt;
            // Tanggal stok awal — handle berbagai format dari Excel
            $tglRaw = $row['tanggal_stok_awal'] ?? '';
            try {
                if (is_numeric($tglRaw) && $tglRaw > 0) {
                    // Excel serial date number
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglRaw)
                                ->format('Y-m-d');
                } elseif ($tglRaw !== '' && $tglRaw !== null) {
                    $tanggal = \Carbon\Carbon::parse((string) $tglRaw)->format('Y-m-d');
                } else {
                    $tanggal = now()->toDateString();
                }
            } catch (\Throwable $e) {
                $tanggal = now()->toDateString();
            }

            try {
                DB::transaction(function () use (
                    $namaBarang, $namaKat, $isiInt, $stokTotal, $stokBeliInt,
                    $totalHargaF, $hargaSatuan, $hargaJualF, $persenF, $tanggal
                ) {
                    $kategori = Kategori::whereRaw('LOWER(TRIM(nama_kategori)) = ?', [
                        strtolower(trim($namaKat))
                    ])->first();

                    if (!$kategori) {
                        throw new \Exception("Kategori '{$namaKat}' tidak ditemukan.");
                    }
                    if (empty($kategori->kode_prefix)) {
                        throw new \Exception("Kategori '{$kategori->nama_kategori}' belum memiliki kode prefix.");
                    }

                    $sudahAda = Barang::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower(trim($namaBarang))])
                                     ->where('kategori_id', $kategori->id)
                                     ->exists();
                    if ($sudahAda) {
                        throw new \Exception("SKIP:Barang '{$namaBarang}' di kategori '{$kategori->nama_kategori}' sudah ada.");
                    }

                    $prefix = strtoupper($kategori->kode_prefix);
                    $last   = Barang::where('kode_barang', 'like', $prefix . '%')
                                    ->orderBy('kode_barang', 'desc')
                                    ->value('kode_barang');
                    $next   = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
                    $kode   = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);

                    Barang::create([
                        'kode_barang'           => $kode,
                        'nama_barang'           => trim($namaBarang),
                        'kategori_id'           => $kategori->id,
                        'tanggal_stok_awal'     => $tanggal,
                        'jumlah_beli_awal'      => $stokBeliInt,
                        'satuan_beli'           => $kategori->default_satuan_beli,
                        'isi_per_satuan_beli'   => $isiInt,
                        'total_harga_beli'      => $totalHargaF,
                        'harga_satuan_beli'     => $hargaSatuan,
                        'persentase_keuntungan' => $persenF,
                        'harga_beli'            => $totalHargaF,
                        'satuan_jual'           => $kategori->default_satuan_jual,
                        'harga_jual'            => $hargaJualF,
                        'stok'                  => $stokTotal,
                        'aktif'                 => true,
                    ]);
                });

                $this->berhasil++;

            } catch (\Throwable $e) {
                if (str_starts_with($e->getMessage(), 'SKIP:')) {
                    $this->errors[] = "Baris {$baris}: " . substr($e->getMessage(), 5);
                } else {
                    $this->errors[] = "Baris {$baris}: gagal ({$e->getMessage()}).";
                }
                $this->gagal++;
            }
        }
    }
}
