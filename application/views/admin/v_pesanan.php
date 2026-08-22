<div class="page-head">
    <h2 class="page-title">Manajemen Pesanan</h2>
    <p class="page-sub">Lacak dan verifikasi transaksi pelanggan.</p>
</div>

<div class="filter-pills mb-4">
    <?php $filters=[null=>'Semua','pending'=>'Pending','paid'=>'Paid','processed'=>'Processed','shipped'=>'Shipped','rejected'=>'Rejected','cancelled'=>'Cancelled']; ?>
    <?php foreach ($filters as $val=>$label): ?>
        <a href="<?= base_url('admin/pesanan' . ($val ? '?status='.$val : '')) ?>" class="filter-pill <?= $filter === $val ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach ?>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table table-admin mb-0">
            <thead>
                <tr>
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
                    <td class="ps-4 fw-bold">#<?= $o->id ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar-circle avatar-customer avatar-sm"><?= strtoupper(substr($o->username, 0, 1)) ?></span>
                            <div>
                                <div class="fw-bold small"><?= htmlspecialchars($o->username) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;"><?= $o->email ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small" style="color: var(--text-2);"><?= date('d M Y', strtotime($o->created_at)) ?></div>
                        <div class="text-muted" style="font-size: 0.72rem;"><?= date('H:i', strtotime($o->created_at)) ?> WIB</div>
                    </td>
                    <td class="fw-bold num">Rp <?= number_format($o->total_price, 0, ',', '.') ?></td>
                    <td>
                        <?php 
                            $badge = ['pending'=>'badge-pending','paid'=>'badge-paid','processed'=>'badge-processed','shipped'=>'badge-shipped','rejected'=>'badge-rejected','cancelled'=>'badge-cancelled'][$o->status] ?? 'badge-neutral'; 
                        ?>
                        <span class="admin-badge <?= $badge ?>"><?= $o->status ?></span>
                    </td>
                    <td>
                        <?php if ($o->payment_proof): ?>
                            <a href="<?= base_url('uploads/payments/'.$o->payment_proof) ?>" target="_blank" class="icon-btn" title="Lihat bukti transfer">
                                <i class="fas fa-receipt"></i>
                            </a>
                        <?php else: ?>
                            <span class="text-muted small"><i class="fas fa-clock me-1"></i>Menunggu</span>
                        <?php endif ?>
                    </td>
                    <td class="text-end pe-4">
                        <a href="<?= base_url('admin/pesanan/detail/'.$o->id) ?>" class="btn btn-sm btn-admin-outline">Kelola</a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-receipt"></i></div>
                        <h6>Tidak ada pesanan ditemukan</h6>
                        <p>Pesanan dengan filter ini belum tersedia.</p>
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
