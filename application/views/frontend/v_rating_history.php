<div class="container py-5 mt-5">
    <div class="mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none text-muted small text-uppercase ls-1">Home</a></li>
                <li class="breadcrumb-item active small text-uppercase ls-1 fw-bold" aria-current="page">My Ratings</li>
            </ol>
        </nav>
        <h1 class="fw-bold display-5 mb-0 ls-1">RIWAYAT RATING SAYA</h1>
        <p class="text-muted">Daftar produk yang telah Anda berikan penilaian.</p>
    </div>

    <div class="bg-white rounded-5 shadow-sm border border-light overflow-hidden">
        <?php if (empty($ratings)): ?>
            <div class="text-center py-5">
                <i class="far fa-star fs-1 text-muted opacity-25 mb-3"></i>
                <h4 class="fw-bold">Belum Ada Rating</h4>
                <p class="text-muted">Anda belum pernah memberikan rating pada produk apapun.</p>
                <a href="<?= base_url('katalog') ?>" class="btn-discovery d-inline-block mt-3">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="small text-uppercase ls-1 fw-bold">
                            <th class="ps-4 py-3 border-0">Produk</th>
                            <th class="py-3 border-0">Penilaian</th>
                            <th class="py-3 border-0">Komentar</th>
                            <th class="pe-4 py-3 border-0 text-end">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ratings as $r): ?>
                            <tr>
                                <td class="ps-4 py-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $r->product_image && $r->product_image !== 'default.jpg' ? base_url('uploads/products/' . $r->product_image) : 'https://placehold.co/100x100/f5f5f5/000000?text=IMG' ?>" class="rounded-3 shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                        <div>
                                            <a href="<?= base_url('produk/' . $r->product_slug) ?>" class="text-dark fw-bold text-decoration-none d-block mb-1"><?= htmlspecialchars($r->product_name) ?></a>
                                            <span class="small text-muted">Rp <?= number_format($r->product_price, 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="text-warning small">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="fa<?= $i <= $r->rating ? 's' : 'r' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="small fw-bold"><?= $r->rating ?>/5</span>
                                </td>
                                <td class="py-4">
                                    <p class="small text-muted mb-0" style="max-width: 300px;">
                                        <?= $r->review ? nl2br(htmlspecialchars($r->review)) : '<span class="fst-italic opacity-50">Tidak ada komentar</span>' ?>
                                    </p>
                                </td>
                                <td class="pe-4 py-4 text-end">
                                    <span class="small text-muted fw-bold"><?= date('d M Y', strtotime($r->created_at)) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.rounded-5 { border-radius: 30px !important; }
.ls-1 { letter-spacing: 1px; }
.btn-discovery {
    background: #000;
    color: #fff;
    padding: 12px 30px;
    text-transform: uppercase;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-decoration: none;
    transition: all 0.3s ease;
}
.btn-discovery:hover {
    background: #333;
    color: #fff;
    transform: translateY(-2px);
}
</style>
