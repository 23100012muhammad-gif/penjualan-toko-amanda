@if (session('status') === 'avatar-updated')
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-1"></i> Foto profil berhasil diperbarui.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Preview Foto Profil --}}
<div class="text-center mb-3">
  <img id="avatarPreview"
       src="{{ Auth::user()->avatarUrl() }}"
       alt="Foto Profil"
       class="rounded-circle border"
       style="width:90px;height:90px;object-fit:cover;border-width:3px !important;border-color:#26B99A !important;">
  <p class="text-muted small mt-2 mb-0">{{ Auth::user()->name }}</p>
</div>

{{-- Form Upload --}}
<form method="POST" action="{{ route('account.avatar') }}" enctype="multipart/form-data">
  @csrf

  <div class="mb-3">
    <label for="avatar" class="form-label fw-semibold">Pilih Foto Baru</label>
    <input id="avatar" type="file" name="avatar" accept="image/jpg,image/jpeg,image/png,image/webp"
           class="form-control @error('avatar') is-invalid @enderror"
           onchange="previewAvatar(this)">
    @error('avatar')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Format: JPG, PNG, WEBP. Maks. 2MB.</div>
  </div>

  <div class="d-flex">
    <button type="submit" class="btn-ta btn-ta-blue">
      <i class="fas fa-upload me-1"></i> Simpan Foto
    </button>
  </div>
</form>

@section('scripts')
  @parent
  <script>
    function previewAvatar(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>
@endsection
