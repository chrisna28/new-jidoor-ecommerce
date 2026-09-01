<div class="page-head">
    <a href="<?= base_url('admin/pesanan') ?>" class="btn btn-sm btn-admin-outline mb-3">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
    </a>
    <h2 class="page-title">Detail Pesanan <span style="color: var(--accent);">#<?= $order->id ?></span></h2>
</div>

<div class="row g-3 g-md-4">
    <!-- Items & Customer Info -->
    <div class="col-lg-8">
        <div class="admin-card p-0">
            <div class="px-4 pt-4 pb-3 fw-bold">Item Pesanan</div>
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/'.$item->image) : 'https://placehold.co/40x40/f1f5f9/94a3b8?text=P' ?>" class="rounded border" style="width: 42px; height: 42px; object-fit: cover;">
                                    <span class="fw-bold small"><?= htmlspecialchars($item->name) ?></span>
                                </div>
                            </td>
                            <td class="small">
                                <?php if (!empty($item->color) && $item->color !== 'Standar'): ?>
                                    <span class="badge-neutral"><?= htmlspecialchars($item->variant_name1 ?: 'Variasi') ?>: <?= htmlspecialchars($item->color) ?></span>
                                <?php endif ?>
                                <?php if (!empty($item->size) && $item->size !== 'Standar'): ?>
                                    <span class="badge-neutral mt-1"><?= htmlspecialchars($item->variant_name2 ?: 'Variasi') ?>: <?= htmlspecialchars($item->size) ?></span>
                                <?php endif ?>
                                <?php if (!empty($item->sleeve)): ?>
                                    <span class="badge-neutral mt-1"><i class="fas fa-shirt me-1"></i>Lengan: <?= htmlspecialchars($item->sleeve) ?></span>
                                <?php endif ?>
                                <?php if (!empty($item->material)): ?>
                                    <span class="badge-neutral mt-1"><i class="fas fa-layer-group me-1"></i>Bahan: <?= htmlspecialchars($item->material) ?></span>
                                <?php endif ?>
                                <?php if (!empty($item->note)): ?>
                                    <div class="text-muted fst-italic mt-1"><i class="fas fa-pen-nib me-1"></i><?= htmlspecialchars($item->note) ?></div>
                                <?php endif ?>
                                <?php if (!empty($item->custom_text) || !empty($item->custom_image)): ?>
                                    <div class="mt-2 info-panel p-warning" style="padding: 10px 14px;">
                                        <span class="fw-bold text-uppercase ls-1 d-block mb-1" style="font-size: 0.65rem; color: var(--warning);"><i class="fas fa-wand-magic-sparkles me-1"></i> Permintaan Custom</span>
                                        <?php if (!empty($item->custom_text)): ?>
                                            <div class="fst-italic small">"<?= htmlspecialchars($item->custom_text) ?>"</div>
                                        <?php endif; ?>
                                        <?php if (!empty($item->custom_image)): ?>
                                            <a href="<?= base_url($item->custom_image) ?>" target="_blank">
                                                <img src="<?= base_url($item->custom_image) ?>" class="img-fluid rounded mt-1 border" style="max-height: 120px;" alt="Referensi custom">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif ?>
                            </td>
                            <td class="text-center num"><?= $item->qty ?></td>
                            <td class="text-end num">Rp <?= number_format($item->price, 0, ',', '.') ?></td>
                            <td class="text-end fw-bold num">Rp <?= number_format($item->price*$item->qty, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Pembayaran</td>
                            <td colspan="2" class="text-end fs-5" style="font-weight: 800; color: var(--text-1);">Rp <?= number_format($order->total_price, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="admin-card mt-4">
            <div class="admin-card-title">Informasi Pengiriman</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase ls-1 d-block mb-1">Nama Penerima</label>
                        <div class="fw-bold"><?= htmlspecialchars($order->receiver_name ?? $order->username ?? '-') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase ls-1 d-block mb-1">Telepon Penerima</label>
                        <div class="small"><?= $order->phone ?? '-' ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase ls-1 d-block mb-1">Alamat Tujuan</label>
                        <div class="small">
                            <?= nl2br(htmlspecialchars($order->address)) ?><br>
                            <?= htmlspecialchars($order->city ?? '-') ?>, <?= htmlspecialchars($order->province ?? '-') ?>
                        </div>
                    </div>
                    <?php if ($order->note): ?>
                    <div class="info-panel p-warning small fst-italic mt-2">
                        "<?= htmlspecialchars($order->note) ?>"
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Actions -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-title">Status Pesanan</div>
            
            <?php
                $panelMap = [
                    'pending'   => 'p-warning',
                    'paid'      => 'p-success',
                    'processed' => 'p-info',
                    'shipped'   => 'p-info',
                    'delivered' => 'p-success',
                    'rejected'  => 'p-danger',
                    'cancelled' => 'p-danger',
                ];
                $statusColor = ['pending'=>'var(--warning)','paid'=>'var(--success)','processed'=>'var(--info)','shipped'=>'var(--info)','delivered'=>'var(--success)','rejected'=>'var(--danger)','cancelled'=>'var(--danger)'][$order->status] ?? 'var(--text-1)';
            ?>
            <div class="info-panel <?= $panelMap[$order->status] ?? '' ?> mb-4 text-center py-4">
                <h4 class="fw-bold mb-1 text-uppercase ls-2" style="color: <?= $statusColor ?>;"><?= status_label_id($order->status) ?></h4>
                <small class="opacity-75"><?= date('d M Y, H:i', strtotime($order->created_at)) ?></small>
            </div>

            <!-- Timeline status -->
            <?php
                $flow = ['pending', 'paid', 'processed', 'shipped'];
                $currentIndex = array_search($order->status, $flow);
                $allDone = ($order->status === 'delivered');
            ?>
            <?php if (!in_array($order->status, ['rejected', 'cancelled'])): ?>
            <ul class="status-steps mb-4">
                <?php foreach ($flow as $i => $step):
                    $labels = [
                        'pending'   => 'Menunggu Pembayaran',
                        'paid'      => 'Pembayaran Diverifikasi',
                        'processed' => 'Proses Produksi',
                        'shipped'   => 'Dalam Pengiriman',
                    ];
                    $cls = '';
                    if ($allDone || ($currentIndex !== false && $i < $currentIndex)) $cls = 'done';
                    elseif ($i === $currentIndex && !$allDone) $cls = 'current';
                ?>
                <li class="<?= $cls ?>"><i class="fas <?= ['pending'=>'fa-hourglass-half','paid'=>'fa-circle-check','processed'=>'fa-gears','shipped'=>'fa-truck'][$step] ?> me-2"></i><?= $labels[$step] ?></li>
                <?php endforeach; ?>
                <li class="<?= $allDone ? 'done' : '' ?>"><i class="fas fa-box-open me-2"></i>Pesanan Diterima</li>
            </ul>
            <?php else: ?>
            <div class="info-panel p-danger mb-4 text-center small fw-semibold">
                <i class="fas fa-ban me-2"></i>Pesanan ini telah dibatalkan / ditolak.
            </div>
            <?php endif; ?>

            <?php if ($order->payment_proof): ?>
                <div class="mb-4">
                    <label class="text-muted small text-uppercase ls-1 d-block mb-2 text-center">Bukti Pembayaran</label>
                    <a href="<?= base_url('uploads/payments/'.$order->payment_proof) ?>" target="_blank">
                        <img src="<?= base_url('uploads/payments/'.$order->payment_proof) ?>" class="img-fluid rounded-3 border shadow-sm w-100">
                    </a>
                </div>
            <?php else: ?>
                <div class="info-panel text-center mb-4 py-4">
                    <i class="fas fa-clock fs-3 text-muted mb-2 d-block"></i>
                    <p class="text-muted small mb-0">Belum ada bukti pembayaran.</p>
                </div>
            <?php endif ?>

            <!-- Update Status + Tracking -->
            <?php if (in_array($order->status, ['pending', 'paid', 'processed'])): ?>
                <div class="info-panel p-warning">
                    <h6 class="fw-bold mb-3" style="color: var(--warning);"><i class="fas fa-pen-to-square me-2"></i>Update Status Pesanan</h6>
                    <form method="post" action="<?= base_url('admin/pesanan/verifikasi/'.$order->id) ?>" data-confirm="Perbarui status pesanan #<?= $order->id ?>?" data-confirm-title="Update Status">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="small d-block mb-1" style="color: var(--text-2); font-weight: 600;">Status Baru</label>
                            <select name="status" id="statusSelect" class="form-select form-control-admin" required>
                                <?php if ($order->status === 'pending'): ?>
                                    <option value="paid">Verifikasi Pembayaran (Terbayar)</option>
                                    <option value="rejected">Tolak Pembayaran</option>
                                    <option value="cancelled">Batalkan Pesanan</option>
                                <?php elseif ($order->status === 'paid'): ?>
                                    <option value="processed">Proses Produksi</option>
                                    <option value="shipped">Kirim Barang</option>
                                    <option value="cancelled">Batalkan Pesanan</option>
                                <?php else: ?>
                                    <option value="shipped">Kirim Barang</option>
                                    <option value="cancelled">Batalkan Pesanan</option>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small d-block mb-1" style="color: var(--text-2); font-weight: 600;">Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" class="form-control-admin w-100" placeholder="mis. sedang diukir, estimasi 3 hari">
                        </div>
                        <div class="row g-2 mb-3 d-none" id="shippingFields">
                            <div class="col-7">
                                <label class="small d-block mb-1" style="color: var(--text-2); font-weight: 600;">No. Resi *</label>
                                <input type="text" name="resi" class="form-control-admin w-100" placeholder="mis. JX1234567890">
                            </div>
                            <div class="col-5">
                                <label class="small d-block mb-1" style="color: var(--text-2); font-weight: 600;">Kurir *</label>
                                <input type="text" name="courier" class="form-control-admin w-100" placeholder="mis. JNE">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-admin-primary w-100">PERBARUI STATUS</button>
                    </form>
                </div>
            <?php endif ?>

            <?php if ($order->status === 'shipped' && $order->resi): ?>
                <div class="info-panel p-info mt-4">
                    <h6 class="fw-bold mb-2" style="color: var(--info);"><i class="fas fa-truck me-2"></i>Sedang Dikirim</h6>
                    <div class="small"><?= htmlspecialchars($order->courier) ?> — <span class="fw-bold"><?= htmlspecialchars($order->resi) ?></span></div>
                    <p class="text-muted small mb-0 mt-2">Menunggu konfirmasi "Pesanan Diterima" dari pelanggan.</p>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<script>
// Tampilkan field resi & kurir hanya saat status "shipped"
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('statusSelect');
    const shippingFields = document.getElementById('shippingFields');
    if (!statusSelect || !shippingFields) return;

    function toggleShipping() {
        shippingFields.classList.toggle('d-none', statusSelect.value !== 'shipped');
    }
    statusSelect.addEventListener('change', toggleShipping);
    toggleShipping();
});
</script>
