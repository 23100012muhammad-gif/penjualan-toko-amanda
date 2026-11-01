<?php $__env->startSection('title', 'Riwayat Penjualan | Toko Amanda'); ?>
<?php $bodyClass = 'nav-md'; ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
  <div class="col-12">
    <h3 class="fw-bold mb-0 tx-heading">
      <i class="bi bi-clock-history me-2"></i>Riwayat Penjualan
    </h3>
    <small class="text-muted">Transaksi &rsaquo; Riwayat Penjualan</small>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="x_panel">
      <div class="x_title">
        <h4><i class="bi bi-receipt me-2"></i>Daftar Transaksi</h4>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0f4ff;border:1px solid #d0dcff;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#2a3f54;line-height:1.3;white-space:nowrap;">&#128230; <?php echo e($totalTransaksi); ?></div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Transaksi</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#f0fff4;border:1px solid #b7ebc8;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#1e8c45;line-height:1.3;white-space:nowrap;">&#128722; <?php echo e(number_format($totalItem, 0, ',', '.')); ?> <span style="font-size:10px;font-weight:400;">item</span></div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Barang Terjual</div>
          </div>
          <div style="display:inline-flex;flex-direction:column;justify-content:center;background:#fffbf0;border:1px solid #ffe0a0;border-radius:6px;padding:5px 12px;box-shadow:0 1px 3px rgba(0,0,0,.06);min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#b45309;line-height:1.3;white-space:nowrap;">&#128176; Rp <?php echo e(number_format($totalPendapatan, 0, ',', '.')); ?></div>
            <div style="font-size:10px;color:#6c757d;white-space:nowrap;">Total Penjualan</div>
          </div>
        </div>

        
        <form method="GET" action="<?php echo e(route('penjualan.histori')); ?>" class="mb-2">
          <div class="d-flex align-items-end gap-2 flex-wrap">

            <div>
              <label class="form-label" style="font-size:12px;color:#6c757d;">Kode Transaksi</label>
              <input type="text" name="keyword" class="form-control form-control-sm"
                     placeholder="Cari kode / nama barang..."
                     value="<?php echo e(request('keyword')); ?>" style="width:200px;">
            </div>

            <div>
              <label class="form-label" style="font-size:12px;color:#6c757d;">Dari Tanggal</label>
              <input type="date" name="tanggal_dari" class="form-control form-control-sm"
                     value="<?php echo e(request('tanggal_dari')); ?>">
            </div>

            <div>
              <label class="form-label" style="font-size:12px;color:#6c757d;">Sampai Tanggal</label>
              <input type="date" name="tanggal_sampai" class="form-control form-control-sm"
                     value="<?php echo e(request('tanggal_sampai')); ?>">
            </div>

            <div>
              <button type="submit" class="btn-ta btn-ta-dark">
                <i class="fas fa-filter me-1"></i>Filter
              </button>
              <?php if(request('keyword') || request('tanggal_dari') || request('tanggal_sampai')): ?>
              <a href="<?php echo e(route('penjualan.histori')); ?>" class="btn-ta btn-ta-gray ms-1">
                <i class="fas fa-times me-1"></i>Reset
              </a>
              <?php endif; ?>
            </div>

          </div>
        </form>

        <div class="mb-2" style="font-size:12px;color:#6c757d;">
          Menampilkan <strong><?php echo e($totalTransaksi); ?></strong> transaksi
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle tx-table">
            <thead>
              <tr>
                <th width="50">No</th>
                <th width="130">Kode Transaksi</th>
                <th width="120">Tanggal</th>
                <th class="text-end" width="150">Total</th>
                <th class="text-center" width="70">Item</th>
                <th width="100">Status</th>
                <th class="text-center" width="80">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td class="text-muted small"><?php echo e($loop->iteration); ?></td>
                <td><code class="text-primary fw-semibold"><?php echo e($item->kode_transaksi); ?></code></td>
                <td><?php echo e($item->tanggal->format('d-m-Y')); ?></td>
                <td class="text-end fw-semibold text-success">
                  Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?>

                </td>
                <td class="text-center">
                  <span class="badge bg-secondary"><?php echo e($item->detailPenjualan->count()); ?> item</span>
                </td>
                <td>
                  <?php if($item->status === 'selesai'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Batal</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <button type="button" class="btn-ta btn-ta-blue"
                          data-bs-toggle="modal"
                          data-bs-target="#modalDetail<?php echo e($item->id); ?>">
                    <i class="bi bi-eye"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                  <?php echo e(request('keyword') || request('tanggal_dari') || request('tanggal_sampai') ? 'Tidak ada data ditemukan' : 'Belum ada transaksi'); ?>

                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
          <?php echo e($penjualan->links()); ?>

        </div>

      </div>
    </div>
  </div>
</div>


<?php $__currentLoopData = $penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="modalDetail<?php echo e($item->id); ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-receipt-cutoff me-2 text-primary"></i>
          Detail — <?php echo e($item->kode_transaksi); ?>

        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <div class="row mb-3">
          <div class="col-6">
            <small class="text-muted d-block">Tanggal</small>
            <strong><?php echo e($item->tanggal->format('d F Y')); ?></strong>
          </div>
          <div class="col-6">
            <small class="text-muted d-block">Status</small>
            <?php if($item->status === 'selesai'): ?>
              <span class="badge bg-success">Selesai</span>
            <?php else: ?>
              <span class="badge bg-danger">Batal</span>
            <?php endif; ?>
          </div>
        </div>

        <?php if($item->catatan): ?>
        <div class="mb-3">
          <small class="text-muted d-block">Catatan</small>
          <p class="mb-0"><?php echo e($item->catatan); ?></p>
        </div>
        <?php endif; ?>

        <hr class="my-2">

        <table class="table table-sm table-bordered mb-0">
          <thead style="background:#f8f9fa;">
            <tr>
              <th>Barang</th>
              <th class="text-center" width="90">Qty</th>
              <th class="text-end" width="120">Harga</th>
              <th class="text-end" width="130">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $item->detailPenjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td>
                <span class="fw-semibold"><?php echo e($detail->barang->nama_barang); ?></span>
                <br><small class="text-muted"><?php echo e($detail->barang->kode_barang); ?></small>
              </td>
              <td class="text-center"><?php echo e($detail->jumlah); ?> <?php echo e(strtoupper($detail->barang->satuan_jual)); ?></td>
              <td class="text-end">Rp <?php echo e(number_format($detail->harga, 0, ',', '.')); ?></td>
              <td class="text-end fw-semibold">Rp <?php echo e(number_format($detail->subtotal, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
          <tfoot style="background:#f8f9fa;">
            <tr>
              <td colspan="3" class="text-end fw-bold">Total</td>
              <td class="text-end fw-bold text-success">
                Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?>

              </td>
            </tr>
          </tfoot>
        </table>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>