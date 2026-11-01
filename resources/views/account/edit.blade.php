@extends('layouts.app')

@section('title', 'Pengaturan Akun | Toko Amanda')

@php $bodyClass = 'nav-md'; @endphp

@section('content')
<h1 class="visually-hidden">Pengaturan Akun</h1>

{{-- Page Header --}}
<div class="mb-3">
  <h3 class="fw-bold mb-1" style="color:#2a3f54;font-size:18px;">
    <i class="fas fa-user-cog me-2"></i>Pengaturan Akun
  </h3>
  <p class="text-muted mb-0" style="font-size:13px;">Kelola foto profil, informasi akun, dan keamanan Anda.</p>
</div>

<div class="row g-3">

  {{-- Kolom Kiri: Foto Profil (lebih sempit, berdiri sendiri) --}}
  <div class="col-12 col-lg-3">
    <div class="x_panel" style="margin-bottom:0;">
      <div class="x_title">
        <h4 style="font-size:13px;font-weight:600;"><i class="fas fa-camera me-2"></i>Foto Profil</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        @include('account.partials.update-avatar-form')
      </div>
    </div>
  </div>

  {{-- Kolom Kanan: 3 card ditumpuk --}}
  <div class="col-12 col-lg-9">
    <div class="row g-3">

      {{-- Informasi Akun --}}
      <div class="col-12">
        <div class="x_panel" style="margin-bottom:0;">
          <div class="x_title">
            <h4 style="font-size:13px;font-weight:600;"><i class="fas fa-id-card me-2"></i>Informasi Akun</h4>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            @include('account.partials.update-information-form')
          </div>
        </div>
      </div>

      {{-- Ganti Password --}}
      <div class="col-12">
        <div class="x_panel" style="margin-bottom:0;">
          <div class="x_title">
            <h4 style="font-size:13px;font-weight:600;"><i class="fas fa-lock me-2"></i>Ganti Password</h4>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            @include('account.partials.update-password-form')
          </div>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
