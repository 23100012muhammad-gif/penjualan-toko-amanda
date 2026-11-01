@extends('layouts.app')

@section('title', 'Edit Pengeluaran | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-pencil-square me-2"></i>Edit Pengeluaran
    </h3>
    <small class="text-muted">Pengeluaran › Edit</small>
  </div>
</div>

<div class="row">
  <div class="col-md-7">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-cash-stack me-2"></i>Form Edit Pengeluaran</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{ route('pengeluaran.update', $pengeluaran) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label for="tanggal" class="form-label fw-semibold">
              Tanggal <span class="text-danger">*</span>
            </label>
            <input type="date" id="tanggal" name="tanggal"
                   class="form-control @error('tanggal') is-invalid @enderror"
                   value="{{ old('tanggal', $pengeluaran->tanggal->format('Y-m-d')) }}">
            @error('tanggal')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="kategori_pengeluaran_id" class="form-label fw-semibold">
              Kategori Pengeluaran <span class="text-danger">*</span>
            </label>
            <select id="kategori_pengeluaran_id" name="kategori_pengeluaran_id"
                    class="form-select @error('kategori_pengeluaran_id') is-invalid @enderror">
              <option value="">— Pilih Kategori —</option>
              @foreach ($kategori as $kat)
              <option value="{{ $kat->id }}"
                {{ (string) old('kategori_pengeluaran_id', $pengeluaran->kategori_pengeluaran_id) === (string) $kat->id ? 'selected' : '' }}>
                {{ $kat->nama_kategori }}
                @if ($kat->status === 'nonaktif')
                  (Nonaktif)
                @endif
              </option>
              @endforeach
            </select>
            @error('kategori_pengeluaran_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="nominal" class="form-label fw-semibold">
              Nominal (Rp) <span class="text-danger">*</span>
            </label>
            <input type="number" id="nominal" name="nominal" min="0" step="0.01"
                   class="form-control @error('nominal') is-invalid @enderror"
                   value="{{ old('nominal', $pengeluaran->nominal) }}">
            @error('nominal')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3"
                      class="form-control @error('keterangan') is-invalid @enderror"
                      placeholder="Detail pengeluaran (opsional)">{{ old('keterangan', $pengeluaran->keterangan) }}</textarea>
            @error('keterangan')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <small class="text-muted">
              Dicatat oleh: <strong>{{ $pengeluaran->user->name ?? '-' }}</strong>
            </small>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn-ta btn-ta-blue">
              <i class="fas fa-save me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('pengeluaran.index') }}" class="btn-ta btn-ta-gray">
              <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
