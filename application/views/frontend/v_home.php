<!-- Hero Editorial -->
<section class="hero-ed">
    <div class="hero-ed-photo" style="background-image: url('<?= base_url('assets/images/hero.jpeg') ?>');"></div>
    <div class="hero-ed-scrim"></div>
    <div class="hero-ed-inner">
        <span class="eyebrow">Produksi Busana Premium</span>
        <h1>Gaya yang tenang,<em> dibuat dengan teliti.</em></h1>
        <p class="lead-txt">Koleksi busana minimalis berbahan premium — jahitan rapi, potongan bersih, dan nyaman dipakai setiap hari.</p>
        <div class="d-flex gap-3 flex-wrap">
            <a href="<?= base_url('katalog') ?>" class="btn-ink">Jelajahi Katalog <i class="fas fa-arrow-right" style="font-size:.75rem;"></i></a>
            <a href="#koleksi-baru" class="btn-line" style="color:#f7f4ef; border-color:rgba(247,244,239,.4);">Koleksi Baru</a>
        </div>
        <div class="hero-stats">
            <div><b><?= number_format($total_orders) ?>+</b><span>Pesanan diproses</span></div>
            <div><b>Premium</b><span>Standar bahan</span></div>
            <div><b>Nasional</b><span>Jangkauan kirim</span></div>
        </div>
    </div>
</section>

<!-- Sectioned Recommendation Feed -->
<?php if (!empty($sections)): ?>
    <?php foreach ($sections as $i => $section): ?>
    <section class="sect">
        <div class="container">
            <div class="sec-head">
                <div>
                    <span class="eyebrow">Sinyal <?= $section['origin'] ?></span>
                    <h2 class="mt-2"><?= $section['title'] ?></h2>
                </div>
                <a href="<?= base_url('katalog') ?>" class="sec-link d-none d-md-inline-flex">Lihat semua <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
                <?php foreach ($section['products'] as $p): ?>
                    <div class="col">
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
            <div>
                <span class="eyebrow">Baru tiba</span>
                <h2 class="mt-2">Koleksi terbaru</h2>
            </div>
            <a href="<?= base_url('katalog') ?>" class="sec-link d-none d-md-inline-flex">Semua produk <i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- Tab Filter -->
        <div class="chipbar mb-5">
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
<section class="sect pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <a href="<?= base_url('katalog') ?>" class="panel2 h-100" style="min-height:500px;">
                    <img src="<?= base_url('assets/images/panel-1.jpeg') ?>" alt="Produksi massal">
                    <div class="panel2-body">
                        <span class="eyebrow">Kapasitas besar</span>
                        <h3 style="font-size:clamp(1.5rem,2vw,2.1rem);">Produksi massal<br>dengan standar industri</h3>
                        <span class="panel2-cta">Lihat produk <i class="fas fa-arrow-right" style="font-size:.72rem;"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-md-6 d-flex flex-column gap-4" style="min-height:500px;">
                <a href="<?= base_url('katalog') ?>" class="panel2 flex-grow-1" style="min-height:240px;">
                    <img src="<?= base_url('assets/images/panel-2.jpeg') ?>" alt="Bahan premium">
                    <div class="panel2-body">
                        <span class="eyebrow">Bahan premium</span>
                        <h3 style="font-size:clamp(1.25rem,1.6vw,1.7rem);">Pilihan bahan terbaik &amp; nyaman</h3>
                        <span class="panel2-cta">Pesan sekarang <i class="fas fa-arrow-right" style="font-size:.72rem;"></i></span>
                    </div>
                </a>
                <div class="row g-4 flex-grow-1">
                    <div class="col-6">
                        <a href="<?= base_url('katalog') ?>" class="panel2 h-100" style="min-height:236px;">
                            <img src="<?= base_url('assets/images/panel-3.jpeg') ?>" alt="Desain kustom">
                            <div class="panel2-body">
                                <span class="eyebrow">Desain kustom</span>
                                <h3 style="font-size:clamp(1.05rem,1.3vw,1.35rem);">Desain bebas sesuai keinginan</h3>
                                <span class="panel2-cta">Cek katalog <i class="fas fa-arrow-right" style="font-size:.7rem;"></i></span>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <div class="stat2 h-100" style="min-height:236px;">
                            <span class="eyebrow" style="color:rgba(247,244,239,.6); margin-bottom:12px;">Data penjualan</span>
                            <div class="num"><?= number_format($total_orders) ?>+</div>
                            <p class="mb-0 mt-2" style="font-size:.82rem; color:rgba(247,244,239,.65);">Pesanan berhasil diproses</p>
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

    // Status awal: tampilkan 4 produk teratas
    applyFilter('all');
});
</script>
