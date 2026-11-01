@extends('layouts.app')

@section('title', 'Tambah Kategori Pengeluaran | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-plus-circle me-2"></i>Tambah Kategori Pengeluaran
    </h3>
    <small class="text-muted">Master Data › Kategori Pengeluaran › Tambah</small>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-wallet2 me-2"></i>Form Tambah Kategori Pengeluaran</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{ route('kategori-pengeluaran.store') }}" method="POST">
          @csrf

          <div class="mb-3">
            <label for="nama_kategori" class="form-label fw-semibold">
              Nama Kategori <span class="text-danger">*</span>
            </label>
            <input type="text" id="nama_kategori" name="nama_kategori"
                   class="form-control @error('nama_kategori') is-invalid @enderror"
                   value="{{ old('nama_kategori') }}"
                   placeholder="Contoh: BBM, Listrik, Gaji, Sewa"
                   autofocus>
            @error('nama_kategori')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
            <input type="text" id="keterangan" name="keterangan"
                   class="form-control @error('keterangan') is-invalid @enderror"
                   value="{{ old('keterangan') }}"
                   placeholder="Keterangan singkat (opsional)">
            @error('keterangan')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="status" class="form-label fw-semibold">
              Status <span class="text-danger">*</span>
            </label>
            <select id="status" name="status"
                    class="form-select @error('status') is-invalid @enderror">
              <option value="aktif"    {{ old('status', 'aktif') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
              <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Simpan
            </button>
            <a href="{{ route('kategori-pengeluaran.index') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
