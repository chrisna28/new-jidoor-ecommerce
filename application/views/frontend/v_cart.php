<div class="container">
    <div class="page-head text-center" data-reveal>
        <span class="eyebrow eyebrow-plain">Keranjang belanja</span>
        <h1 class="page-title mt-3">Tas Anda</h1>
        <p style="color:var(--muted); font-size:.92rem;" class="mb-0">Periksa pesanan Anda sebelum melanjutkan ke pembayaran.</p>
    </div>

    <?php if (!empty($cart_items)): ?>
        <div class="row g-5 pb-sect">
            <!-- Daftar Item -->
            <div class="col-lg-8" data-reveal>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-row flex-column flex-sm-row align-items-sm-center">
                        <a href="<?= base_url('produk/' . ($item->slug ?? '')) ?>">
                            <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/100x120/f6f2ea/1a1511?text=P' ?>" class="cart-thumb" alt="<?= htmlspecialchars($item->name) ?>">
                        </a>
                        <div class="flex-grow-1 mt-3 mt-sm-0">
                            <a href="<?= base_url('produk/' . ($item->slug ?? '')) ?>" class="cart-name"><?= htmlspecialchars($item->name) ?></a>
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
                            <div class="cart-var tnum">Rp <?= number_format($item->price, 0, ',', '.') ?></div>
                        </div>

                        <form action="<?= base_url('keranjang/update') ?>" method="post" class="my-3 my-sm-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="cart_id" value="<?= $item->id ?>">
                            <div class="qty-stepper">
                                <button type="submit" name="action" value="minus" aria-label="Kurangi">−</button>
                                <input type="text" value="<?= $item->qty ?>" readonly>
                                <button type="submit" name="action" value="plus" aria-label="Tambah">+</button>
                            </div>
                        </form>

                        <div class="text-sm-end ms-auto" style="min-width:110px;">
                            <div class="fw-semibold tnum">Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?></div>
                            <a href="<?= base_url('keranjang/hapus/' . $item->id) ?>" class="small text-decoration-none" style="color:var(--bad);"><i class="fas fa-xmark me-1"></i>Hapus</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-4 pt-2">
                    <a href="<?= base_url('katalog') ?>" class="btn-text2"><i class="fas fa-arrow-left"></i> Lanjut belanja</a>
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="col-lg-4" data-reveal style="--rd:.12s;">
                <div class="summary-card card-soft">
                    <h5 class="mb-4" style="font-family:'Playfair Display',Georgia,serif; font-weight:500; font-size:1.35rem;">Ringkasan</h5>

                    <div class="sum-row">
                        <span class="lbl">Subtotal</span>
                        <span class="tnum">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-row">
                        <span class="lbl">Pengiriman</span>
                        <span style="color:var(--ok); font-weight:600;">Gratis</span>
                    </div>

                    <hr style="border-color:var(--line); opacity:1;">

                    <div class="fx-field">
                        <label>Kode promo</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="fx-input" placeholder="Masukkan kode">
                            <button type="button" class="btn-line btn-sm2 flex-shrink-0">Pakai</button>
                        </div>
                    </div>

                    <div class="sum-row sum-total pt-2">
                        <span>Total pesanan</span>
                        <span class="tnum">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>

                    <a href="<?= base_url('checkout') ?>" class="btn-ink btn-block2 mt-4 mb-3">
                        Lanjut ke pembayaran
                    </a>

                    <p class="text-center mb-0" style="color:var(--muted); font-size:.78rem;">
                        <i class="fas fa-lock me-1"></i> Transaksi Anda aman dan terenkripsi.
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty2 card-soft mb-sect" data-reveal>
            <div class="ico"><i class="fas fa-bag-shopping"></i></div>
            <h3>Tas Anda masih kosong</h3>
            <p>Sepertinya Anda belum menambahkan produk apa pun ke tas.</p>
            <a href="<?= base_url('katalog') ?>" class="btn-ink">Mulai belanja</a>
        </div>
    <?php endif; ?>
</div>
