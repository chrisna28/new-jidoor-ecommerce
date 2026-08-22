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
                                    <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/'.$item->image) : 'https://placehold.co/40x40/161b22/8b949e?text=P' ?>" class="rounded-3" style="width: 40px; height: 40px; object-fit: cover;">
                                    <span class="fw-bold text-white small"><?= htmlspecialchars($item->name) ?></span>
                                </div>
                            </td>
                            <td class="small">
                                <?php if (!empty($item->color) && $item->color !== 'Standar'): ?>
                                    <span class="badge bg-dark border border-secondary text-white"><?= htmlspecialchars($item->variant_name1 ?: 'Variasi') ?>: <?= htmlspecialchars($item->color) ?></span>
                                <?php endif ?>
                                <?php if (!empty($item->size) && $item->size !== 'Standar'): ?>
                                    <span class="badge bg-dark border border-secondary text-white mt-1"><?= htmlspecialchars($item->variant_name2 ?: 'Variasi') ?>: <?= htmlspecialchars($item->size) ?></span>
                                <?php endif ?>
                                <?php if (!empty($item->note)): ?>
                                    <div class="text-muted fst-italic mt-1"><i class="fas fa-pen-nib me-1"></i><?= htmlspecialchars($item->note) ?></div>
                                <?php endif ?>
                                <?php if (!empty($item->custom_text) || !empty($item->custom_image)): ?>
                                    <div class="mt-2 p-2 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-30">
                                        <span class="fw-bold text-warning text-uppercase ls-1" style="font-size: 0.65rem;"><i class="fas fa-wand-magic-sparkles me-1"></i> Permintaan Custom</span>
                                        <?php if (!empty($item->custom_text)): ?>
                                            <div class="text-white fst-italic small">"<?= htmlspecialchars($item->custom_text) ?>"</div>
                                        <?php endif; ?>
                                        <?php if (!empty($item->custom_image)): ?>
                                            <a href="<?= base_url($item->custom_image) ?>" target="_blank">
                                                <img src="<?= base_url($item->custom_image) ?>" class="img-fluid rounded-3 mt-1 border border-secondary" style="max-height: 120px;" alt="Referensi custom">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif ?>
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

            <!-- Update Status + Tracking (Revisi #5) -->
            <?php if (in_array($order->status, ['pending', 'paid', 'processed'])): ?>
                <div class="p-4 rounded-4 bg-warning bg-opacity-10 border border-warning border-opacity-20">
                    <h6 class="fw-bold text-warning mb-3"><i class="fas fa-gavel me-2"></i>Update Status Pesanan</h6>
                    <form method="post" action="<?= base_url('admin/pesanan/verifikasi/'.$order->id) ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase ls-1 d-block mb-1">Status Baru</label>
                            <select name="status" id="statusSelect" class="form-select form-control-admin" required>
                                <?php if ($order->status === 'pending'): ?>
                                    <option value="paid">✅ Verifikasi Pembayaran (Paid)</option>
                                    <option value="rejected">❌ Tolak Pembayaran</option>
                                    <option value="cancelled">🚫 Batalkan Pesanan</option>
                                <?php elseif ($order->status === 'paid'): ?>
                                    <option value="processed">🔧 Proses Produksi</option>
                                    <option value="shipped">🚚 Kirim Barang</option>
                                    <option value="cancelled">🚫 Batalkan Pesanan</option>
                                <?php else: ?>
                                    <option value="shipped">🚚 Kirim Barang</option>
                                    <option value="cancelled">🚫 Batalkan Pesanan</option>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase ls-1 d-block mb-1">Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" class="form-control-admin w-100" placeholder="mis. sedang diukir, estimasi 3 hari">
                        </div>
                        <div class="row g-2 mb-3 d-none" id="shippingFields">
                            <div class="col-7">
                                <label class="text-muted small text-uppercase ls-1 d-block mb-1">No. Resi *</label>
                                <input type="text" name="resi" class="form-control-admin w-100" placeholder="mis. JX1234567890">
                            </div>
                            <div class="col-5">
                                <label class="text-muted small text-uppercase ls-1 d-block mb-1">Kurir *</label>
                                <input type="text" name="courier" class="form-control-admin w-100" placeholder="mis. JNE">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 rounded-3"
                                onclick="return confirm('Perbarui status pesanan?')">PERBARUI STATUS</button>
                    </form>
                </div>
            <?php endif ?>

            <?php if ($order->status === 'shipped' && $order->resi): ?>
                <div class="p-4 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-20 mb-4">
                    <h6 class="fw-bold text-info mb-2"><i class="fas fa-truck me-2"></i>Sedang Dikirim</h6>
                    <div class="text-white small"><?= htmlspecialchars($order->courier) ?> — <span class="fw-bold"><?= htmlspecialchars($order->resi) ?></span></div>
                    <p class="text-muted small mb-0 mt-2">Menunggu konfirmasi "Pesanan Diterima" dari pelanggan.</p>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<script>
// Tampilkan field resi & kurir hanya saat status "shipped" (Revisi #5)
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
