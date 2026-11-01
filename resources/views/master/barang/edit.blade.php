﻿@extends('layouts.app')

@section('title', 'Edit Master Barang (Stok Awal) | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-pencil-square me-2"></i>Edit Master Barang (Stok Awal)
    </h3>
    <small class="text-muted">Master Data &rsaquo; Master Barang (Stok Awal) &rsaquo; Edit</small>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-box-seam me-2"></i>Form Edit Master Barang (Stok Awal)</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <div class="mb-3 p-2 rounded d-flex align-items-center gap-2"
             style="background:#f0f4f8; border-left:4px solid #26B99A;">
          <div>
            <small class="text-muted d-block">Kode Barang</small>
            <span class="fw-bold tx-heading" style="font-family:monospace; font-size:16px;">{{ $barang->kode_barang }}</span>
          </div>
          <small class="text-muted ms-2"><i class="bi bi-lock me-1"></i>Tidak dapat diubah</small>
        </div>

        <form action="{{ route('barang.update', $barang) }}" method="POST">
          @csrf @method('PATCH')

          {{-- INFORMASI DASAR --}}
          <h6 class="fw-bold mb-3 tx-heading"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h6>

          <div class="mb-3">
            <label for="kategori_id" class="form-label fw-semibold">
              Kategori <span class="text-danger">*</span>
            </label>
            <select id="kategori_id" name="kategori_id"
                    class="form-select @error('kategori_id') is-invalid @enderror"
                    onchange="onKategoriChange(this.value)">
              <option value="">-- Pilih Kategori --</option>
              @foreach ($kategori as $kat)
                <option value="{{ $kat->id }}"
                  {{ old('kategori_id', $barang->kategori_id) == $kat->id ? 'selected' : '' }}>
                  {{ $kat->nama_kategori }}
                  @if($kat->kode_prefix) ({{ $kat->kode_prefix }}) @endif
                </option>
              @endforeach
            </select>
            @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label for="nama_barang" class="form-label fw-semibold">
              Nama Barang <span class="text-danger">*</span>
            </label>
            <input type="text" id="nama_barang" name="nama_barang"
                   class="form-control @error('nama_barang') is-invalid @enderror"
                   value="{{ old('nama_barang', $barang->nama_barang) }}" autofocus>
            @error('nama_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label for="tanggal_stok_awal" class="form-label fw-semibold">
              Tanggal Stok Awal <span class="text-danger">*</span>
            </label>
            <input type="date" id="tanggal_stok_awal" name="tanggal_stok_awal"
                   class="form-control @error('tanggal_stok_awal') is-invalid @enderror"
                   value="{{ old('tanggal_stok_awal', $barang->tanggal_stok_awal?->format('Y-m-d') ?? date('Y-m-d')) }}">
            <div class="form-text">Tanggal barang pertama kali dimasukkan ke sistem.</div>
            @error('tanggal_stok_awal')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- INFORMASI PEMBELIAN --}}
          <hr class="my-3">
          <h6 class="fw-bold mb-3 tx-heading"><i class="bi bi-cart-plus me-2"></i>Informasi Pembelian dari Supplier</h6>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="satuan_beli" class="form-label fw-semibold">
                Satuan Beli <span class="text-danger">*</span>
              </label>
              <select id="satuan_beli" name="satuan_beli"
                      class="form-select @error('satuan_beli') is-invalid @enderror"
                      onchange="onSatuanBeliChange(this.value)">
                @foreach (['Pcs','Botol','Bungkus','Sachet','Pack','Kardus','Dus','Karton','Lusin','Kg','Liter'] as $s)
                  <option value="{{ $s }}"
                    {{ old('satuan_beli', $barang->satuan_beli) == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
              </select>
              @error('satuan_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label for="isi_per_satuan_beli" class="form-label fw-semibold">
                Isi per Satuan <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input type="number" id="isi_per_satuan_beli" name="isi_per_satuan_beli"
                       class="form-control @error('isi_per_satuan_beli') is-invalid @enderror"
                       value="{{ old('isi_per_satuan_beli', $barang->isi_per_satuan_beli ?? 1) }}" min="1"
                       oninput="paksakanIsiJikaEceran(); hitungHarga(); updateHint();">
                <span class="input-group-text" id="labelSatuanJualIsi">{{ $barang->satuan_jual }}</span>
              </div>
              <div class="form-text">
                1 <span id="hintSatBeli">{{ $barang->satuan_beli }}</span>
                = <span id="hintIsi">{{ old('isi_per_satuan_beli', $barang->isi_per_satuan_beli ?? 1) }}</span>
                <span id="hintSatJual">{{ $barang->satuan_jual }}</span>
              </div>
              @error('isi_per_satuan_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Stok Saat Ini</label>
              @php $isi = max((int)($barang->isi_per_satuan_beli ?? 1), 1); @endphp
              <div class="p-2 rounded" style="background:#f8f9fa; border:1px solid #dee2e6;">
                <strong class="text-primary">{{ $barang->stok }}</strong>
                <span class="text-muted small"> {{ $barang->satuan_jual }}</span>
                <span class="text-muted small"> ({{ ceil($barang->stok / $isi) }} {{ $barang->satuan_beli }})</span>
              </div>
              <div class="form-text">Diperbarui otomatis via transaksi.</div>
              <div class="form-text text-muted mt-1">
                Jml Beli Awal: <strong>{{ $barang->jumlah_beli_awal ?? '-' }} {{ $barang->satuan_beli }}</strong>
              </div>
              <input type="hidden" id="stok" name="stok" value="{{ $barang->stok }}">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="total_harga_beli" class="form-label fw-semibold">
                Total Bayar Supplier <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" id="total_harga_beli" name="total_harga_beli"
                       class="form-control @error('total_harga_beli') is-invalid @enderror"
                       value="{{ old('total_harga_beli', $barang->total_harga_beli ?? $barang->harga_beli) }}"
                       min="1" step="1" oninput="hitungHarga()">
                <span class="input-group-text">total</span>
              </div>
              <div class="form-text">Total pembayaran untuk seluruh pembelian dari supplier.</div>
              @error('total_harga_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Harga Satuan Beli (Modal)</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" id="displayHargaSatuan" class="form-control"
                       readonly style="background:#f8f9fa; font-weight:bold; color:#2a3f54;"
                       value="{{ number_format($barang->harga_satuan_beli ?? ($barang->harga_beli / max($barang->isi_per_satuan_beli ?? 1, 1)), 0, ',', '.') }}">
                <span class="input-group-text">/ {{ $barang->satuan_jual }}</span>
              </div>
              <div class="form-text text-info"><i class="bi bi-calculator me-1"></i>Otomatis: total bayar &divide; (jumlah beli &times; isi per satuan)</div>
            </div>
          </div>

          {{-- INFORMASI PENJUALAN --}}
          <hr class="my-3">
          <h6 class="fw-bold mb-3 tx-heading"><i class="bi bi-bag-check me-2"></i>Informasi Penjualan ke Pelanggan</h6>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="satuan_jual" class="form-label fw-semibold">
                Satuan Jual <span class="text-danger">*</span>
              </label>
              <select id="satuan_jual" name="satuan_jual"
                      class="form-select @error('satuan_jual') is-invalid @enderror"
                      onchange="onSatuanBeliChange(document.getElementById('satuan_beli').value)">
                @foreach (['Pcs','Botol','Bungkus','Sachet','Pack','Kg','Gram','Liter','Ml'] as $s)
                  <option value="{{ $s }}"
                    {{ old('satuan_jual', $barang->satuan_jual) == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
              </select>
              @error('satuan_jual') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label for="persentase_keuntungan" class="form-label fw-semibold">
                Persentase Keuntungan <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input type="number" id="persentase_keuntungan" name="persentase_keuntungan"
                       class="form-control @error('persentase_keuntungan') is-invalid @enderror"
                       value="{{ old('persentase_keuntungan', $barang->persentase_keuntungan ?? 0) }}"
                       min="0" step="0.01" oninput="hitungHarga()">
                <span class="input-group-text">%</span>
              </div>
              @error('persentase_keuntungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label for="harga_jual_display" class="form-label fw-semibold">Harga Jual</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" id="harga_jual_display" class="form-control"
                       value="{{ number_format(old('harga_jual', $barang->harga_jual), 0, ',', '.') }}"
                       style="font-weight:bold; color:#16a34a;"
                       oninput="hitungProfitDariJual()">
                <span class="input-group-text">/ {{ $barang->satuan_jual }}</span>
              </div>
              <input type="hidden" id="harga_jual" name="harga_jual" value="{{ old('harga_jual', $barang->harga_jual) }}">
              <input type="hidden" id="harga_jual_manual" name="harga_jual_manual" value="{{ old('harga_jual_manual', $barang->harga_jual_manual ?? 1) }}">
              <div class="form-text text-success"><i class="bi bi-calculator me-1"></i>Otomatis atau edit manual</div>
            </div>
          </div>

          <hr class="my-3">
          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Perbarui
            </button>
            <a href="{{ route('barang.index') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const satuanKategori = @json($satuanKategori);
// PENTING: deklarasi di sini agar semua fungsi di bawah bisa akses tanpa implicit global
let _updatingHarga = false;

function onKategoriChange(id) {
    const data = satuanKategori[id];
    if (!data) return;
    document.getElementById('satuan_beli').value = data.satuan_beli || '';
    document.getElementById('satuan_jual').value = data.satuan_jual || '';
    updateHint();
    hitungHarga();
}

function paksakanIsiJikaEceran() {
    const satBeli = document.getElementById('satuan_beli').value || '';
    const satJual = document.getElementById('satuan_jual').value || '';
    if (satBeli.toLowerCase() === 'pcs' || (satBeli && satBeli === satJual)) {
        document.getElementById('isi_per_satuan_beli').value = 1;
    }
}

function onSatuanBeliChange(satBeli) {
    satBeli = satBeli || '';
    const satJual  = document.getElementById('satuan_jual').value || '';
    const isiInput = document.getElementById('isi_per_satuan_beli');
    const kunci    = satBeli.toLowerCase() === 'pcs' || (satBeli && satBeli === satJual);

    if (kunci) {
        isiInput.value    = 1;
        isiInput.readOnly = true;
        isiInput.style.background = '#f8f9fa';
    } else {
        isiInput.readOnly = false;
        isiInput.style.background = '';
    }
    updateHint();
    hitungHarga();
}

function updateHint() {
    const satBeli  = document.getElementById('satuan_beli').value || '';
    const satJual  = document.getElementById('satuan_jual').value || '';
    const isiInput = document.getElementById('isi_per_satuan_beli');
    const kunci    = satBeli.toLowerCase() === 'pcs' || (satBeli && satBeli === satJual);

    if (kunci) {
        isiInput.value    = 1;
        isiInput.readOnly = true;
        isiInput.style.background = '#f8f9fa';
        document.querySelector('#isi_per_satuan_beli').closest('.col-md-4').querySelector('.form-text').innerHTML =
            '<span class="text-info"><i class="bi bi-info-circle me-1"></i>Satuan beli Pcs &rarr; isi otomatis 1</span>';
    } else {
        isiInput.readOnly = false;
        isiInput.style.background = '';
        const isi = isiInput.value || 1;
        document.getElementById('hintSatBeli').textContent = satBeli;
        document.getElementById('hintIsi').textContent     = isi;
        document.getElementById('hintSatJual').textContent = satJual;
        document.getElementById('labelSatuanJualIsi').textContent = satJual;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const satBeli  = document.getElementById('satuan_beli').value || '';
    const satJual  = document.getElementById('satuan_jual').value || '';
    const isiInput = document.getElementById('isi_per_satuan_beli');
    const kunci    = satBeli.toLowerCase() === 'pcs' || (satBeli && satBeli === satJual);
    if (kunci) {
        isiInput.value    = 1;
        isiInput.readOnly = true;
        isiInput.style.background = '#f8f9fa';
        document.querySelector('#isi_per_satuan_beli').closest('.col-md-4').querySelector('.form-text').innerHTML =
            '<span class="text-info"><i class="bi bi-info-circle me-1"></i>Satuan beli Pcs &rarr; isi otomatis 1</span>';
    }
});

// Di edit, jumlah_beli dihitung mundur dari stok existing ÷ isi
function getJumlahBeliEfektif() {
    const stok = parseFloat(document.getElementById('stok').value) || 0;
    const isi  = parseFloat(document.getElementById('isi_per_satuan_beli').value) || 1;
    return stok > 0 ? Math.ceil(stok / isi) : 0;
}

function hitungHarga() {
    if (_updatingHarga) return;
    _updatingHarga = true;

    const total     = parseFloat(document.getElementById('total_harga_beli').value) || 0;
    const jumlah    = getJumlahBeliEfektif();
    const isi       = parseFloat(document.getElementById('isi_per_satuan_beli').value) || 1;
    const persen    = parseFloat(document.getElementById('persentase_keuntungan').value) || 0;
    const totalUnit = jumlah * isi;
    const modal     = totalUnit > 0 ? Math.round(total / totalUnit) : 0;
    const jual      = Math.round(modal * (1 + persen / 100));

    document.getElementById('displayHargaSatuan').value = modal > 0 ? modal.toLocaleString('id-ID') : '0';
    document.getElementById('harga_jual_display').value = jual > 0 ? jual.toLocaleString('id-ID') : '0';
    document.getElementById('harga_jual').value         = jual;
    document.getElementById('harga_jual_manual').value  = '0';

    _updatingHarga = false;
}

function hitungProfitDariJual() {
    if (_updatingHarga) return;
    _updatingHarga = true;

    const total     = parseFloat(document.getElementById('total_harga_beli').value) || 0;
    const jumlah    = getJumlahBeliEfektif();
    const isi       = parseFloat(document.getElementById('isi_per_satuan_beli').value) || 1;
    // Selalu parse dari display — strip titik ribuan Indonesia, ganti koma desimal ke titik
    const rawStr    = (document.getElementById('harga_jual_display').value || '0')
                        .replace(/\./g, '').replace(',', '.');
    const jualRaw   = parseFloat(rawStr) || 0;
    const totalUnit = jumlah * isi;
    const modal     = totalUnit > 0 ? total / totalUnit : 0;
    const persen    = modal > 0 ? ((jualRaw - modal) / modal * 100) : 0;

    document.getElementById('persentase_keuntungan').value = parseFloat(persen.toFixed(2));
    document.getElementById('harga_jual').value            = jualRaw;
    document.getElementById('harga_jual_manual').value     = '1';

    _updatingHarga = false;
}

// Nilai harga_jual sudah dirender dari database via PHP.
// hitungHarga() hanya berjalan saat user mengubah field secara aktif.
</script>
@endsection
