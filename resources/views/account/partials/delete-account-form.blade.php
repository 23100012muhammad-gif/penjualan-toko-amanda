<p class="text-muted small">
  Setelah akun dihapus, semua data akan hilang secara permanen.
  Pastikan Anda sudah menyimpan data penting sebelum melanjutkan.
</p>

<button type="button" class="btn-ta btn-ta-delete" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
  <i class="fas fa-trash-alt me-1"></i> Hapus Akun Saya
</button>

{{-- Modal Konfirmasi Hapus Akun --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-danger">
        <h5 class="modal-title text-danger" id="deleteAccountModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Akun
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('account.destroy') }}">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p class="text-muted">
            Tindakan ini <strong>tidak dapat dibatalkan</strong>. Masukkan password Anda untuk mengonfirmasi.
          </p>
          <div class="mb-3">
            <label for="delete_password" class="form-label fw-semibold">Password</label>
            <input id="delete_password" type="password" name="password"
                   class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                   placeholder="Masukkan password Anda">
            @error('password', 'userDeletion')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-ta btn-ta-gray" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-ta btn-ta-delete">
            <i class="fas fa-trash-alt me-1"></i> Ya, Hapus Akun
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Buka modal otomatis jika ada error validasi --}}
@if ($errors->userDeletion->isNotEmpty())
<script>
  document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
  });
</script>
@endif
