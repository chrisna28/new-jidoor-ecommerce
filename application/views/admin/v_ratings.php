<div class="admin-card border-0 shadow-lg">
    <div class="admin-card-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-admin-accent-soft p-2 rounded-3">
                <i class="fas fa-star text-admin-accent"></i>
            </div>
            <span class="fs-5 fw-bold">Manajemen Rating & Review</span>
        </div>
        <span class="badge bg-admin-accent-soft text-admin-accent px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
            Total: <?= $this->M_rating->count_all($selected_rating) ?> Reviews
        </span>
    </div>

    <!-- Enhanced Filter Section -->
    <div class="p-4 mb-4 rounded-4" style="background: rgba(255,255,255,0.02); border: 1px solid var(--admin-border);">
        <form action="<?= base_url('admin/ratings') ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted text-uppercase fw-bold mb-2" style="letter-spacing: 1px;">Filter Bintang</label>
                <div class="input-group">
                    <span class="input-group-text bg-admin-card border-secondary border-opacity-25 text-warning px-3">
                        <i class="fas fa-star"></i>
                    </span>
                    <select name="rating" class="form-select bg-admin-card text-white border-secondary border-opacity-25 py-2 px-3 shadow-none">
                        <option value="">Semua Bintang</option>
                        <?php for($i=5; $i>=1; $i--): ?>
                            <option value="<?= $i ?>" <?= $selected_rating == $i ? 'selected' : '' ?>>
                                <?= $i ?> Bintang <?= str_repeat('★', $i) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-admin-primary px-4 py-2 d-flex align-items-center gap-2" style="height: 45px;">
                    <i class="fas fa-filter shadow-sm"></i> Filter Data
                </button>
            </div>
            <?php if($selected_rating): ?>
            <div class="col-md-auto">
                <a href="<?= base_url('admin/ratings') ?>" class="btn btn-outline-light border-secondary border-opacity-25 px-4 py-2 d-flex align-items-center gap-2" style="height: 45px; font-weight: 600;">
                    <i class="fas fa-undo opacity-50"></i> Reset
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-responsive border-0">
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
                <tr class="admin-row-hover">
                    <td class="ps-4 fw-bold opacity-50">#<?= $r->id ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle bg-admin-accent-soft text-admin-accent fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; font-size: 0.8rem;">
                                <?= strtoupper(substr($r->username, 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-white"><?= htmlspecialchars($r->username) ?></div>
                                <div class="small text-muted opacity-75" style="font-size: 0.7rem;">UID: #<?= $r->user_id ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-white fw-medium mb-1" style="font-size: 0.85rem;"><?= htmlspecialchars($r->product_name) ?></div>
                        <div class="badge bg-dark text-muted border border-secondary border-opacity-10 fw-normal" style="font-size: 0.65rem;">Product ID: #<?= $r->product_id ?></div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex flex-column align-items-center gap-1">
                            <div class="text-warning d-flex gap-1" style="font-size: 0.75rem;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa<?= $i <= $r->rating ? 's' : 'r' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="badge bg-admin-accent-soft text-admin-accent px-2 py-1 rounded small" style="font-size: 0.6rem;"><?= $r->rating ?>/5.0</span>
                        </div>
                    </td>
                    <td>
                        <div class="p-2 rounded-3 bg-dark bg-opacity-25" style="max-width: 280px;">
                            <p class="small text-muted mb-0 lh-base" style="white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= $r->review ? htmlspecialchars($r->review) : '<span class="fst-italic opacity-25">Tidak ada komentar tambahan...</span>' ?>
                            </p>
                        </div>
                    </td>
                    <td>
                        <div class="small text-white opacity-75 fw-medium"><?= date('d M Y', strtotime($r->created_at)) ?></div>
                        <div class="small text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($r->created_at)) ?> WIB</div>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?= base_url('admin/ratings/hapus/' . $r->id) ?>" 
                           class="btn-delete-admin" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus rating ini?')"
                           data-bs-toggle="tooltip" title="Hapus Review">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="opacity-25 mb-3"><i class="fas fa-folder-open fa-3x"></i></div>
                        <div class="text-muted fw-bold">Belum ada data rating ditemukan.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(isset($pagination)): ?>
    <div class="d-flex justify-content-center mt-5">
        <?= $pagination ?>
    </div>
    <?php endif; ?>
</div>

<style>
.admin-row-hover:hover {
    background-color: rgba(255,255,255,0.02) !important;
}
.btn-delete-admin {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border-radius: 10px;
    border: 1px solid rgba(239, 68, 68, 0.2);
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-delete-admin:hover {
    background: #ef4444;
    color: #fff;
    transform: scale(1.1);
}
.text-admin-accent {
    color: var(--admin-accent) !important;
}
.bg-admin-accent-soft {
    background-color: var(--admin-accent-soft) !important;
}
</style>

