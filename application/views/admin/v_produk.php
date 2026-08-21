<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-white mb-1">Daftar Produk</h2>
        <p class="text-muted small mb-0">Kelola katalog pintu dan aksesoris Anda.</p>
    </div>
    <a href="<?= base_url('admin/produk/tambah') ?>" class="btn btn-admin-primary">
        <i class="fas fa-plus me-2"></i> Tambah Produk
    </a>
</div>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-4 mb-4">
        <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<div class="admin-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr>
                    <th class="ps-4" width="100">Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th width="100">Stok</th>
                    <th class="text-end pe-4" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($products)): foreach($products as $p): ?>
                <tr>
                    <td class="ps-4">
                        <img src="<?= $p->image && $p->image !== 'default.jpg' ? base_url('uploads/products/'.$p->image) : 'https://placehold.co/50x50/161b22/8b949e?text=P' ?>" class="rounded-3 border border-secondary border-opacity-20" style="width: 56px; height: 56px; object-fit: cover;">
                    </td>
                    <td>
                        <div class="fw-bold text-white mb-1"><?= htmlspecialchars($p->name) ?></div>
                        <div class="small text-muted" style="font-size: 0.7rem;">ID: #<?= $p->id ?></div>
                    </td>
                    <td><span class="admin-badge badge-paid" style="background: rgba(255,255,255,0.05); color: #fff; font-size: 0.65rem;"><?= htmlspecialchars($p->category_name) ?></span></td>
                    <td class="fw-bold text-white">Rp <?= number_format($p->price, 0, ',', '.') ?></td>
                    <td>
                        <?php if($p->stock < 5): ?>
                            <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i><?= $p->stock ?></span>
                        <?php else: ?>
                            <span class="text-success fw-bold"><?= $p->stock ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= base_url('admin/produk/edit/'.$p->id) ?>" class="btn btn-sm btn-admin-outline" style="padding: 8px 12px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= base_url('admin/produk/hapus/'.$p->id) ?>" class="btn btn-sm btn-outline-danger" style="padding: 8px 12px;" onclick="return confirm('Hapus produk ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" class="text-center text-muted py-5">Belum ada produk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(!empty($pagination)): ?>
    <div class="mt-4 pb-2">
        <?= $pagination ?>
    </div>
    <?php endif; ?>
</div>
