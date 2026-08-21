<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-white mb-1">Manajemen Pesanan</h2>
        <p class="text-muted small mb-0">Lacak dan verifikasi transaksi pelanggan.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
            <?php $filters=[null=>'Semua','pending'=>'⏳ Pending','paid'=>'✅ Paid','shipped'=>'🚚 Shipped','rejected'=>'❌ Rejected']; ?>
            <?php foreach ($filters as $val=>$label): ?>
                <a href="<?= base_url('admin/pesanan' . ($val ? '?status='.$val : '')) ?>" class="btn btn-sm <?= $filter === $val ? 'btn-admin-primary' : 'btn-admin-outline' ?> rounded-pill px-4">
                    <?= $label ?>
                </a>
            <?php endforeach ?>
        </div>

        <div class="admin-card p-0 overflow-hidden shadow-lg border-0" style="background: rgba(22, 27, 34, 0.4); backdrop-filter: blur(10px);">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr class="ls-1">
                            <th class="ps-4">Order ID</th>
                            <th>Pelanggan</th>
                            <th>Waktu Pesanan</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): foreach ($orders as $o): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-white">#<?= $o->id ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <?= strtoupper(substr($o->username, 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-white small"><?= htmlspecialchars($o->username) ?></div>
                                        <div class="small text-muted" style="font-size: 0.65rem;"><?= $o->email ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-white-50 small"><?= date('d M Y', strtotime($o->created_at)) ?></div>
                                <div class="text-muted" style="font-size: 0.6rem;"><?= date('H:i', strtotime($o->created_at)) ?> WIB</div>
                            </td>
                            <td class="fw-bold text-warning">Rp <?= number_format($o->total_price, 0, ',', '.') ?></td>
                            <td>
                                <?php 
                                    $badge = ['pending'=>'badge-pending','paid'=>'badge-paid','shipped'=>'badge-shipped','rejected'=>'badge-rejected'][$o->status] ?? ''; 
                                ?>
                                <span class="admin-badge <?= $badge ?>" style="font-size: 0.6rem; padding: 6px 12px;"><?= $o->status ?></span>
                            </td>
                            <td>
                                <?php if ($o->payment_proof): ?>
                                    <a href="<?= base_url('uploads/payments/'.$o->payment_proof) ?>" target="_blank" class="btn btn-sm btn-dark border border-secondary border-opacity-25 p-1 px-3 rounded-pill hover-lift" style="font-size: 0.65rem;">
                                        <i class="fas fa-receipt me-1 text-info"></i> Bukti
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic opacity-50"><i class="fas fa-clock me-1"></i>Menunggu</span>
                                <?php endif ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="<?= base_url('admin/pesanan/detail/'.$o->id) ?>" class="btn btn-admin-primary btn-sm rounded-pill px-4 fw-bold hover-lift" style="font-size: 0.7rem;">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-receipt fa-3x mb-3 opacity-10"></i>
                            <p>Tidak ada pesanan ditemukan.</p>
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
    </div>
</div>

