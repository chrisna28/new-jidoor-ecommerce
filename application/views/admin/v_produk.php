<?php if ($s = $this->session->flashdata('success')): ?><div class="js-flash d-none" data-type="success" data-msg="<?= htmlspecialchars($s) ?>"></div><?php endif; ?>
<?php if ($e = $this->session->flashdata('error')): ?><div class="js-flash d-none" data-type="error" data-msg="<?= htmlspecialchars($e) ?>"></div><?php endif; ?>

<div class="page-head toolbar">
    <div>
        <h2 class="page-title">Daftar Produk</h2>
        <p class="page-sub">Kelola katalog pintu dan aksesoris Anda.</p>
    </div>
    <a href="<?= base_url('admin/produk/tambah') ?>" class="btn btn-admin-primary">
        <i class="fas fa-plus me-2"></i> Tambah Produk
    </a>
</div>

<div class="admin-card p-0">
    <div class="px-4 py-3 border-bottom" style="border-color: var(--border) !important;">
        <input type="text" id="productSearch" class="form-control form-control-admin" style="max-width: 320px;" placeholder="Cari nama produk atau ID..." autocomplete="off">
    </div>
    <div class="table-responsive">
        <table class="table table-admin mb-0" id="productTable">
            <thead>
                <tr>
                    <th class="ps-4" width="100">Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th width="100">Stok</th>
                    <th class="text-end pe-4" width="110">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($products)): foreach($products as $p): ?>
                <tr data-name="<?= strtolower(htmlspecialchars($p->name)) ?>" data-id="<?= $p->id ?>">
                    <td class="ps-4">
                        <img src="<?= $p->image && $p->image !== 'default.jpg' ? base_url('uploads/products/'.$p->image) : 'https://placehold.co/56x56/f1f5f9/94a3b8?text=P' ?>" class="rounded border" style="width: 52px; height: 52px; object-fit: cover;">
                    </td>
                    <td>
                        <div class="fw-bold mb-1"><?= htmlspecialchars($p->name) ?></div>
                        <div class="text-muted" style="font-size: 0.72rem;">ID: #<?= $p->id ?></div>
                    </td>
                    <td><span class="badge-neutral"><?= htmlspecialchars($p->category_name) ?></span></td>
                    <td class="fw-bold num">Rp <?= number_format($p->price, 0, ',', '.') ?></td>
                    <td>
                        <?php if($p->stock < 5): ?>
                            <span class="admin-badge badge-rejected"><i class="fas fa-triangle-exclamation"></i><?= $p->stock ?></span>
                        <?php else: ?>
                            <span class="admin-badge badge-completed"><?= $p->stock ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="<?= base_url('admin/produk/edit/'.$p->id) ?>" class="icon-btn" title="Edit produk">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= base_url('admin/produk/hapus/'.$p->id) ?>" class="icon-btn btn-delete"
                               data-confirm="Hapus produk &quot;<?= htmlspecialchars($p->name) ?>&quot; secara permanen?"
                               data-confirm-title="Hapus Produk" title="Hapus produk">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                        <h6>Belum ada produk</h6>
                        <p>Tambahkan produk pertama Anda ke katalog.</p>
                        <a href="<?= base_url('admin/produk/tambah') ?>" class="btn btn-admin-primary"><i class="fas fa-plus me-2"></i>Tambah Produk</a>
                    </div>
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(!empty($pagination)): ?>
    <div class="mt-4 pb-4 px-4">
        <?= $pagination ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('productSearch');
    var rows = document.querySelectorAll('#productTable tbody tr[data-name]');
    input.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        rows.forEach(function (r) {
            r.style.display = (!q || r.dataset.name.indexOf(q) !== -1 || r.dataset.id === q.replace('#', '')) ? '' : 'none';
        });
    });
});
</script>
