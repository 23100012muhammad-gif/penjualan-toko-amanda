@extends('layouts.app')

@section('title', 'Kategori | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-0 tx-heading">
        <i class="bi bi-tags me-2"></i>Kategori Barang
      </h3>
      <small class="text-muted">Master Data › Kategori</small>
    </div>
    <a href="{{ route('kategori.create') }}" class="btn-ta btn-ta-blue">
      <i class="fas fa-plus me-1"></i> Tambah Kategori
    </a>
  </div>
</div>

<x-alert />

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4>Daftar Kategori</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="table-responsive">
          <table class="table table-striped table-hover tx-table">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Nama Kategori</th>
                <th width="130" class="text-center">Satuan Beli</th>
                <th width="130" class="text-center">Satuan Jual</th>
                <th width="110" class="text-center">Jumlah Barang</th>
                <th width="110" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($kategori as $item)
              <tr>
                <td class="text-muted small">{{ $kategori->firstItem() + $loop->index }}</td>
                <td>
                  <span class="fw-semibold">{{ $item->nama_kategori }}</span>
                  @if ($item->kode_prefix)
                    <span class="badge bg-light text-secondary border ms-1 small" style="font-family:monospace; letter-spacing:1px;">
                      {{ $item->kode_prefix }}
                    </span>
                  @endif
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary">{{ ucfirst(strtolower($item->default_satuan_beli)) }}</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-info text-white">{{ ucfirst(strtolower($item->default_satuan_jual)) }}</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-primary">{{ $item->barang_count }}</span>
                </td>
                <td class="text-center">
                  <a href="{{ route('kategori.edit', $item) }}" class="btn-ta btn-ta-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('kategori.destroy', $item) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus kategori \'{{ $item->nama_kategori }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ta btn-ta-delete" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                  Belum ada data kategori.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
          {{ $kategori->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
