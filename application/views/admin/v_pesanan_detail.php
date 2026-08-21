<div class="mb-5">
    <a href="<?= base_url('admin/pesanan') ?>" class="btn btn-admin-outline px-4 mb-4">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
    </a>
    <h2 class="fw-bold text-white">Detail Pesanan <span class="text-warning">#<?= $order->id ?></span></h2>
</div>

<div class="row g-4">
    <!-- Items & Customer Info -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-title">Item Pesanan</div>
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
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
                                    <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/'.$item->image) : 'https://placehold.co/40x40/161b22/8b949e?text=P' ?>" class="rounded-3" style="width: 40px; height: 40px; object-fit: cover;">
                                    <span class="fw-bold text-white small"><?= htmlspecialchars($item->name) ?></span>
                                </div>
                            </td>
                            <td class="text-center"><?= $item->qty ?></td>
                            <td class="text-end">Rp <?= number_format($item->price, 0, ',', '.') ?></td>
                            <td class="text-end fw-bold text-white">Rp <?= number_format($item->price*$item->qty, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold pt-4">Total Pembayaran</td>
                            <td class="text-end fw-800 text-warning fs-5 pt-4">Rp <?= number_format($order->total_price, 0, ',', '.') ?></td>
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
                        <div class="fw-bold text-white"><?= htmlspecialchars($order->receiver_name ?? $order->username ?? '-') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase ls-1 d-block mb-1">Telepon Penerima</label>
                        <div class="text-white small"><?= $order->phone ?? '-' ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase ls-1 d-block mb-1">Alamat Tujuan</label>
                        <div class="text-white small">
                            <?= nl2br(htmlspecialchars($order->address)) ?><br>
                            <?= htmlspecialchars($order->city ?? '-') ?>, <?= htmlspecialchars($order->province ?? '-') ?>
                        </div>
                    </div>
                    <?php if ($order->note): ?>
                    <div class="p-3 rounded-3 bg-dark bg-opacity-50 border-start border-warning border-3 small italic mt-2">
                        "<?= htmlspecialchars($order->note) ?>"
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Actions -->
    <div class="col-lg-4">
        <div class="admin-card mb-4">
            <div class="admin-card-title">Status Pesanan</div>
            <div class="p-3 rounded-4 mb-4 text-center <?= ['pending'=>'bg-warning bg-opacity-10 text-warning','paid'=>'bg-success bg-opacity-10 text-success','shipped'=>'bg-info bg-opacity-10 text-info','rejected'=>'bg-danger bg-opacity-10 text-danger'][$order->status] ?? 'bg-secondary bg-opacity-10 text-white' ?>">
                <h4 class="fw-bold mb-0 text-uppercase ls-2"><?= ucfirst($order->status) ?></h4>
                <small class="opacity-75"><?= date('d M Y, H:i', strtotime($order->created_at)) ?></small>
            </div>

            <?php if ($order->payment_proof): ?>
                <div class="mb-4">
                    <label class="text-muted small text-uppercase ls-1 d-block mb-2 text-center">Bukti Pembayaran</label>
                    <a href="<?= base_url('uploads/payments/'.$order->payment_proof) ?>" target="_blank">
                        <img src="<?= base_url('uploads/payments/'.$order->payment_proof) ?>" class="img-fluid rounded-4 border border-secondary border-opacity-30 shadow-sm">
                    </a>
                </div>
            <?php else: ?>
                <div class="p-4 rounded-4 bg-dark bg-opacity-50 text-center mb-4">
                    <i class="fas fa-clock fs-1 text-muted mb-2"></i>
                    <p class="text-muted small mb-0">Belum ada bukti pembayaran.</p>
                </div>
            <?php endif ?>

            <!-- Verification Actions -->
            <?php if ($order->status === 'pending' && $order->payment_proof): ?>
                <div class="p-4 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-20">
                    <h6 class="fw-bold text-warning mb-3"><i class="fas fa-gavel me-2"></i>Verifikasi Sekarang</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <form method="post" action="<?= base_url('admin/pesanan/verifikasi/'.$order->id) ?>">
                                <?= csrf_field() ?><input type="hidden" name="status" value="paid">
                                <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3" onclick="return confirm('Konfirmasi pembayaran?')">TERIMA</button>
                            </form>
                        </div>
                        <div class="col-6">
                            <form method="post" action="<?= base_url('admin/pesanan/verifikasi/'.$order->id) ?>">
                                <?= csrf_field() ?><input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-outline-danger w-100 fw-bold py-2 rounded-3" onclick="return confirm('Tolak pesanan?')">TOLAK</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php elseif ($order->status === 'paid'): ?>
                <form method="post" action="<?= base_url('admin/pesanan/verifikasi/'.$order->id) ?>">
                    <?= csrf_field() ?><input type="hidden" name="status" value="shipped">
                    <button type="submit" class="btn btn-info w-100 fw-bold py-3 rounded-pill text-white shadow-lg" onclick="return confirm('Tandai sudah dikirim?')">
                        <i class="fas fa-truck me-2"></i> KIRIM SEKARANG
                    </button>
                </form>
            <?php endif ?>
        </div>
    </div>
</div>
