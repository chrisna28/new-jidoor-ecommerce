<div class="container">
    <div class="page-head">
        <nav class="crumb2" aria-label="breadcrumb">
            <a href="<?= base_url() ?>">Beranda</a>
            <span class="sep">/</span>
            <strong>Rating saya</strong>
        </nav>
        <h1 class="page-title">Riwayat rating saya</h1>
        <p style="color:var(--muted); font-size:.9rem;" class="mb-0">Daftar produk yang telah Anda berikan penilaian.</p>
    </div>

    <?php if (empty($ratings)): ?>
        <div class="empty2 card-soft mb-5">
            <div class="ico"><i class="far fa-star"></i></div>
            <h3>Belum ada rating</h3>
            <p>Anda belum pernah memberikan rating pada produk apa pun.</p>
            <a href="<?= base_url('katalog') ?>" class="btn-ink">Mulai belanja</a>
        </div>
    <?php else: ?>
        <div class="card-soft overflow-hidden mb-5">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-color:var(--line);">
                    <thead>
                        <tr class="small text-uppercase fw-semibold" style="background:var(--paper); letter-spacing:.1em; color:var(--muted);">
                            <th class="ps-4 py-3 border-0" style="font-weight:600;">Produk</th>
                            <th class="py-3 border-0" style="font-weight:600;">Penilaian</th>
                            <th class="py-3 border-0" style="font-weight:600;">Komentar</th>
                            <th class="pe-4 py-3 border-0 text-end" style="font-weight:600;">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ratings as $r): ?>
                            <tr>
                                <td class="ps-4 py-4" style="border-color:var(--line);">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $r->product_image && $r->product_image !== 'default.jpg' ? base_url('uploads/products/' . $r->product_image) : 'https://placehold.co/100x100/f5f5f5/000000?text=IMG' ?>" alt="" class="rounded-3" style="width: 60px; height: 60px; object-fit: cover;">
                                        <div>
                                            <a href="<?= base_url('produk/' . $r->product_slug) ?>" class="cart-name d-block mb-1"><?= htmlspecialchars($r->product_name) ?></a>
                                            <span class="cart-var tnum">Rp <?= number_format($r->product_price, 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4" style="border-color:var(--line);">
                                    <div class="stars">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="fa<?= $i <= $r->rating ? 's' : 'r off' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="small fw-semibold"><?= $r->rating ?>/5</span>
                                </td>
                                <td class="py-4" style="border-color:var(--line);">
                                    <p class="small mb-0" style="max-width: 300px; color:var(--muted);">
                                        <?= $r->review ? nl2br(htmlspecialchars($r->review)) : '<span class="fst-italic opacity-50">Tidak ada komentar</span>' ?>
                                    </p>
                                </td>
                                <td class="pe-4 py-4 text-end" style="border-color:var(--line);">
                                    <span class="small" style="color:var(--muted);"><?= tanggal_indo(strtotime($r->created_at)) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
