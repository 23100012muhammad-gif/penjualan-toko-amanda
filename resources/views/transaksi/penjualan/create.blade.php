@extends('layouts.app')

@section('title', 'Transaksi Penjualan | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-cart-check me-2"></i>Transaksi Penjualan
    </h3>
    <small class="text-muted">Transaksi &rsaquo; Penjualan Baru</small>
  </div>
</div>

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if (session('error_stok'))
  <div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="fas fa-exclamation-circle me-1"></i>Tidak dapat menyimpan:</strong>
    <ul class="mb-0 mt-1">
      @foreach (session('error_stok') as $err)
        <li><small>{{ $err }}</small></li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<form action="{{ route('penjualan.store') }}" method="POST" id="formTransaksi">
@csrf

<div class="row">
  <div class="col-xl-9 col-lg-10 col-12">
    <div class="x_panel">
      <div class="x_content">

        {{-- ===== TANGGAL TRANSAKSI ===== --}}
        <div class="mb-3">
          <label for="tanggal_transaksi" class="form-label fw-semibold">
            Tanggal Transaksi <span class="text-danger">*</span>
          </label>
          <input type="date" id="tanggal_transaksi" name="tanggal_transaksi"
                 class="form-control @error('tanggal_transaksi') is-invalid @enderror"
                 value="{{ old('tanggal_transaksi', date('Y-m-d')) }}"
                 style="max-width:200px;">
          <div class="form-text">Tanggal transaksi penjualan. Digunakan untuk histori dan laporan penjualan.</div>
          @error('tanggal_transaksi')
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

              {{-- Input ketik + tombol ▼ --}}
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

              {{-- Dropdown hasil pencarian --}}
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
              Harga: <strong id="infoHarga" class="text-success">-</strong>
              &nbsp;&bull;&nbsp;
              Stok: <strong id="infoStok">-</strong>
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
                <th class="text-end" width="120">Harga</th>
                <th class="text-center" width="90">Qty</th>
                <th class="text-end" width="120">Subtotal</th>
                <th width="45"></th>
              </tr>
            </thead>
            <tbody id="bodyKeranjang">
              <tr id="emptyRow">
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="bi bi-cart3 d-block mb-1 tx-empty-icon"></i>
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
            <div class="tx-total-label">Total</div>
            <div id="totalTransaksi" class="tx-total-value">Rp 0</div>
          </div>

          <div class="tx-action">
            <a href="{{ route('dashboard') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-times me-1"></i>Batal
            </a>
            <button type="submit" class="btn-ta btn-ta-green" id="btnSimpan" disabled>
              <i class="fas fa-save me-1"></i>Simpan Transaksi
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
                  placeholder="Catatan transaksi..."></textarea>
      </div>
    </div>

  </div>
</div>

</form>
@endsection

@section('scripts')
<script>
// ── Data barang dari server ───────────────────────────────────────────────
const daftarBarang = {!! json_encode(
    $barang->map(function($b) {
        return [
            'id'     => $b->id,
            'kode'   => $b->kode_barang,
            'nama'   => $b->nama_barang,
            'harga'  => (float) $b->harga_jual,
            'stok'   => (int) $b->stok,
            'satuan' => $b->satuan_jual,
            'habis'  => $b->stok <= 0,
        ];
    })->sortBy('nama')->values()
) !!};

// ── State ─────────────────────────────────────────────────────────────────
let keranjang      = [];
let selectedBarang = null;   // objek barang yang sedang dipilih

// ── Elemen ────────────────────────────────────────────────────────────────
const searchInput = document.getElementById('searchBarang');
const dropdown    = document.getElementById('dropdownBarang');

// ── Autocomplete logic ────────────────────────────────────────────────────
searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (q.length === 0) { tutupDropdown(); return; }

    const hasil = daftarBarang.filter(b =>
        b.nama.toLowerCase().includes(q) ||
        b.kode.toLowerCase().includes(q)
    );
    renderDropdown(hasil);
});

// Tombol ▼ — toggle semua barang
document.getElementById('btnToggleDropdown').addEventListener('click', function () {
    if (dropdown.style.display === 'block') {
        tutupDropdown();
    } else {
        renderDropdown(daftarBarang);
        searchInput.focus();
    }
});

// Klik di luar → tutup dropdown
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

    dropdown.innerHTML = hasil.map(b => {
        const style = b.habis ? 'color:#adb5bd; cursor:not-allowed;' : 'cursor:pointer;';
        const stokLabel = b.habis
            ? '<span style="color:#dc3545; font-weight:600;">(Habis)</span>'
            : 'Stok: <strong>' + b.stok + ' ' + b.satuan + '</strong>';
        return '<div class="dropdown-item-barang" ' +
            (b.habis ? '' : 'onclick="pilihBarang(' + b.id + ')"') +
            ' style="padding:7px 12px; font-size:13px; border-bottom:1px solid #f0f0f0; ' + style + '">' +
            '<span><strong>[' + b.kode + ']</strong> ' + b.nama + '</span>' +
            '<small style="float:right; color:#6c757d;">' + stokLabel + '</small>' +
            '</div>';
    }).join('');
    dropdown.style.display = 'block';
}

function tutupDropdown() {
    dropdown.style.display = 'none';
}

function pilihBarang(id) {
    selectedBarang = daftarBarang.find(b => b.id === id);
    if (!selectedBarang) return;

    searchInput.value = '[' + selectedBarang.kode + '] ' + selectedBarang.nama;
    tutupDropdown();

    document.getElementById('infoHarga').textContent =
        'Rp ' + selectedBarang.harga.toLocaleString('id-ID') + ' / ' + selectedBarang.satuan;
    document.getElementById('infoStok').textContent =
        selectedBarang.stok + ' ' + selectedBarang.satuan;
    document.getElementById('inputJumlah').max   = selectedBarang.stok;
    document.getElementById('inputJumlah').value = 1;
    document.getElementById('infoBarang').classList.remove('d-none');
    document.getElementById('inputJumlah').focus();
    document.getElementById('inputJumlah').select();
}

// ── Keranjang ─────────────────────────────────────────────────────────────
function ubahJumlah(delta) {
    const input = document.getElementById('inputJumlah');
    const val   = parseInt(input.value) + delta;
    const max   = parseInt(input.max) || 9999;
    if (val >= 1 && val <= max) input.value = val;
}

function tambahKeKeranjang() {
    if (!selectedBarang) { alert('Pilih barang terlebih dahulu.'); return; }
    const jumlah = parseInt(document.getElementById('inputJumlah').value);
    if (jumlah < 1) { alert('Jumlah minimal 1.'); return; }

    const stok = selectedBarang.stok;
    const idx  = keranjang.findIndex(k => k.barang_id === selectedBarang.id);

    if (idx >= 0) {
        const totalJumlah = keranjang[idx].jumlah + jumlah;
        if (totalJumlah > stok) {
            alert('Stok ' + selectedBarang.nama + ' tidak cukup.\nTersedia: ' + stok + ' ' + selectedBarang.satuan + '.');
            return;
        }
        keranjang[idx].jumlah   = totalJumlah;
        keranjang[idx].subtotal = selectedBarang.harga * totalJumlah;
    } else {
        if (jumlah > stok) {
            alert('Stok ' + selectedBarang.nama + ' tidak cukup.\nTersedia: ' + stok + ' ' + selectedBarang.satuan + '.');
            return;
        }
        keranjang.push({
            barang_id : selectedBarang.id,
            kode      : selectedBarang.kode,
            nama      : selectedBarang.nama,
            satuan    : selectedBarang.satuan,
            harga     : selectedBarang.harga,
            stok      : stok,
            jumlah    : jumlah,
            subtotal  : selectedBarang.harga * jumlah,
        });
    }

    renderKeranjang();

    // Reset
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
        tbody.innerHTML = `
          <tr id="emptyRow">
            <td colspan="5" class="text-center text-muted py-5">
              <i class="bi bi-cart3 fs-2 d-block mb-2 tx-empty-icon"></i>
              <span class="tx-empty-text">Belum ada barang ditambahkan</span>
            </td>
          </tr>`;
        document.getElementById('totalTransaksi').textContent = 'Rp 0';
        btnSimpan.disabled = true;
        return;
    }

    let total = 0;
    keranjang.forEach((item, i) => {
        total += item.subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>
            <div class="fw-semibold tx-cell-name">${item.nama}</div>
            <small class="text-muted">${item.kode}</small>
          </td>
          <td class="text-end tx-cell-num">Rp ${item.harga.toLocaleString('id-ID')}</td>
          <td class="text-center tx-cell-num">${item.jumlah} ${item.satuan}</td>
          <td class="text-end fw-semibold tx-cell-num">Rp ${item.subtotal.toLocaleString('id-ID')}</td>
          <td class="text-center">
            <button type="button" class="btn-ta btn-ta-delete btn-ta-xs"
                    onclick="hapusItem(${i})" title="Hapus">
              <i class="fas fa-trash"></i>
            </button>
          </td>`;
        tbody.appendChild(tr);

        hidden.innerHTML += `
          <input type="hidden" name="items[${i}][barang_id]" value="${item.barang_id}">
          <input type="hidden" name="items[${i}][jumlah]"    value="${item.jumlah}">`;
    });

    document.getElementById('totalTransaksi').textContent =
        'Rp ' + total.toLocaleString('id-ID');
    btnSimpan.disabled = false;
}
</script>
@endsection
