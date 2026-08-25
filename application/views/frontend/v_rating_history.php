<div class="container">
    <div class="page-head" data-reveal>
        <nav class="crumb2" aria-label="breadcrumb">
            <a href="<?= base_url() ?>">Beranda</a>
            <span class="sep">/</span>
            <strong>Rating saya</strong>
        </nav>
        <h1 class="page-title">Riwayat rating saya</h1>
        <p style="color:var(--muted); font-size:.92rem;" class="mb-0 mt-2">Daftar produk yang telah Anda berikan penilaian.</p>
    </div>

    <?php if (empty($ratings)): ?>
        <div class="empty2 card-soft mb-sect">
            <div class="ico"><i class="far fa-star"></i></div>
            <h3>Belum ada rating</h3>
            <p>Anda belum pernah memberikan rating pada produk apa pun.</p>
            <a href="<?= base_url('katalog') ?>" class="btn-ink">Mulai belanja</a>
        </div>
    <?php else: ?>
        <div class="pb-sect">
            <?php foreach ($ratings as $r): ?>
                <div class="review-card2 card-soft d-flex flex-column flex-sm-row gap-4 align-items-sm-center">
                    <a href="<?= base_url('produk/' . $r->product_slug) ?>" class="flex-shrink-0">
                        <img src="<?= $r->product_image && $r->product_image !== 'default.jpg' ? base_url('uploads/products/' . $r->product_image) : 'https://placehold.co/100x125/f6f2ea/1a1511?text=IMG' ?>" alt="<?= htmlspecialchars($r->product_name) ?>" style="width:76px; height:95px; object-fit:cover; border-radius:10px;">
                    </a>
                    <div class="flex-grow-1">
                        <a href="<?= base_url('produk/' . $r->product_slug) ?>" class="cart-name d-block mb-1"><?= htmlspecialchars($r->product_name) ?></a>
                        <span class="cart-var tnum d-block mb-2">Rp <?= number_format($r->product_price, 0, ',', '.') ?></span>
                        <?php if ($r->review): ?>
                            <p class="mb-0" style="font-size:.9rem; color:var(--ink-soft); max-width:520px;"><?= nl2br(htmlspecialchars($r->review)) ?></p>
                        <?php else: ?>
                            <p class="mb-0 fst-italic" style="font-size:.85rem; color:var(--muted); opacity:.7;">Tidak ada komentar</p>
                        <?php endif; ?>
                    </div>
                    <div class="text-sm-end flex-shrink-0">
                        <div class="stars mb-1">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fa<?= $i <= $r->rating ? 's' : 'r off' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="small fw-semibold tnum d-block"><?= $r->rating ?>/5</span>
                        <span class="small d-block mt-1" style="color:var(--muted); font-size:.78rem;"><?= tanggal_indo(strtotime($r->created_at)) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
