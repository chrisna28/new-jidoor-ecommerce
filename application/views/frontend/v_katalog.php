<?php $is_search = isset($keyword); ?>
<div class="container">
    <!-- Header -->
    <div class="page-head">
        <nav class="crumb2" aria-label="breadcrumb">
            <a href="<?= base_url() ?>">Beranda</a>
            <span class="sep">/</span>
            <strong>Katalog</strong>
        </nav>
        <h1 class="page-title">
            <?= $is_search ? 'Hasil untuk “' . htmlspecialchars($keyword) . '”' : (isset($active_slug) ? htmlspecialchars(ucwords(str_replace('-', ' ', $active_slug))) : 'Semua koleksi') ?>
        </h1>
    </div>

    <div class="row g-5 pb-5">
        <!-- Sidebar Filter -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="sticky-top" style="top: 104px;">
                <span class="eyebrow" style="margin-bottom:16px;">Kategori</span>
                <ul class="filter-list mt-3">
                    <li>
                        <a href="<?= base_url('katalog') ?>" class="<?= !isset($active_slug) ? 'active' : '' ?>">
                            <span>Semua koleksi</span>
                        </a>
                    </li>
                    <?php if (isset($categories)): foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= base_url('kategori/' . $cat->slug) ?>" class="<?= (isset($active_slug) && $active_slug == $cat->slug) ? 'active' : '' ?>">
                            <span><?= htmlspecialchars($cat->name) ?></span>
                        </a>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>
        </div>

        <!-- Kolom Produk -->
        <div class="col-lg-9">
            <?php if (!empty($products)): ?>
                <div class="katalog-toolbar">
                    <span class="count-txt"><?= count($products) ?> produk<?= $is_search ? ' ditemukan' : '' ?></span>
                    <a href="<?= base_url('katalog') ?>" class="sec-link" style="font-size:.78rem;">Atur ulang <i class="fas fa-rotate-left" style="font-size:.7rem;"></i></a>
                </div>

                <div class="row g-4">
                    <?php foreach ($products as $p): ?>
                        <div class="col-6 col-md-4 col-lg-3">
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

                <?php if (!empty($pagination)): ?>
                <div class="pagination-v2">
                    <?= $pagination ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty2 card-soft">
                    <div class="ico"><i class="fas fa-magnifying-glass"></i></div>
                    <h3>Produk tidak ditemukan</h3>
                    <p>Tidak ada produk yang cocok dengan pencarian Anda.</p>
                    <a href="<?= base_url('katalog') ?>" class="btn-ink btn-sm2">Atur ulang filter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
