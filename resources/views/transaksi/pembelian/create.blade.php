@extends('layouts.app')

@section('title', 'Pembelian / Restock | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-box-arrow-in-down me-2"></i>Pembelian / Restock
    </h3>
    <small class="text-muted">Transaksi &rsaquo; Pembelian / Restock</small>
  </div>
</div>

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<form action="{{ route('pembelian.store') }}" method="POST" id="formPembelian">
@csrf

<div class="row">
  <div class="col-xl-9 col-lg-10 col-12">
    <div class="x_panel">
      <div class="x_content">

        {{-- ===== TANGGAL PEMBELIAN ===== --}}
        <div class="mb-3">
          <label for="tanggal" class="form-label fw-semibold">
            Tanggal Pembelian <span class="text-danger">*</span>
          </label>
          <input type="date" id="tanggal" name="tanggal"
                 class="form-control @error('tanggal') is-invalid @enderror"
                 value="{{ old('tanggal', date('Y-m-d')) }}"
                 style="max-width:200px;">
          <div class="form-text">Tanggal pembelian / restock barang dari supplier.</div>
          @error('tanggal')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <hr class="my-2">

        {{-- ===== INPUT BARANG ===== --}}
        <div class="tx-bar mb-2">
          <div class="tx-bar-row">

            {{-- Search-first autocomplete --}}
            <div class="flex-grow-1" style="position:relative;">
              <label class="form-label">Pilih Barang</label>
              <div class="input-group input-group-sm">
                <input type="text" id="searchBarang"
                       class="form-control"
                       placeholder="Ketik nama atau kode barang..."
                       autocomplete="off">
                <button type="button" id="btnToggleDropdown"
                        class="btn btn-outline-secondary px-2" tabindex="-1"
                        title="Tampilkan semua barang">
                  <i class="bi bi-chevron-down" style="font-size:11px;"></i>
                </button>
              </div>
              <div id="dropdownBarang"
                   style="display:none; position:absolute; top:100%; left:0; right:0; z-index:9999;
                          background:#fff; border:1px solid #ced4da; border-top:none;
                          border-radius:0 0 4px 4px; max-height:260px; overflow-y:auto;
                          box-shadow:0 4px 8px rgba(0,0,0,.1);">
              </div>
            </div>

            {{-- Jumlah + Tombol Tambah --}}
            <div>
              <label class="form-label">Jumlah</label>
              <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2"
                        onclick="ubahJumlah(-1)">−</button>
                <input type="number" id="inputJumlah" class="form-control form-control-sm qty-input"
                       value="1" min="1">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2"
                        onclick="ubahJumlah(1)">+</button>
                <button type="button" class="btn-ta btn-ta-blue"
                        onclick="tambahKeKeranjang()">
                  <i class="fas fa-plus me-1"></i>Tambah
                </button>
              </div>
            </div>

          </div>

          {{-- Info harga & stok --}}
          <div id="infoBarang" class="d-none">
            <span class="tx-info">
              Harga Beli: <strong id="infoHarga" class="text-primary">-</strong>
              &nbsp;&bull;&nbsp;
              Stok Saat Ini: <strong id="infoStok">-</strong>
            </span>
          </div>
        </div>

        <hr class="my-2">

        {{-- ===== TABEL KERANJANG ===== --}}
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0 tx-table">
            <thead>
              <tr>
                <th>Barang</th>
                <th class="text-end" width="120">Harga Beli</th>
                <th class="text-center" width="90">Qty</th>
                <th class="text-end" width="120">Subtotal</th>
                <th width="45"></th>
              </tr>
            </thead>
            <tbody id="bodyKeranjang">
              <tr id="emptyRow">
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="bi bi-box-seam d-block mb-1 tx-empty-icon"></i>
                  <span class="tx-empty-text">Belum ada barang ditambahkan</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div id="hiddenInputs"></div>

        {{-- ===== TOTAL + TOMBOL ===== --}}
        <div class="mt-2">
          <div class="tx-total-box">
            <div class="tx-total-label">Total Pembelian</div>
            <div id="totalPembelian" class="tx-total-value">Rp 0</div>
          </div>

          <div class="tx-action">
            <a href="{{ route('dashboard') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-times me-1"></i>Batal
            </a>
            <button type="submit" class="btn-ta btn-ta-blue" id="btnSimpan" disabled>
              <i class="fas fa-save me-1"></i>Simpan Restock
            </button>
          </div>
        </div>

      </div>
    </div>

    {{-- ===== CATATAN ===== --}}
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-pencil me-2"></i>Catatan <small class="text-muted fw-normal">(opsional)</small></h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <textarea name="catatan" class="form-control form-control-sm" rows="2"
                  placeholder="Catatan pembelian / restock..."></textarea>
      </div>
    </div>

  </div>
</div>

</form>
@endsection

@section('scripts')
<script>
// Data barang — semua barang aktif (termasuk stok 0, karena ini restock)
const daftarBarang = {!! json_encode(
    $barang->map(function($b) {
        $isi = max((int)($b->isi_per_satuan_beli ?? 1), 1);
        $harga = (float) (($b->harga_satuan_beli ?? 0) * $isi);
        return [
            'id'          => $b->id,
            'kode'        => $b->kode_barang,
            'nama'        => $b->nama_barang,
            'harga'       => $harga,
            'stok'        => (int) $b->stok,
            'satuan_beli' => $b->satuan_beli,
            'satuan_jual' => $b->satuan_jual,
            'isi'         => $isi,
        ];
    })->sortBy('nama')->values()
) !!};

let keranjang      = [];
let selectedBarang = null;

const searchInput = document.getElementById('searchBarang');
const dropdown    = document.getElementById('dropdownBarang');

// ── Autocomplete ──────────────────────────────────────────────────────────
searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (q.length === 0) { tutupDropdown(); return; }
    const hasil = daftarBarang.filter(b =>
        b.nama.toLowerCase().includes(q) || b.kode.toLowerCase().includes(q)
    );
    renderDropdown(hasil);
});

document.getElementById('btnToggleDropdown').addEventListener('click', function () {
    if (dropdown.style.display === 'block') {
        tutupDropdown();
    } else {
        renderDropdown(daftarBarang);
        searchInput.focus();
    }
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#searchBarang') &&
        !e.target.closest('#dropdownBarang') &&
        !e.target.closest('#btnToggleDropdown')) {
        tutupDropdown();
    }
});

function renderDropdown(hasil) {
    if (hasil.length === 0) {
        dropdown.innerHTML = '<div style="padding:8px 12px; color:#6c757d; font-size:13px;">Barang tidak ditemukan</div>';
        dropdown.style.display = 'block';
        return;
    }
    dropdown.innerHTML = hasil.map(function(b) {
        return '<div onclick="pilihBarang(' + b.id + ')" ' +
            'style="padding:7px 12px; font-size:13px; border-bottom:1px solid #f0f0f0; cursor:pointer;">' +
            '<span><strong>[' + b.kode + ']</strong> ' + b.nama + '</span>' +
            '<small style="float:right; color:#6c757d;">Stok: <strong>' + b.stok + ' ' + b.satuan_jual + '</strong></small>' +
            '</div>';
    }).join('');
    dropdown.style.display = 'block';
}

function tutupDropdown() {
    dropdown.style.display = 'none';
}

function pilihBarang(id) {
    selectedBarang = daftarBarang.find(function(b) { return b.id === id; });
    if (!selectedBarang) return;

    searchInput.value = '[' + selectedBarang.kode + '] ' + selectedBarang.nama;
    tutupDropdown();

    document.getElementById('infoHarga').textContent =
        'Rp ' + selectedBarang.harga.toLocaleString('id-ID') + ' / ' + selectedBarang.satuan_beli;
    document.getElementById('infoStok').textContent =
        selectedBarang.stok + ' ' + selectedBarang.satuan_jual +
        ' • Satuan Beli: ' + selectedBarang.satuan_beli +
        ' (isi ' + selectedBarang.isi + ' ' + selectedBarang.satuan_jual + ')';
    document.getElementById('inputJumlah').max   = '';
    document.getElementById('inputJumlah').value = 1;
    document.getElementById('infoBarang').classList.remove('d-none');
    document.getElementById('inputJumlah').focus();
    document.getElementById('inputJumlah').select();
}

// ── Keranjang ─────────────────────────────────────────────────────────────
function ubahJumlah(delta) {
    const input = document.getElementById('inputJumlah');
    const val   = parseInt(input.value) + delta;
    if (val >= 1) input.value = val;
}

function tambahKeKeranjang() {
    if (!selectedBarang) { alert('Pilih barang terlebih dahulu.'); return; }
    const jumlah = parseInt(document.getElementById('inputJumlah').value);
    if (!jumlah || jumlah < 1) { alert('Jumlah minimal 1.'); return; }

    const idx = keranjang.findIndex(function(k) { return k.barang_id === selectedBarang.id; });

    if (idx >= 0) {
        keranjang[idx].jumlah   += jumlah;
        keranjang[idx].subtotal  = selectedBarang.harga * keranjang[idx].jumlah;
    } else {
        keranjang.push({
            barang_id   : selectedBarang.id,
            kode        : selectedBarang.kode,
            nama        : selectedBarang.nama,
            satuan_beli : selectedBarang.satuan_beli,
            satuan_jual : selectedBarang.satuan_jual,
            isi         : selectedBarang.isi,
            harga       : selectedBarang.harga,
            jumlah      : jumlah,
            subtotal    : selectedBarang.harga * jumlah,
        });
    }

    renderKeranjang();

    selectedBarang = null;
    searchInput.value = '';
    document.getElementById('inputJumlah').value = 1;
    document.getElementById('infoBarang').classList.add('d-none');
    searchInput.focus();
}

function hapusItem(idx) {
    keranjang.splice(idx, 1);
    renderKeranjang();
}

function renderKeranjang() {
    const tbody     = document.getElementById('bodyKeranjang');
    const hidden    = document.getElementById('hiddenInputs');
    const btnSimpan = document.getElementById('btnSimpan');

    tbody.innerHTML  = '';
    hidden.innerHTML = '';

    if (keranjang.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-5">' +
            '<i class="bi bi-box-seam fs-2 d-block mb-2 tx-empty-icon"></i>' +
            '<span class="tx-empty-text">Belum ada barang ditambahkan</span>' +
            '</td></tr>';
        document.getElementById('totalPembelian').textContent = 'Rp 0';
        btnSimpan.disabled = true;
        return;
    }

    var total = 0;
    keranjang.forEach(function(item, i) {
        total += item.subtotal;
        var tr = document.createElement('tr');
        var pcsTotal = item.jumlah * item.isi;
        tr.innerHTML =
            '<td>' +
                '<div class="fw-semibold tx-cell-name">' + item.nama + '</div>' +
                '<small class="text-muted">' + item.kode + '</small>' +
            '</td>' +
            '<td class="text-end tx-cell-num">Rp ' + item.harga.toLocaleString('id-ID') + ' / ' + item.satuan_beli + '</td>' +
            '<td class="text-center tx-cell-num">' + item.jumlah + ' ' + item.satuan_beli +
                '<br><small class="text-muted">(' + pcsTotal + ' ' + item.satuan_jual + ')</small></td>' +
            '<td class="text-end fw-semibold tx-cell-num">Rp ' + item.subtotal.toLocaleString('id-ID') + '</td>' +
            '<td class="text-center">' +
                '<button type="button" class="btn-ta btn-ta-delete btn-ta-xs" onclick="hapusItem(' + i + ')" title="Hapus">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</td>';
        tbody.appendChild(tr);

        hidden.innerHTML +=
            '<input type="hidden" name="items[' + i + '][barang_id]" value="' + item.barang_id + '">' +
            '<input type="hidden" name="items[' + i + '][jumlah]" value="' + item.jumlah + '">';
    });

    document.getElementById('totalPembelian').textContent = 'Rp ' + total.toLocaleString('id-ID');
    btnSimpan.disabled = false;
}
</script>
@endsection
