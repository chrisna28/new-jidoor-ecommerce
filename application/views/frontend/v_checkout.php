<div class="container">
    <div class="page-head" data-reveal>
        <span class="eyebrow">Checkout</span>
        <h1 class="page-title mt-3">Selesaikan pesanan</h1>
    </div>

    <div class="steps mb-5" data-reveal style="--rd:.08s;">
        <span class="step active"><span class="step-dot">1</span> <span class="step-label">Pengiriman</span></span>
        <span class="step-line"></span>
        <span class="step active"><span class="step-dot">2</span> <span class="step-label">Pembayaran</span></span>
        <span class="step-line"></span>
        <span class="step"><span class="step-dot">3</span> <span class="step-label">Selesai</span></span>
    </div>

    <form action="<?= base_url('checkout/proses') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="row g-5 pb-sect">
            <!-- Kolom 1 -->
            <div class="col-lg-7" data-reveal>
                <section class="mb-5">
                    <span class="eyebrow">Langkah 1 — Informasi pengiriman</span>
                    <div class="card-soft p-4 p-md-5 mt-3">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="fx-field mb-0">
                                    <label>Nama lengkap</label>
                                    <input type="text" name="receiver_name" class="fx-input" placeholder="Nama lengkap Anda" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fx-field mb-0">
                                    <label>Nomor telepon</label>
                                    <input type="text" name="phone" class="fx-input" placeholder="08xx xxxx xxxx" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="fx-field mb-0">
                                    <label>Alamat lengkap</label>
                                    <textarea name="address" class="fx-input" rows="3" placeholder="Nama jalan, nomor rumah, dll." required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fx-field mb-0">
                                    <label>Provinsi</label>
                                    <input type="text" name="province" class="fx-input" placeholder="Provinsi" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fx-field mb-0">
                                    <label>Kota / Kabupaten</label>
                                    <input type="text" name="city" class="fx-input" placeholder="Kota" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-5">
                    <span class="eyebrow">Langkah 2 — Metode pembayaran</span>
                    <div class="d-grid gap-3 mt-3">
                        <label class="pay-opt">
                            <div class="d-flex align-items-start gap-3">
                                <input type="radio" name="payment_method" id="pay_online" value="midtrans" checked style="margin-top:4px;">
                                <div>
                                    <div class="pay-title"><i class="fas fa-bolt me-2" style="color:var(--accent);"></i>Bayar online — QRIS / Virtual Account / E-Wallet / Kartu</div>
                                    <div class="pay-desc">Via Midtrans. Status pesanan diperbarui otomatis setelah pembayaran berhasil.</div>
                                    <div class="d-flex gap-3 mt-3" style="color:var(--muted);">
                                        <i class="fab fa-cc-visa fs-4"></i>
                                        <i class="fab fa-cc-mastercard fs-4"></i>
                                        <i class="fas fa-qrcode fs-4"></i>
                                        <i class="fas fa-wallet fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <label class="pay-opt">
                            <div class="d-flex align-items-start gap-3">
                                <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" style="margin-top:4px;">
                                <div>
                                    <div class="pay-title"><i class="fas fa-building-columns me-2" style="color:var(--accent);"></i>Transfer bank (verifikasi manual)</div>
                                    <div class="pay-desc">Transfer ke rekening Bank Mandiri <strong>123-456-7890 (JiDoor Store)</strong>. Bukti pembayaran diunggah setelah checkout.</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </section>

                <section class="mb-5">
                    <span class="eyebrow">Catatan tambahan</span>
                    <div class="card-soft p-4 p-md-5 mt-3">
                        <div class="fx-field mb-0">
                            <label>Catatan untuk penjual (opsional)</label>
                            <textarea name="note" class="fx-input" rows="2" placeholder="Ada instruksi khusus?"></textarea>
                        </div>
                    </div>
                </section>

                <?php $has_custom_items = false; ?>
                <?php foreach ($cart_items as $ci): if (!empty($ci->is_custom)) { $has_custom_items = true; break; } endforeach; ?>
                <?php if ($has_custom_items): ?>
                <section class="mb-5">
                    <span class="eyebrow">Gambar referensi custom</span>
                    <p style="color:var(--muted); font-size:.8rem;" class="mt-1 mb-3">jpg/png, maks 2 MB — opsional.</p>
                    <?php foreach ($cart_items as $ci): if (empty($ci->is_custom)) continue; ?>
                        <div class="card-soft p-4 mb-3">
                            <label class="fw-semibold d-block mb-2" style="font-size:.88rem;"><?= htmlspecialchars($ci->name) ?></label>
                            <input type="file" name="custom_image[<?= $ci->id ?>]" accept="image/jpeg,image/png" class="fx-input" style="padding:10px 14px;">
                            <?php if (!empty($ci->custom_text)): ?>
                                <div class="fst-italic mt-2 fx-hint"><i class="fas fa-pen-nib me-1"></i>"<?= htmlspecialchars($ci->custom_text) ?>"</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>
            </div>

            <!-- Kolom 2: Ringkasan -->
            <div class="col-lg-5" data-reveal style="--rd:.12s;">
                <div class="summary-card card-soft">
                    <h5 class="mb-4" style="font-family:'Playfair Display',Georgia,serif; font-weight:500; font-size:1.35rem;">Pesanan Anda</h5>

                    <div class="mb-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center gap-3 py-3" style="border-bottom:1px solid var(--line);">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/50x60/f6f2ea/1a1511?text=P' ?>" alt="" style="width:46px; height:56px; object-fit:cover; border-radius:8px;">
                                    <div>
                                        <div class="fw-semibold" style="font-size:.86rem;"><?= htmlspecialchars($item->name) ?></div>
                                        <?php if ((!empty($item->color) && $item->color !== 'Standar') || (!empty($item->size) && $item->size !== 'Standar')): ?>
                                            <div class="cart-var"><?= trim((!empty($item->color) && $item->color !== 'Standar' ? htmlspecialchars($item->color) . ' / ' : '') . (!empty($item->size) && $item->size !== 'Standar' ? htmlspecialchars($item->size) : ''), ' /') ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($item->sleeve) || !empty($item->material)): ?>
                                            <div class="cart-var"><?= trim((!empty($item->sleeve) ? htmlspecialchars($item->sleeve) . ' / ' : '') . (!empty($item->material) ? htmlspecialchars($item->material) : ''), ' /') ?></div>
                                        <?php endif; ?>
                                        <div class="cart-var">Qty: <?= $item->qty ?></div>
                                    </div>
                                </div>
                                <div class="small fw-semibold tnum text-nowrap">Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="sum-row">
                        <span class="lbl">Subtotal</span>
                        <span class="tnum">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="lbl">Pengiriman</span>
                        <span style="color:var(--ok); font-weight:600;">Gratis</span>
                    </div>

                    <hr style="border-color:var(--line); opacity:1;">

                    <div class="sum-row sum-total pt-2">
                        <span>Total</span>
                        <span class="tnum">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>

                    <button type="submit" class="btn-ink btn-block2 mt-4 mb-3">
                        Pesan sekarang
                    </button>

                    <p class="text-center mb-0" style="color:var(--muted); font-size:.78rem;">
                        <i class="fas fa-lock me-1"></i> Transaksi Anda terenkripsi dan aman.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
