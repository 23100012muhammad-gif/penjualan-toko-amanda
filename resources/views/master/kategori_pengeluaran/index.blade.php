@extends('layouts.app')

@section('title', 'Kategori Pengeluaran | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp

@section('content')
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-0 tx-heading">
        <i class="bi bi-wallet2 me-2"></i>Kategori Pengeluaran
      </h3>
      <small class="text-muted">Master Data › Kategori Pengeluaran</small>
    </div>
    <a href="{{ route('kategori-pengeluaran.create') }}" class="btn-ta btn-ta-blue">
      <i class="fas fa-plus me-1"></i> Tambah Kategori
    </a>
  </div>
</div>

<x-alert />

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-wallet2 me-2"></i>Daftar Kategori Pengeluaran</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="table-responsive">
          <table class="table table-striped table-hover tx-table">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Nama Kategori</th>
                <th>Keterangan</th>
                <th class="text-center" width="100">Status</th>
                <th class="text-center" width="90">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($kategori as $item)
              <tr>
                <td class="text-muted small">{{ $kategori->firstItem() + $loop->index }}</td>
                <td class="fw-semibold">{{ $item->nama_kategori }}</td>
                <td class="text-muted" style="font-size:13px;">{{ $item->keterangan ?: '-' }}</td>
                <td class="text-center">
                  @if ($item->status === 'aktif')
                    <span class="badge bg-success">Aktif</span>
                  @else
                    <span class="badge bg-secondary">Nonaktif</span>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route('kategori-pengeluaran.edit', $item) }}"
                     class="btn-ta btn-ta-edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                  Belum ada data kategori pengeluaran.
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
