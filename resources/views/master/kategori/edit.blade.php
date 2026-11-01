@extends('layouts.app')

@section('title', 'Edit Kategori | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-pencil-square me-2"></i>Edit Kategori
    </h3>
    <small class="text-muted">Master Data › Kategori › Edit</small>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-tags me-2"></i>Edit Kategori Barang</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{ route('kategori.update', $kategori) }}" method="POST">
          @csrf @method('PATCH')

          <div class="mb-3">
            <label for="nama_kategori" class="form-label fw-semibold">
              Nama Kategori <span class="text-danger">*</span>
            </label>
            <input type="text" id="nama_kategori" name="nama_kategori"
                   class="form-control @error('nama_kategori') is-invalid @enderror"
                   value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                   autofocus>
            <div class="form-text text-muted">
              <i class="bi bi-magic me-1"></i>Prefix kategori dikelola otomatis oleh sistem.
            </div>
            @error('nama_kategori')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          @if ($kategori->kode_prefix)
          <div class="mb-3">
            <small class="text-muted">
              Prefix sistem:
              <span class="badge bg-light text-secondary border fw-semibold" style="font-family:monospace; letter-spacing:2px;">
                {{ $kategori->kode_prefix }}
              </span>
            </small>
          </div>
          @endif

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="default_satuan_beli" class="form-label fw-semibold">
                Satuan Beli Default <span class="text-danger">*</span>
              </label>
              <select id="default_satuan_beli" name="default_satuan_beli"
                      class="form-select @error('default_satuan_beli') is-invalid @enderror">
                @foreach (['Pcs','Botol','Bungkus','Sachet','Pack','Kardus','Dus','Karton','Lusin','Kg','Liter'] as $s)
                  <option value="{{ $s }}"
                    {{ old('default_satuan_beli', $kategori->default_satuan_beli) == $s ? 'selected' : '' }}>
                    {{ $s }}
                  </option>
                @endforeach
              </select>
              @error('default_satuan_beli')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="default_satuan_jual" class="form-label fw-semibold">
                Satuan Jual Default <span class="text-danger">*</span>
              </label>
              <select id="default_satuan_jual" name="default_satuan_jual"
                      class="form-select @error('default_satuan_jual') is-invalid @enderror">
                @foreach (['Pcs','Botol','Bungkus','Sachet','Pack','Kg','Gram','Liter','Ml'] as $s)
                  <option value="{{ $s }}"
                    {{ old('default_satuan_jual', $kategori->default_satuan_jual) == $s ? 'selected' : '' }}>
                    {{ $s }}
                  </option>
                @endforeach
              </select>
              @error('default_satuan_jual')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Perbarui
            </button>
            <a href="{{ route('kategori.index') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
