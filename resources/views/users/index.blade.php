@extends('layouts.app')
 
@section('title', 'Kelola User | Toko Amanda')
@php $bodyClass = 'nav-md'; @endphp
 
@section('content')
<div class="row mb-3">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <div>
      <h3 class="fw-bold mb-0 tx-heading">
        <i class="bi bi-people me-2"></i>Kelola User
      </h3>
      <small class="text-muted">Pengaturan › Kelola User</small>
    </div>
    <a href="{{ route('users.create') }}" class="btn-ta btn-ta-blue">
      <i class="fas fa-plus me-1"></i> Tambah User
    </a>
  </div>
</div>
 
<x-alert />
 
<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4>Daftar User Sistem</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="table-responsive">
          <table class="table table-striped table-hover tx-table">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Nama</th>
                <th>Email</th>
                <th width="150" class="text-center">Role</th>
                <th width="120" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $item)
              <tr>
                <td class="text-muted small">{{ $users->firstItem() + $loop->index }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img src="{{ $item->avatarUrl() }}" alt=""
                         style="width:30px;height:30px;object-fit:cover;border-radius:50%;">
                    <span class="fw-semibold">{{ $item->name }}</span>
                    @if(Auth::id() === $item->id)
                      <span class="badge bg-light text-primary border small">Anda</span>
                    @endif
                  </div>
                </td>
                <td>{{ $item->email }}</td>
                <td class="text-center">
                  @if($item->role === 'pemilik')
                    <span class="badge bg-success" style="font-size:11px;">Pemilik Toko</span>
                  @elseif($item->role === 'admin')
                    <span class="badge bg-primary" style="font-size:11px;">Admin</span>
                  @elseif($item->role === 'kasir')
                    <span class="badge bg-info text-white" style="font-size:11px;">Kasir</span>
                  @else
                    <span class="badge bg-secondary" style="font-size:11px;">{{ ucfirst($item->role) }}</span>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route('users.edit', $item) }}" class="btn-ta btn-ta-edit" title="Edit">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                  Belum ada data user.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
          {{ $users->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
