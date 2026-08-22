<div class="mb-5">
    <h2 class="fw-bold text-white">Chat <span class="text-warning">Pelanggan</span></h2>
    <p class="text-muted">Balas pertanyaan pelanggan secara real-time.</p>
</div>

<div class="admin-card">
    <div class="admin-card-title">Inbox <span class="text-muted small fw-normal">(terurut pesan terbaru)</span></div>
    <?php if (empty($inbox)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
            <p class="text-muted mb-0">Belum ada percakapan masuk.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-admin align-middle">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Pesan Terakhir</th>
                        <th class="text-center">Belum Dibaca</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inbox as $c): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-white"><?= htmlspecialchars($c->username ?: 'User #' . $c->user_id) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($c->email ?: '-') ?></div>
                        </td>
                        <td class="text-muted small">
                            <?php if ($c->last_text !== null): ?>
                                <div class="d-flex align-items-center gap-1">
                                    <?php if ($c->last_product_id): ?>
                                        <i class="fas fa-box-open text-warning" title="Pesan terakhir menyertakan produk"></i>
                                    <?php endif ?>
                                    <span class="d-inline-block text-truncate" style="max-width:260px;"><?= htmlspecialchars($c->last_text) ?></span>
                                </div>
                            <?php else: ?>
                                —
                            <?php endif ?>
                            <div><?= date('d M Y, H:i', strtotime($c->last_message_at)) ?></div>
                        </td>
                        <td class="text-center">
                            <?php if ($c->unread_admin > 0): ?>
                                <span class="badge bg-warning text-dark rounded-pill px-3"><?= $c->unread_admin ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= base_url('admin/chat/' . $c->id) ?>" class="btn btn-sm btn-admin-outline px-3">
                                Buka Chat
                            </a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
