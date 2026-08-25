<!-- Hero Editorial -->
<section class="hero-ed">
    <div class="hero-ed-photo" style="background-image: url('<?= base_url('assets/images/hero.jpeg') ?>');"></div>
    <div class="hero-ed-scrim"></div>
    <div class="hero-ed-inner">
        <span class="eyebrow">Produksi busana premium</span>
        <h1 data-reveal>Gaya yang tenang, <em>dibuat dengan teliti.</em></h1>
        <p class="lead-txt" data-reveal style="--rd:.1s;">Koleksi busana minimalis berbahan premium — jahitan rapi, potongan bersih, dan nyaman dipakai setiap hari.</p>
        <div class="d-flex gap-3 flex-wrap" data-reveal style="--rd:.2s;">
            <a href="<?= base_url('katalog') ?>" class="btn-ink">Jelajahi katalog <i class="fas fa-arrow-right"></i></a>
            <a href="#koleksi-baru" class="btn-line">Koleksi baru</a>
        </div>
        <div class="hero-stats" data-reveal style="--rd:.3s;">
            <div><b><?= number_format($total_orders) ?>+</b><span>Pesanan diproses</span></div>
            <div><b>Premium</b><span>Standar bahan</span></div>
            <div><b>Nasional</b><span>Jangkauan kirim</span></div>
        </div>
    </div>
</section>

<!-- Marquee band -->
<div class="marquee-band" aria-hidden="true">
    <div class="marquee-track">
        <?php for ($m = 0; $m < 2; $m++): ?>
        <span>Bahan premium <i class="fas fa-asterisk"></i> Jahitan rapi <i class="fas fa-asterisk"></i> Produksi kustom <i class="fas fa-asterisk"></i> Desain bebas <i class="fas fa-asterisk"></i> Kirim se-Indonesia <i class="fas fa-asterisk"></i> Garansi 3 tahun <i class="fas fa-asterisk"></i></span>
        <?php endfor; ?>
    </div>
</div>

<!-- Sectioned Recommendation Feed -->
<?php if (!empty($sections)): ?>
    <?php foreach ($sections as $i => $section): ?>
    <section class="sect">
        <div class="container">
            <div class="sec-head">
                <div data-reveal>
                    <span class="sec-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?> — sinyal <?= $section['origin'] ?></span>
                    <h2><?= $section['title'] ?></h2>
                </div>
                <a href="<?= base_url('katalog') ?>" class="sec-link d-none d-md-inline-flex" data-reveal style="--rd:.1s;">Lihat semua <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
                <?php foreach ($section['products'] as $p): ?>
                    <div class="col" data-reveal>
                        <?php
                            $this->load->view('frontend/components/product_card', [
                                'p' => $p,
                                'badge_text' => isset($p->badge_text) ? $p->badge_text : null
                            ]);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Koleksi Baru -->
<section class="sect" id="koleksi-baru">
    <div class="container">
        <div class="sec-head">
            <div data-reveal>
                <span class="sec-num">Baru tiba</span>
                <h2>Koleksi terbaru</h2>
            </div>
            <a href="<?= base_url('katalog') ?>" class="sec-link d-none d-md-inline-flex" data-reveal style="--rd:.1s;">Semua produk <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- Tab Filter -->
        <div class="chipbar mb-5" data-reveal style="--rd:.15s;">
            <button type="button" data-filter="all" class="shop-filter-link chip-f active">Semua</button>
            <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                <button type="button" data-filter="<?= $cat->slug ?>" class="shop-filter-link chip-f"><?= htmlspecialchars($cat->name) ?></button>
            <?php endforeach; endif; ?>
        </div>

        <!-- Grid Produk -->
        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5" id="new-arrivals-grid">
            <?php if (!empty($latest_products)): foreach ($latest_products as $p): ?>
                <div class="col product-item" data-category="<?= $p->category_slug ?>">
                    <?php $this->load->view('frontend/components/product_card', ['p' => $p, 'badge_text' => isset($p->badge_text) ? $p->badge_text : null]); ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<!-- Panel Promosi -->
<section class="sect pb-sect">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-md-6" data-reveal>
                <a href="<?= base_url('katalog') ?>" class="panel2 h-100" style="min-height:520px;">
                    <img src="<?= base_url('assets/images/panel-1.jpeg') ?>" alt="Produksi massal">
                    <div class="panel2-body">
                        <span class="eyebrow">Kapasitas besar</span>
                        <h3 style="font-size:clamp(1.6rem,2.2vw,2.3rem);">Produksi massal<br>dengan standar industri</h3>
                        <span class="panel2-cta">Lihat produk <i class="fas fa-arrow-right" style="font-size:.7rem;"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-md-6 d-flex flex-column gap-4" style="min-height:520px;">
                <a href="<?= base_url('katalog') ?>" class="panel2 flex-grow-1" style="min-height:240px; --rd:.1s;" data-reveal>
                    <img src="<?= base_url('assets/images/panel-2.jpeg') ?>" alt="Bahan premium">
                    <div class="panel2-body">
                        <span class="eyebrow">Bahan premium</span>
                        <h3 style="font-size:clamp(1.25rem,1.6vw,1.7rem);">Pilihan bahan terbaik &amp; nyaman</h3>
                        <span class="panel2-cta">Pesan sekarang <i class="fas fa-arrow-right" style="font-size:.7rem;"></i></span>
                    </div>
                </a>
                <div class="row g-4 flex-grow-1">
                    <div class="col-6" data-reveal style="--rd:.12s;">
                        <a href="<?= base_url('katalog') ?>" class="panel2 h-100" style="min-height:236px;">
                            <img src="<?= base_url('assets/images/panel-3.jpeg') ?>" alt="Desain kustom">
                            <div class="panel2-body">
                                <span class="eyebrow">Desain kustom</span>
                                <h3 style="font-size:clamp(1.05rem,1.3vw,1.35rem);">Desain bebas sesuai keinginan</h3>
                                <span class="panel2-cta">Cek katalog <i class="fas fa-arrow-right" style="font-size:.68rem;"></i></span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6" data-reveal style="--rd:.22s;">
                        <div class="stat2 h-100" style="min-height:236px;">
                            <span class="eyebrow" style="color:rgba(239,233,221,.75); margin-bottom:14px;">Data penjualan</span>
                            <div class="num"><?= number_format($total_orders) ?>+</div>
                            <p class="mb-0 mt-3" style="font-size:.86rem; color:rgba(239,233,221,.78);">Pesanan berhasil diproses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterLinks = document.querySelectorAll('.shop-filter-link');
    const products = document.querySelectorAll('#new-arrivals-grid .product-item');

    function applyFilter(filterValue) {
        let count = 0;
        products.forEach(product => {
            if (filterValue === 'all') {
                if (count < 5) {
                    product.style.display = '';
                    count++;
                } else {
                    product.style.display = 'none';
                }
            } else if (product.getAttribute('data-category') === filterValue) {
                product.style.display = '';
            } else {
                product.style.display = 'none';
            }
        });
    }

    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            filterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            applyFilter(this.getAttribute('data-filter'));
        });
    });

    // Status awal: tampilkan 5 produk teratas
    applyFilter('all');
});
</script>
