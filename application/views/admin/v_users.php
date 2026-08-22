<div class="page-head">
    <h2 class="page-title">Manajemen Pengguna</h2>
    <p class="page-sub">Kelola akses dan data pengguna terdaftar.</p>
</div>

<div class="admin-card p-0">
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
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-circle <?= $u->role === 'admin' ? '' : 'avatar-customer' ?> avatar-sm"><?= strtoupper(substr($u->username, 0, 1)) ?></span>
                            <span class="fw-bold small"><?= htmlspecialchars($u->username) ?></span>
                        </div>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($u->email) ?></td>
                    <td class="small text-muted num"><?= $u->phone ?: '-' ?></td>
                    <td>
                        <?php if ($u->role === 'admin'): ?>
                            <span class="badge-role-admin">ADMIN</span>
                        <?php else: ?>
                            <span class="badge-role-customer">CUSTOMER</span>
                        <?php endif ?>
                    </td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($u->created_at)) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-users"></i></div>
                        <h6>Belum ada pengguna</h6>
                        <p>Pengguna terdaftar akan tampil di sini.</p>
                    </div>
                </td></tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
    
    <?php if(!empty($pagination)): ?>
    <div class="mt-4 pb-4 px-4">
        <?= $pagination ?>
    </div>
    <?php endif; ?>
</div>
