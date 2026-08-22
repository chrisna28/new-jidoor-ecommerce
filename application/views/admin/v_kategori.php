<?php if ($s = $this->session->flashdata('success')): ?><div class="js-flash d-none" data-type="success" data-msg="<?= htmlspecialchars($s) ?>"></div><?php endif; ?>
<?php if ($e = $this->session->flashdata('error')): ?><div class="js-flash d-none" data-type="error" data-msg="<?= htmlspecialchars($e) ?>"></div><?php endif; ?>

<div class="page-head toolbar">
    <div>
        <h2 class="page-title">Manajemen Kategori</h2>
        <p class="page-sub">Kelola kategori produk yang akan tampil di katalog.</p>
    </div>
    <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-plus me-2"></i> Tambah Kategori
    </button>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr>
                    <th class="ps-4" width="80">No</th>
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($categories)): $no=1; foreach($categories as $c): ?>
                <tr>
                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($c->name) ?></td>
                    <td class="text-muted small">/kategori/<?= $c->slug ?></td>
                    <td class="text-end pe-4">
                        <button class="icon-btn me-1" 
                                onclick="editKategori('<?= $c->id ?>', '<?= htmlspecialchars($c->name) ?>')"
                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                title="Edit kategori">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="<?= base_url('admin/kategori/hapus/'.$c->id) ?>" class="icon-btn btn-delete" 
                           data-confirm="Hapus kategori &quot;<?= htmlspecialchars($c->name) ?>&quot;?"
                           data-confirm-title="Hapus Kategori" title="Hapus kategori">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-tags"></i></div>
                        <h6>Belum ada kategori</h6>
                        <p>Tambahkan kategori pertama Anda untuk mengorganisir produk.</p>
                        <button class="btn btn-admin-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus me-2"></i>Tambah Kategori</button>
                    </div>
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/kategori/tambah') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="mb-0">
                        <label class="small text-uppercase ls-1 d-block mb-2">Nama Kategori</label>
                        <input type="text" name="name" class="form-control-admin w-100" placeholder="Contoh: Pintu Modern" required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary px-4"><i class="fas fa-check me-2"></i>Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/kategori/update') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body py-4">
                    <div class="mb-0">
                        <label class="small text-uppercase ls-1 d-block mb-2">Nama Kategori</label>
                        <input type="text" name="name" id="edit_name" class="form-control-admin w-100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-admin-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary px-4"><i class="fas fa-check me-2"></i>Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editKategori(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
}
</script>
