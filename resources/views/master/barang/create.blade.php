﻿@extends('layouts.app')

@section('title', 'Tambah Master Barang (Stok Awal) | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-plus-circle me-2"></i>Tambah Master Barang (Stok Awal)
    </h3>
    <small class="text-muted">Master Data &rsaquo; Master Barang (Stok Awal) &rsaquo; Tambah</small>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-box-seam me-2"></i>Form Tambah Master Barang (Stok Awal)</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{ route('barang.store') }}" method="POST">
          @csrf

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
                <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                  {{ $kat->nama_kategori }}
                  @if($kat->kode_prefix) ({{ $kat->kode_prefix }}) @endif
                </option>
              @endforeach
            </select>
            @error('kategori_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Barang</label>
            <div class="d-flex align-items-center gap-2">
              <div id="previewKode" class="px-3 py-2 rounded fw-bold tx-heading"
                   style="background:#f0f4f8; border:1px solid #dee2e6; font-family:monospace; font-size:15px; min-width:140px;">
                <span class="text-muted small fw-normal">Pilih kategori...</span>
              </div>
              <small class="text-muted"><i class="bi bi-magic me-1"></i>Dibuat otomatis saat disimpan</small>
            </div>
          </div>

          <div class="mb-3">
            <label for="nama_barang" class="form-label fw-semibold">
              Nama Barang <span class="text-danger">*</span>
            </label>
            <input type="text" id="nama_barang" name="nama_barang"
                   class="form-control @error('nama_barang') is-invalid @enderror"
                   value="{{ old('nama_barang') }}"
                   placeholder="Contoh: Indomie Goreng, Teh Botol Sosro"
                   autofocus>
            @error('nama_barang')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="tanggal_stok_awal" class="form-label fw-semibold">
              Tanggal Stok Awal <span class="text-danger">*</span>
            </label>
            <input type="date" id="tanggal_stok_awal" name="tanggal_stok_awal"
                   class="form-control @error('tanggal_stok_awal') is-invalid @enderror"
                   value="{{ old('tanggal_stok_awal', date('Y-m-d')) }}">
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
                <option value="">-- Pilih --</option>
                @foreach (['Pcs','Botol','Bungkus','Sachet','Pack','Kardus','Dus','Karton','Lusin','Kg','Liter'] as $s)
                  <option value="{{ $s }}" {{ old('satuan_beli') == $s ? 'selected' : '' }}>{{ $s }}</option>
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
                       value="{{ old('isi_per_satuan_beli', 1) }}" min="1"
                       oninput="paksakanIsiJikaEceran(); hitungStok();">
                <span class="input-group-text" id="labelSatuanJualIsi">Pcs</span>
              </div>
              <div class="form-text" id="hintIsiWrapper">1 <span id="hintSatBeli">Pack</span> = <span id="hintIsi">1</span> <span id="hintSatJual">Pcs</span></div>
              @error('isi_per_satuan_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label for="jumlah_beli" class="form-label fw-semibold">Jumlah Beli dari Supplier</label>
              <div class="input-group">
                <input type="number" id="jumlah_beli" name="jumlah_beli"
                       class="form-control"
                       value="{{ old('jumlah_beli', 0) }}" min="0"
                       oninput="hitungStok()">
                <span class="input-group-text" id="labelSatuanBeli">Pack</span>
              </div>
              <div class="form-text">Berapa kemasan yang dibeli dari supplier.</div>
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
                       value="{{ old('total_harga_beli', 0) }}" min="1" step="1"
                       oninput="hitungHarga()">
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
                       readonly value="0" style="background:#f8f9fa; font-weight:bold; color:#2a3f54;">
                <span class="input-group-text">/ <span id="labelSatJualHarga">Pcs</span></span>
              </div>
              <div class="form-text text-info"><i class="bi bi-calculator me-1"></i>Otomatis: total bayar &divide; (jumlah beli &times; isi per satuan)</div>
            </div>
          </div>

          {{-- Stok hasil --}}
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <div class="p-2 rounded" style="background:#f0faf7; border:1px solid #26B99A;">
                <small class="text-muted d-block mb-1">Total Stok (Satuan Jual)</small>
                <span class="fw-bold text-success" style="font-size:15px;">
                  <strong id="hasilStok">0</strong>
                  <span id="labelHasilSatuan" class="text-muted" style="font-size:13px;"> Pcs</span>
                </span>
                <span class="text-muted small ms-2">(
                  <span id="hintJumlahBeli">0</span>
                  <span id="hintSatBeliBox"> Pack</span>
                  &times;
                  <span id="hintIsiBox">1</span>
                  <span id="hintSatJualBox"> Pcs</span>
                )</span>
              </div>
              <input type="hidden" id="stok" name="stok" value="{{ old('stok', 0) }}">
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
                <option value="">-- Pilih --</option>
                @foreach (['Pcs','Botol','Bungkus','Sachet','Pack','Kg','Gram','Liter','Ml'] as $s)
                  <option value="{{ $s }}" {{ old('satuan_jual') == $s ? 'selected' : '' }}>{{ $s }}</option>
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
                       value="{{ old('persentase_keuntungan', 10) }}" min="0" step="0.01"
                       oninput="hitungHarga()">
                <span class="input-group-text">%</span>
              </div>
              @error('persentase_keuntungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label for="harga_jual_display" class="form-label fw-semibold">Harga Jual</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" id="harga_jual_display" class="form-control"
                       value="0"
                       style="font-weight:bold; color:#16a34a;"
                       oninput="hitungProfitDariJual()">
                <span class="input-group-text">/ <span id="labelSatJualJual">Pcs</span></span>
              </div>
              <input type="hidden" id="harga_jual" name="harga_jual" value="">
              <input type="hidden" id="harga_jual_manual" name="harga_jual_manual" value="0">
              <div class="form-text text-success"><i class="bi bi-calculator me-1"></i>Otomatis atau edit manual</div>
            </div>
          </div>

          <hr class="my-3">
          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Simpan
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

function onKategoriChange(id) {
    const data = satuanKategori[id];
    if (!data) {
        document.getElementById('previewKode').innerHTML = '<span class="text-muted small fw-normal">Pilih kategori...</span>';
        return;
    }
    document.getElementById('satuan_beli').value = data.satuan_beli || '';
    document.getElementById('satuan_jual').value = data.satuan_jual || '';
    onSatuanBeliChange(document.getElementById('satuan_beli').value);

    const box = document.getElementById('previewKode');
    box.innerHTML = '<span class="text-muted small fw-normal">Memuat...</span>';
    fetch(`{{ route('barang.preview-kode') }}?kategori_id=${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => { box.textContent = d.kode || '—'; })
    .catch(() => { box.innerHTML = '<span class="text-muted small fw-normal">—</span>'; });
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
    const satJual    = document.getElementById('satuan_jual').value || '';
    const isiInput   = document.getElementById('isi_per_satuan_beli');
    const hintWrapper = document.getElementById('hintIsiWrapper');
    const kunci      = satBeli.toLowerCase() === 'pcs' || (satBeli && satBeli === satJual);

    if (kunci) {
        isiInput.value    = 1;
        isiInput.readOnly = true;
        isiInput.style.background = '#f8f9fa';
        hintWrapper.innerHTML = '<span class="text-info"><i class="bi bi-info-circle me-1"></i>Satuan beli Pcs &rarr; isi otomatis 1</span>';
    } else {
        isiInput.readOnly = false;
        isiInput.style.background = '';
        const isi = parseFloat(isiInput.value) || 1;
        hintWrapper.innerHTML = '1 <span id="hintSatBeli">' + (satBeli || 'Pack') + '</span> = <span id="hintIsi">' + isi + '</span> <span id="hintSatJual">' + (satJual || 'Pcs') + '</span>';
    }

    document.getElementById('labelSatuanBeli').textContent    = satBeli || 'Pack';
    document.getElementById('labelSatuanJualIsi').textContent = satJual || 'Pcs';
    document.getElementById('labelSatJualHarga').textContent  = satJual || 'Pcs';
    document.getElementById('labelSatJualJual').textContent   = satJual || 'Pcs';
    document.getElementById('labelHasilSatuan').textContent   = satJual || 'Pcs';
    document.getElementById('hintSatJualBox').textContent     = ' ' + (satJual || 'Pcs');

    hitungStok();
}

let _updatingHarga = false;

function hitungHarga() {
    if (_updatingHarga) return;
    _updatingHarga = true;

    const total     = parseFloat(document.getElementById('total_harga_beli').value) || 0;
    const jumlah    = parseFloat(document.getElementById('jumlah_beli').value) || 0;
    const isi       = parseFloat(document.getElementById('isi_per_satuan_beli').value) || 1;
    const persen    = parseFloat(document.getElementById('persentase_keuntungan').value) || 0;
    const totalUnit = jumlah * isi;
    const modal     = totalUnit > 0 ? Math.round(total / totalUnit) : 0;
    const jual      = Math.round(modal * (1 + persen / 100));

    document.getElementById('displayHargaSatuan').value = modal > 0 ? modal.toLocaleString('id-ID') : '0';
    document.getElementById('harga_jual_display').value = jual > 0 ? jual.toLocaleString('id-ID') : '0';
    document.getElementById('harga_jual').value         = jual;
    document.getElementById('harga_jual_manual').value  = '0';
    const hintIsi = document.getElementById('hintIsi');
    if (hintIsi) hintIsi.textContent = isi;

    _updatingHarga = false;
}

function hitungProfitDariJual() {
    if (_updatingHarga) return;
    _updatingHarga = true;

    const total     = parseFloat(document.getElementById('total_harga_beli').value) || 0;
    const jumlah    = parseFloat(document.getElementById('jumlah_beli').value) || 0;
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

function hitungStok() {
    const jumlah  = parseFloat(document.getElementById('jumlah_beli').value) || 0;
    const isi     = parseFloat(document.getElementById('isi_per_satuan_beli').value) || 1;
    const satBeli = document.getElementById('satuan_beli').value || 'Pack';
    const satJual = document.getElementById('satuan_jual').value || 'Pcs';
    const hasil   = jumlah * isi;

    document.getElementById('hasilStok').textContent      = hasil;
    document.getElementById('stok').value                 = hasil;
    document.getElementById('hintJumlahBeli').textContent = jumlah;
    document.getElementById('hintSatBeliBox').textContent = ' ' + satBeli;
    document.getElementById('hintIsiBox').textContent     = isi;
    document.getElementById('hintSatJualBox').textContent = ' ' + satJual;

    hitungHarga();
}

document.addEventListener('DOMContentLoaded', function () {
    const id = document.getElementById('kategori_id').value;
    if (id) onKategoriChange(id);
    else hitungHarga();
});
</script>
@endsection
