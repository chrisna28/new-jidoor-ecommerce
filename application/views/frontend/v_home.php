<!-- FontAwesome 6 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<!-- Hero Section Mixtas Style -->
<section class="hero-mixtas" style="background-image: url('<?= base_url('assets/images/hero.jpeg') ?>');">
    <div class="hero-overlay"></div>
    
    <div class="container hero-content">
        <div class="row">
            <div class="col-lg-7">
                <span class="hero-label">Premium Apparel Production</span>
                <h1 class="hero-title">Solusi Konveksi Terpercaya & Berkualitas Tinggi</h1>
                <a href="<?= base_url('katalog') ?>" class="btn-discovery">Pesan Sekarang</a>
            </div>
        </div>
    </div>
</section>

<!-- Sectioned Recommendation Feed -->
<?php if (!empty($sections)): ?>
    <?php foreach ($sections as $section): ?>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <?php 
                    $badge_class = 'bg-primary';
                    if ($section['origin'] == 'Hybrid') $badge_class = 'bg-success';
                    if ($section['origin'] == 'Discovery') $badge_class = 'bg-dark';
                    if ($section['origin'] == 'Trending') $badge_class = 'bg-warning text-dark';
                ?>
                <span class="badge <?= $badge_class ?> mb-2 px-3 py-2" style="font-size: 0.6rem; border-radius: 20px; letter-spacing: 1px; text-transform: uppercase;">
                    <?= $section['origin'] ?> SIGNAL
                </span>
                <h2 class="fw-bold m-0" style="font-size: 1.8rem; letter-spacing: -1px;"><?= $section['title'] ?></h2>
            </div>
            <a href="<?= base_url('katalog') ?>" class="text-dark fw-bold text-decoration-none small opacity-50 hover-opacity-100">EXPLORE ALL <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($section['products'] as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
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
    <hr class="container opacity-5">
    <?php endforeach; ?>
<?php endif; ?>

<!-- New Arrivals Section -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-4" style="font-size: 2.5rem;">New Arrivals</h2>
        
        <!-- Tab Filters -->
        <div class="shop-filter-nav">
            <a href="#" data-filter="all" class="shop-filter-link active">All Collection</a>
            <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                <a href="#" data-filter="<?= $cat->slug ?>" class="shop-filter-link"><?= htmlspecialchars($cat->name) ?></a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="row g-4" id="new-arrivals-grid">
        <?php if (!empty($latest_products)): foreach ($latest_products as $p): ?>
            <div class="col-6 col-md-4 col-lg-3 product-item" data-category="<?= $p->category_slug ?>">
                <?php $this->load->view('frontend/components/product_card', ['p' => $p, 'badge_text' => isset($p->badge_text) ? $p->badge_text : null]); ?>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Promo Banners Grid matching the bottom of the image -->
<div class="container-fluid px-4 mb-5">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="position-relative overflow-hidden" style="height: 500px;">
                <img src="<?= base_url('assets/images/panel-1.jpeg') ?>" class="w-100 h-100 object-fit-cover" alt="Banner 1">
                <div class="hero-overlay"></div>
                <div class="position-absolute top-0 start-0 p-5 text-white">
                    <span class="text-uppercase small ls-2 mb-2 d-block">High Capacity</span>
                    <h2 class="display-5 fw-bold mb-4">Produksi Massal<br>Standar Industri</h2>
                    <a href="<?= base_url('katalog') ?>" class="btn-discovery px-4 py-2" style="font-size: 0.7rem;">Lihat Produk</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 d-flex flex-column" style="height: 500px;">
            <div class="position-relative overflow-hidden mb-4" style="height: 240px; flex-shrink: 0;">
                <img src="<?= base_url('assets/images/panel-2.jpeg') ?>" class="w-100 h-100 object-fit-cover" alt="Banner 2">
                <div class="hero-overlay"></div>
                <div class="position-absolute top-0 start-0 p-4 text-white">
                    <span class="text-uppercase small ls-2 mb-2 d-block">Premium Material</span>
                    <h3 class="fw-bold mb-3">Pilihan Bahan<br>Terbaik & Nyaman</h3>
                    <a href="<?= base_url('katalog') ?>" class="btn-discovery px-3 py-1" style="font-size: 0.6rem;">Pesan Sekarang</a>
                </div>
            </div>

            <div class="row g-4 flex-grow-1">
                <div class="col-6">
                    <div class="position-relative overflow-hidden" style="height: 236.5px">
                         <img src="<?= base_url('assets/images/panel-3.jpeg') ?>" class="w-100 h-100 object-fit-cover" alt="Banner 3">
                         <div class="hero-overlay"></div>
                         <div class="position-absolute top-0 start-0 p-4 text-white" style="z-index: 2;">
                            <span class="text-uppercase small ls-1 mb-1 d-block">Custom Design</span>
                            <h5 class="fw-bold mb-2">Desain Bebas<br>Sesuai Keinginan</h5>
                            <a href="<?= base_url('katalog') ?>" class="btn-discovery px-2 py-1" style="font-size: 0.5rem;">Cek Katalog</a>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="position-relative overflow-hidden" style="height: 236.5px">
                        <img src="<?= base_url('assets/images/panel-4.jpeg') ?>" class="w-100 h-100 object-fit-cover" alt="Banner 4">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 p-5 d-flex flex-column justify-content-center text-center text-white">
                            <span class="text-uppercase small ls-2 mb-2">Data Penjualan</span>
                            <h2 class="display-3 fw-bold mb-0"><?= number_format($total_orders) ?>+</h2>
                            <p class="mb-4 small">Pesanan Berhasil Diproses</p>
                            <a href="<?= base_url('katalog') ?>" class="btn-discovery px-3 py-1 mx-auto" style="font-size: 0.6rem;">Lihat Testimoni</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterLinks = document.querySelectorAll('.shop-filter-link');
    const products = document.querySelectorAll('#new-arrivals-grid .product-item');

    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            filterLinks.forEach(l => l.classList.remove('active'));
            // Add active class to clicked link
            this.classList.add('active');

            const filterValue = this.getAttribute('data-filter');

            // Show products with 4-limit for 'all'
            let count = 0;
            products.forEach(product => {
                if (filterValue === 'all') {
                    if (count < 4) {
                        product.style.display = 'block';
                        count++;
                    } else {
                        product.style.display = 'none';
                    }
                } else if (product.getAttribute('data-category') === filterValue) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        });
    });

    // Initial state: Show top 4 products
    let initCount = 0;
    products.forEach(p => {
        if (initCount < 4) {
            p.style.display = 'block';
            initCount++;
        } else {
            p.style.display = 'none';
        }
    });

});
</script>
