<div class="container">
    <div class="page-head" data-reveal>
        <nav class="crumb2">
            <a href="<?= base_url('pesanan') ?>"><i class="fas fa-arrow-left me-1"></i> Pesanan saya</a>
            <span class="sep">/</span>
            <strong>Pesanan #<?= $order->id ?></strong>
        </nav>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <h1 class="page-title">Pesanan #<?= $order->id ?></h1>
            <?php
                $st_map = [
                    'pending'   => 'st-pending',
                    'paid'      => 'st-paid',
                    'processed' => 'st-processed',
                    'shipped'   => 'st-shipped',
                    'delivered' => 'st-delivered',
                    'rejected'  => 'st-rejected',
                    'cancelled' => 'st-cancelled',
                ];
                $st_cls = $st_map[$order->status] ?? 'st-cancelled';
            ?>
            <span class="st-chip <?= $st_cls ?>"><i class="fas fa-circle" style="font-size:.4rem;"></i> <?= status_label_id($order->status) ?></span>
        </div>
        <p style="color:var(--muted); font-size:.85rem;" class="mb-0 mt-2"><?= tanggal_indo(strtotime($order->created_at)) ?>, <?= date('H:i', strtotime($order->created_at)) ?> WIB</p>
    </div>

    <div class="row g-5 pb-sect">
        <!-- Kiri: Item & Detail -->
        <div class="col-lg-8" data-reveal>
            <section class="card-soft p-4 p-md-5 mb-4">
                <h5 class="mb-4" style="font-family:'Playfair Display',Georgia,serif; font-weight:500; font-size:1.35rem;">Item pesanan</h5>
                <?php foreach ($items as $item): ?>
                    <div class="cart-row flex-column flex-sm-row align-items-sm-center" style="border-top:0;">
                        <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/70x90/f6f2ea/1a1511?text=P' ?>" class="cart-thumb" style="width:70px; height:90px;" alt="<?= htmlspecialchars($item->name) ?>">
                        <div class="flex-grow-1 mt-3 mt-sm-0">
                            <h6 class="cart-name mb-1" style="font-size:.98rem; cursor:default;"><?= htmlspecialchars($item->name) ?></h6>
                            <?php if ((!empty($item->color) && $item->color !== 'Standar') || (!empty($item->size) && $item->size !== 'Standar')): ?>
                                <div class="cart-var">
                                    <?= trim((!empty($item->color) && $item->color !== 'Standar' ? ($item->variant_name1 ?: 'Variasi') . ': ' . htmlspecialchars($item->color) . ' · ' : '') . (!empty($item->size) && $item->size !== 'Standar' ? ($item->variant_name2 ?: 'Variasi') . ': ' . htmlspecialchars($item->size) : ''), ' ·') ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item->sleeve) || !empty($item->material)): ?>
                                <div class="cart-var">
                                    <?= trim((!empty($item->sleeve) ? 'Lengan: ' . htmlspecialchars($item->sleeve) . ' · ' : '') . (!empty($item->material) ? 'Bahan: ' . htmlspecialchars($item->material) : ''), ' ·') ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item->note)): ?>
                                <div class="cart-var fst-italic"><i class="fas fa-pen-nib me-1"></i><?= htmlspecialchars($item->note) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item->custom_text) || !empty($item->custom_image)): ?>
                                <div class="mt-2 p-3 rounded-3" style="background:var(--paper); border:1px dashed var(--line-strong);">
                                    <span class="eyebrow eyebrow-plain" style="font-size:.6rem;"><i class="fas fa-wand-magic-sparkles me-1" style="color:var(--accent);"></i> Kustom</span>
                                    <?php if (!empty($item->custom_text)): ?>
                                        <div class="fst-italic cart-var">&ldquo;<?= htmlspecialchars($item->custom_text) ?>&rdquo;</div>
                                    <?php endif; ?>
                                    <?php if (!empty($item->custom_image)): ?>
                                        <a href="<?= base_url($item->custom_image) ?>" target="_blank">
                                            <img src="<?= base_url($item->custom_image) ?>" class="img-fluid rounded-2 mt-2" style="max-height: 100px;" alt="Referensi custom">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="cart-var tnum">Qty: <?= $item->qty ?> &times; Rp <?= number_format($item->price, 0, ',', '.') ?></div>
                        </div>
                        <div class="fw-semibold tnum text-sm-end ms-auto mt-3 mt-sm-0" style="min-width:110px;">Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="sum-row sum-total pt-4 mt-2" style="border-top:1px solid var(--line);">
                    <span>Total pesanan</span>
                    <span class="tnum">Rp <?= number_format($order->total_price, 0, ',', '.') ?></span>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card-soft p-4 h-100">
                        <span class="eyebrow eyebrow-plain" style="margin-bottom:14px;">Alamat pengiriman</span>
                        <div class="lh-lg" style="color:var(--muted); font-size:.88rem;">
                            <strong style="color:var(--ink);" class="d-block mb-1"><?= htmlspecialchars($order->receiver_name) ?></strong>
                            <?= nl2br(htmlspecialchars($order->address)) ?><br>
                            <?= htmlspecialchars($order->city) ?>, <?= htmlspecialchars($order->province) ?><br>
                            <i class="fas fa-phone me-2 mt-2"></i> <?= $order->phone ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-soft p-4 h-100">
                        <span class="eyebrow eyebrow-plain" style="margin-bottom:14px;">Metode pembayaran</span>
                        <div class="lh-lg" style="color:var(--muted); font-size:.88rem;">
                            <strong style="color:var(--ink);" class="d-block mb-1"><?= !empty($order->snap_token) || !empty($order->midtrans_order_id) ? 'Bayar Online (Midtrans)' : 'Transfer Bank (Manual)' ?></strong>
                            <?php if (empty($order->snap_token) && empty($order->midtrans_order_id)): ?>
                                Transfer Mandiri: 123-456-7890 (JiDoor Store)<br>
                            <?php endif; ?>
                            Status: <strong style="color:var(--ink);"><?= status_label_id($order->status) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan: Aksi -->
        <div class="col-lg-4" data-reveal style="--rd:.12s;">
            <div class="summary-card card-soft">
                <h5 class="mb-4" style="font-family:'Playfair Display',Georgia,serif; font-weight:500; font-size:1.35rem;">Bukti pembayaran</h5>

                <?php if ($order->payment_proof): ?>
                    <p class="fx-hint mb-3" style="font-size:.85rem;">Bukti pembayaran Anda sudah kami terima. Tim kami akan segera memverifikasinya.</p>
                    <div class="rounded-3 overflow-hidden mb-3" style="border:1px solid var(--line);">
                        <img src="<?= base_url('uploads/payments/' . $order->payment_proof) ?>" class="img-fluid w-100" alt="Bukti bayar">
                    </div>
                    <?php if ($order->status == 'pending'): ?>
                        <a href="<?= base_url('checkout/bukti/' . $order->id) ?>" class="btn-line btn-sm2 w-100">Unggah ulang bukti</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-2 mb-3">
                        <div class="empty2 py-3 px-2">
                            <div class="ico"><i class="fas fa-cloud-arrow-up"></i></div>
                            <p class="fx-hint" style="font-size:.85rem;">Silakan unggah bukti pembayaran untuk melanjutkan proses verifikasi.</p>
                        </div>
                        <a href="<?= base_url('checkout/bukti/' . $order->id) ?>" class="btn-ink btn-block2">Unggah bukti sekarang</a>
                    </div>
                <?php endif; ?>

                <!-- Bayar Online via Midtrans Snap (Revisi #6) -->
                <?php if ($order->status === 'pending'): ?>
                    <div class="mt-4 p-4 rounded-3" style="background:var(--paper); border:1px solid var(--line);">
                        <h6 class="fw-semibold mb-2" style="font-size:.88rem;"><i class="fas fa-bolt me-2" style="color:var(--accent);"></i>Bayar online</h6>
                        <p class="fx-hint mb-3">QRIS / Virtual Account / E-wallet / Kartu — via Midtrans.</p>
                        <button type="button" id="pay-button" class="btn-ink btn-block2 btn-sm2">
                            <i class="fas fa-lock"></i> Bayar sekarang
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Kartu Resi (Revisi #5) -->
                <?php if (!empty($order->resi)): ?>
                    <div class="mt-4 p-4 rounded-3" style="background:var(--paper); border:1px solid var(--line);">
                        <div class="fw-semibold mb-2" style="font-size:.82rem;"><i class="fas fa-truck-fast me-2"></i><?= htmlspecialchars($order->courier) ?></div>
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-semibold tnum" id="resiNumber"><?= htmlspecialchars($order->resi) ?></span>
                            <button type="button" class="btn-text2 btn-sm2" onclick="copyResi()">
                                <i class="far fa-copy"></i> Salin
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tombol Pesanan Diterima (Revisi #5) -->
                <?php if ($order->status === 'shipped'): ?>
                    <form action="<?= base_url('pesanan/diterima/' . $order->id) ?>" method="post" class="mt-4">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-ink btn-block2"
                                onclick="return confirm('Konfirmasi pesanan sudah diterima dalam kondisi baik?')">
                            <i class="fas fa-check me-2"></i> Pesanan diterima
                        </button>
                    </form>
                <?php endif; ?>

                <div class="mt-4 pt-4" style="border-top:1px solid var(--line);">
                    <span class="eyebrow eyebrow-plain" style="margin-bottom:10px;">Butuh bantuan?</span><br>
                    <a href="#" class="btn-text2 btn-sm2 ps-0"><i class="fab fa-whatsapp"></i> Chat dengan admin</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Tracking Pesanan (Revisi #5) -->
    <?php if (!empty($tracking)): ?>
    <div class="row pb-sect">
        <div class="col-lg-8 mx-auto">
            <div class="sec-head">
                <div data-reveal>
                    <span class="sec-num">Perjalanan paket</span>
                    <h2>Lacak pesanan</h2>
                </div>
            </div>
            <div class="card-soft p-4 p-md-5 mb-4" data-reveal>
                <div class="tl">
                    <?php foreach ($tracking as $i => $t): ?>
                        <?php
                            $is_last = ($i === count($tracking) - 1);
                            // Status terlewati = bukan baris terakhir; aktif = baris terakhir
                            $cls = $is_last ? 'active' : 'done';
                        ?>
                        <div class="tl-item <?= $cls ?>">
                            <div class="tl-dot"></div>
                            <div>
                                <div class="tl-status"><?= status_label_id($t->status) ?></div>
                                <div class="tl-time"><?= tanggal_indo(strtotime($t->created_at)) ?> WIB</div>
                                <?php if (!empty($t->description)): ?>
                                    <div class="mt-1" style="font-size:.86rem; color:var(--ink-soft);"><?= htmlspecialchars($t->description) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($t->resi)): ?>
                                    <div class="mt-1 tl-time"><strong><?= htmlspecialchars($t->courier) ?></strong> — Resi: <span class="tnum"><?= htmlspecialchars($t->resi) ?></span></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

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
