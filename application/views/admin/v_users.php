<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold text-white mb-1">Manajemen Pengguna</h2>
        <p class="text-muted small mb-0">Kelola akses dan data pengguna terdaftar.</p>
    </div>
</div>

<div class="admin-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Pengguna</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Role</th>
                    <th>Bergabung</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): foreach ($users as $u): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 38px; height: 38px; background: linear-gradient(135deg, <?= $u->role === 'admin' ? '#f97316, #fb923c' : '#3b82f6, #60a5fa' ?>); font-size: 0.8rem;">
                                <?= strtoupper(substr($u->username, 0, 1)) ?>
                            </div>
                            <div class="fw-bold text-white small"><?= htmlspecialchars($u->username) ?></div>
                        </div>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($u->email) ?></td>
                    <td class="small text-muted"><?= $u->phone ?: '-' ?></td>
                    <td>
                        <?php if ($u->role === 'admin'): ?>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-3 fw-bold" style="font-size: 0.65rem;">ADMINISTRATOR</span>
                        <?php else: ?>
                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-3 fw-bold" style="font-size: 0.65rem;">CUSTOMER</span>
                        <?php endif ?>
                    </td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($u->created_at)) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada pengguna.</td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
    
    <?php if(!empty($pagination)): ?>
    <div class="mt-4 pb-2">
        <?= $pagination ?>
    </div>
    <?php endif; ?>
</div>
