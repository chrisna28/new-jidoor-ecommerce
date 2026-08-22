<?php if ($s = $this->session->flashdata('success')): ?><div class="js-flash d-none" data-type="success" data-msg="<?= htmlspecialchars($s) ?>"></div><?php endif; ?>
<?php if ($e = $this->session->flashdata('error')): ?><div class="js-flash d-none" data-type="error" data-msg="<?= htmlspecialchars($e) ?>"></div><?php endif; ?>

<div class="page-head d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="page-title">Manajemen Rating & Review</h2>
        <p class="page-sub">Moderasi ulasan yang ditampilkan pada halaman produk.</p>
    </div>
    <span class="badge-neutral" style="font-size: 0.8rem; padding: 8px 16px;">
        Total: <?= $this->M_rating->count_all($selected_rating) ?> Reviews
    </span>
</div>

<!-- Filter -->
<div class="admin-card">
    <form action="<?= base_url('admin/ratings') ?>" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small text-uppercase mb-2 ls-1">Filter Bintang</label>
            <select name="rating" class="form-select form-control-admin w-100">
                <option value="">Semua Bintang</option>
                <?php for($i=5; $i>=1; $i--): ?>
                    <option value="<?= $i ?>" <?= $selected_rating == $i ? 'selected' : '' ?>>
                        <?= $i ?> Bintang <?= str_repeat('★', $i) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-admin-primary"><i class="fas fa-filter me-2"></i>Filter Data</button>
        </div>
        <?php if($selected_rating): ?>
        <div class="col-md-auto">
            <a href="<?= base_url('admin/ratings') ?>" class="btn btn-admin-outline"><i class="fas fa-undo me-2"></i>Reset</a>
        </div>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table table-admin align-middle">
            <thead>
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Customer</th>
                    <th>Detail Produk</th>
                    <th class="text-center">Rating</th>
                    <th>Komentar Review</th>
                    <th>Waktu Posting</th>
                    <th class="text-end pe-4">Kontrol</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($ratings)): foreach($ratings as $r): ?>
                <tr>
                    <td class="ps-4 text-muted">#<?= $r->id ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-circle avatar-customer avatar-sm"><?= strtoupper(substr($r->username, 0, 1)) ?></span>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($r->username) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">UID: #<?= $r->user_id ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold mb-1" style="font-size: 0.85rem;"><?= htmlspecialchars($r->product_name) ?></div>
                        <span class="badge-neutral" style="font-size: 0.65rem;">Product ID: #<?= $r->product_id ?></span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex flex-column align-items-center gap-1">
                            <div class="d-flex gap-1" style="font-size: 0.75rem; color: var(--warning);">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa<?= $i <= $r->rating ? 's' : 'r' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="badge-neutral" style="font-size: 0.62rem; padding: 2px 9px;"><?= $r->rating ?>/5.0</span>
                        </div>
                    </td>
                    <td>
                        <div class="info-panel p-2" style="max-width: 280px;">
                            <p class="small text-muted mb-0 lh-base" style="white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= $r->review ? htmlspecialchars($r->review) : '<span class="fst-italic opacity-50">Tidak ada komentar tambahan...</span>' ?>
                            </p>
                        </div>
                    </td>
                    <td>
                        <div class="small fw-medium"><?= date('d M Y', strtotime($r->created_at)) ?></div>
                        <div class="text-muted" style="font-size: 0.72rem;"><?= date('H:i', strtotime($r->created_at)) ?> WIB</div>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?= base_url('admin/ratings/hapus/' . $r->id) ?>" 
                           class="icon-btn btn-delete" 
                           data-confirm="Hapus rating #<?= $r->id ?> secara permanen?"
                           data-confirm-title="Hapus Review"
                           data-bs-toggle="tooltip" title="Hapus Review">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                        <h6>Belum ada data rating</h6>
                        <p>Rating dengan filter ini belum tersedia.</p>
                    </div>
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(isset($pagination)): ?>
    <div class="d-flex justify-content-center mt-4 pb-4">
        <?= $pagination ?>
    </div>
    <?php endif; ?>
</div>
