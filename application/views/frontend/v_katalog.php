<!-- FontAwesome 6 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div class="container py-5 mt-5">
    <!-- Header Katalog -->
    <div class="mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none text-muted small text-uppercase ls-1">Home</a></li>
                <li class="breadcrumb-item active small text-uppercase ls-1 fw-bold" aria-current="page">Shop</li>
            </ol>
        </nav>
        <h1 class="fw-bold display-5 mb-0 ls-1">
            <?= isset($keyword) ? 'SEARCH: "' . htmlspecialchars($keyword) . '"' : (isset($active_slug) ? 'CATEGORY: ' . strtoupper(str_replace('-', ' ', $active_slug)) : 'ALL COLLECTION') ?>
        </h1>
    </div>

    <div class="row g-5">
        <!-- Sidebar Filter (Minimalist) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="sticky-top" style="top: 100px; z-index: 10;">
                <h6 class="fw-bold text-uppercase ls-2 mb-4 pb-2 border-bottom">Categories</h6>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <a href="<?= base_url('katalog') ?>" class="text-decoration-none <?= !isset($active_slug) ? 'text-dark fw-bold' : 'text-muted' ?> small text-uppercase ls-1">All Collection</a>
                    </li>
                    <?php if (isset($categories)): foreach ($categories as $cat): ?>
                    <li class="mb-3">
                        <a href="<?= base_url('kategori/' . $cat->slug) ?>" class="text-decoration-none <?= (isset($active_slug) && $active_slug == $cat->slug) ? 'text-dark fw-bold' : 'text-muted' ?> small text-uppercase ls-1">
                            <?= htmlspecialchars($cat->name) ?>
                        </a>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>
        </div>

        <!-- Product Column -->
        <div class="col-lg-9">


            <?php if (!empty($products)): ?>
                <div class="row g-4">
                    <?php foreach ($products as $p): ?>
                        <div class="col-6 col-md-4">
                            <?php 
                                $origin = isset($rec_origins[$p->id]) ? $rec_origins[$p->id] : null;
                                $this->load->view('frontend/components/product_card', [
                                    'p' => $p,
                                    'badge_text' => $origin ? $origin : null
                                ]); 
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-5 pt-5 border-top">
                    <?= $pagination ?>
                </div>

            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fs-1 text-muted opacity-25"></i>
                    </div>
                    <h3 class="fw-bold ls-1">No Products Found</h3>
                    <p class="text-muted">We couldn't find any products matching your criteria.</p>
                    <a href="<?= base_url('katalog') ?>" class="btn-discovery d-inline-block mt-4">Reset Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.custom-range::-webkit-slider-thumb { background: #000; }
.custom-range::-moz-range-thumb { background: #000; }
.pagination .page-item .page-link {
    border: none;
    color: #000;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 10px 18px;
    margin: 0 5px;
}
.pagination .page-item.active .page-link {
    background-color: #000;
    color: #fff;
}
</style>
