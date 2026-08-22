<div class="container py-5 mt-5">
    <div class="mb-5">
        <a href="<?= base_url('pesanan') ?>" class="text-decoration-none text-muted small fw-bold ls-1"><i class="fas fa-arrow-left me-2"></i> BACK TO MY ORDERS</a>
        <h1 class="fw-bold display-6 mt-3 ls-1">ORDER #<?= $order->id ?></h1>
        <div class="d-flex align-items-center gap-3 mt-2">
            <span class="text-muted small ls-1 text-uppercase"><?= date('d F Y, H:i', strtotime($order->created_at)) ?></span>
            <span class="badge bg-dark rounded-0 px-3 py-2 fw-bold ls-1 text-uppercase" style="font-size: 0.65rem;"><?= $order->status ?></span>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left: Items & Details -->
        <div class="col-lg-8">
            <div class="mb-5 pb-5 border-bottom">
                <h5 class="fw-bold text-uppercase ls-2 mb-4">Ordered Items</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="py-3 ps-0" style="width: 80px;">
                                        <div class="bg-light p-1" style="width: 70px; height: 90px;">
                                            <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/70x90/f5f5f5/000000?text=P' ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->name) ?>">
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <h6 class="fw-bold mb-1 ls-1"><?= htmlspecialchars($item->name) ?></h6>
                                        <?php if ((!empty($item->color) && $item->color !== 'Standar') || (!empty($item->size) && $item->size !== 'Standar')): ?>
                                            <p class="small mb-1">
                                                <?php if (!empty($item->color) && $item->color !== 'Standar'): ?>
                                                    <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($item->variant_name1 ?: 'Variasi') ?>: <?= htmlspecialchars($item->color) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($item->size) && $item->size !== 'Standar'): ?>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($item->variant_name2 ?: 'Variasi') ?>: <?= htmlspecialchars($item->size) ?></span>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($item->note)): ?>
                                            <p class="small text-muted fst-italic mb-1"><i class="fas fa-pen-nib me-1"></i><?= htmlspecialchars($item->note) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item->custom_text) || !empty($item->custom_image)): ?>
                                            <div class="small mb-1 p-2 rounded-3 bg-light border border-warning border-opacity-50">
                                                <span class="fw-bold text-uppercase ls-1" style="font-size: 0.65rem;"><i class="fas fa-wand-magic-sparkles me-1 text-warning"></i> Custom</span>
                                                <?php if (!empty($item->custom_text)): ?>
                                                    <div class="fst-italic text-muted">"<?= htmlspecialchars($item->custom_text) ?>"</div>
                                                <?php endif; ?>
                                                <?php if (!empty($item->custom_image)): ?>
                                                    <a href="<?= base_url($item->custom_image) ?>" target="_blank">
                                                        <img src="<?= base_url($item->custom_image) ?>" class="img-fluid rounded-2 mt-1" style="max-height: 100px;" alt="Referensi custom">
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <p class="small text-muted mb-0">Qty: <?= $item->qty ?> &times; Rp <?= number_format($item->price, 0, ',', '.') ?></p>
                                    </td>
                                    <td class="py-3 text-end fw-bold ls-1 pe-0">
                                        Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="py-4 ps-0 text-uppercase small fw-bold ls-1">Order Total</td>
                                <td class="py-4 pe-0 text-end fw-800 fs-5">Rp <?= number_format($order->total_price, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <h6 class="fw-bold text-uppercase ls-1 mb-3">Shipping Address</h6>
                    <div class="text-muted small lh-lg">
                        <strong class="text-dark d-block mb-1"><?= htmlspecialchars($order->receiver_name) ?></strong>
                        <?= nl2br(htmlspecialchars($order->address)) ?><br>
                        <?= htmlspecialchars($order->city) ?>, <?= htmlspecialchars($order->province) ?><br>
                        <i class="fas fa-phone-alt me-2 mt-2"></i> <?= $order->phone ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-uppercase ls-1 mb-3">Payment Method</h6>
                    <div class="text-muted small lh-lg">
                        <strong class="text-dark d-block mb-1">Bank Transfer (Manual)</strong>
                        Mandiri Transfer: 123-456-7890 (JiDoor Store)<br>
                        Status: <span class="fw-bold <?= $order->status == 'pending' ? 'text-warning' : 'text-success' ?>"><?= strtoupper($order->status) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Proof of Payment & Instructions -->
        <div class="col-lg-4">
            <div class="bg-light p-4 p-md-5">
                <h5 class="fw-bold text-uppercase ls-2 mb-4">Payment Proof</h5>
                
                <?php if ($order->payment_proof): ?>
                    <div class="mb-4">
                        <p class="small text-muted mb-3">You have uploaded your payment proof. Our team will verify it shortly.</p>
                        <div class="bg-white border p-2 mb-3">
                            <img src="<?= base_url('uploads/payments/' . $order->payment_proof) ?>" class="img-fluid w-100" alt="Proof">
                        </div>
                        <?php if ($order->status == 'pending'): ?>
                            <a href="<?= base_url('checkout/bukti/' . $order->id) ?>" class="btn btn-outline-dark btn-sm w-100 rounded-0 fw-bold ls-1">RE-UPLOAD PROOF</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-cloud-upload-alt display-4 text-muted opacity-25 mb-3"></i>
                        <p class="small text-muted mb-4">Please upload your payment proof to complete the verification process.</p>
                        <a href="<?= base_url('checkout/bukti/' . $order->id) ?>" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1">UPLOAD PROOF NOW</a>
                    </div>
                <?php endif; ?>

                <!-- Bayar Online via Midtrans Snap (Revisi #6) -->
                <?php if ($order->status === 'pending'): ?>
                    <div class="mt-4 p-3 bg-white border border-success border-2 rounded-0">
                        <h6 class="fw-bold text-uppercase ls-1 mb-2"><i class="fas fa-bolt text-success me-2"></i>Bayar Online</h6>
                        <p class="small text-muted mb-3">QRIS / Virtual Account / E-wallet / Kartu — via Midtrans.</p>
                        <button type="button" id="pay-button" class="btn btn-success w-100 py-3 rounded-0 fw-bold ls-1">
                            <i class="fas fa-lock me-2"></i>BAYAR SEKARANG
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Kartu Resi (Revisi #5) -->
                <?php if (!empty($order->resi)): ?>
                    <div class="mt-4 p-3 bg-white border border-dark border-2 rounded-0">
                        <div class="small fw-bold text-uppercase ls-1 mb-1"><i class="fas fa-truck me-2"></i><?= htmlspecialchars($order->courier) ?></div>
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-800 ls-1" id="resiNumber"><?= htmlspecialchars($order->resi) ?></span>
                            <button type="button" class="btn btn-sm btn-outline-dark rounded-0 px-2 py-1" onclick="copyResi()">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tombol Pesanan Diterima (Revisi #5) -->
                <?php if ($order->status === 'shipped'): ?>
                    <form action="<?= base_url('pesanan/diterima/' . $order->id) ?>" method="post" class="mt-4">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success w-100 py-3 rounded-0 fw-bold ls-1"
                                onclick="return confirm('Konfirmasi pesanan sudah diterima dalam kondisi baik?')">
                            <i class="fas fa-check-circle me-2"></i> PESANAN DITERIMA
                        </button>
                    </form>
                <?php endif; ?>

                <div class="mt-4 pt-4 border-top">
                    <h6 class="fw-bold text-uppercase ls-1 mb-3" style="font-size: 0.75rem;">Need Help?</h6>
                    <a href="#" class="text-decoration-none text-dark small fw-bold ls-1"><i class="fab fa-whatsapp me-2"></i> CHAT WITH SUPPORT</a>
                </div>
            </div>
        </div>

        <!-- Timeline Tracking Pesanan (Revisi #5) -->
        <?php if (!empty($tracking)): ?>
        <div class="row mt-5 pt-5 border-top">
            <div class="col-lg-8 mx-auto">
                <h5 class="fw-bold text-uppercase ls-2 mb-4">Lacak Pesanan</h5>
                <div class="tracking-timeline">
                    <?php foreach ($tracking as $i => $t): ?>
                        <?php
                            $is_last = ($i === count($tracking) - 1);
                            // Status terlewati = bukan baris terakhir; aktif = baris terakhir
                            $cls = $is_last ? 'active' : 'done';
                        ?>
                        <div class="track-item <?= $cls ?>">
                            <div class="track-dot">
                                <i class="fa<?= $is_last ? 's' : 's' ?> <?= $is_last ? 'fa-circle' : 'fa-check' ?>"></i>
                            </div>
                            <div class="pb-4 ps-2">
                                <div class="fw-bold text-uppercase ls-1 small"><?= strtoupper(htmlspecialchars($t->status)) ?></div>
                                <div class="small text-muted"><?= date('d M Y, H:i', strtotime($t->created_at)) ?> WIB</div>
                                <?php if (!empty($t->description)): ?>
                                    <div class="small text-dark mt-1"><?= htmlspecialchars($t->description) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($t->resi)): ?>
                                    <div class="small mt-1"><span class="fw-bold"><?= htmlspecialchars($t->courier) ?></span> — Resi: <?= htmlspecialchars($t->resi) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Timeline tracking vertikal (Revisi #5) */
.tracking-timeline { position: relative; }
.track-item { position: relative; display: flex; align-items: flex-start; }
.track-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 34px;
    bottom: 0;
    width: 2px;
    background: rgba(0,0,0,0.08);
}
.track-dot {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    margin-right: 12px;
    z-index: 1;
}
.track-item.done .track-dot {
    background: #000;
    color: #fff;
}
.track-item.active .track-dot {
    background: #fff;
    color: #ee4d2d;
    border: 2px solid #ee4d2d;
    animation: pulse-dot 1.6s infinite;
}
@keyframes pulse-dot {
    0%   { box-shadow: 0 0 0 0 rgba(238,77,45,0.45); }
    70%  { box-shadow: 0 0 0 10px rgba(238,77,45,0); }
    100% { box-shadow: 0 0 0 0 rgba(238,77,45,0); }
}
</style>

<script>
function copyResi() {
    const resi = document.getElementById('resiNumber');
    if (!resi) return;
    navigator.clipboard.writeText(resi.textContent.trim()).then(() => {
        alert('Nomor resi disalin: ' + resi.textContent.trim());
    }).catch(() => {});
}

// ===== Popup Midtrans Snap (Revisi #6) =====
<?php if ($order->status === 'pending'): ?>
document.getElementById('pay-button').addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>MEMPROSES...';

    fetch('<?= site_url("pesanan/bayar/" . $order->id) ?>')
        .then(r => r.json())
        .then(res => {
            if (res.error) {
                alert('Gagal memulai pembayaran: ' + res.error);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock me-2"></i>BAYAR SEKARANG';
                return;
            }
            snap.pay(res.token, {
                onSuccess: function (result) {
                    window.location = '<?= site_url("pesanan/midtrans-finish/" . $order->id) ?>';
                },
                onPending: function (result) {
                    window.location = '<?= site_url("pesanan/detail/" . $order->id) ?>';
                },
                onError: function (result) {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>BAYAR SEKARANG';
                },
                onClose: function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>BAYAR SEKARANG';
                }
            });
        })
        .catch(() => {
            alert('Tidak dapat menghubungi server pembayaran.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-2"></i>BAYAR SEKARANG';
        });
});
<?php endif; ?>
</script>

<?php if ($order->status === 'pending'): ?>
<!-- TODO: hapus ".sandbox" dari URL script jika nanti production -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<?php endif; ?>
