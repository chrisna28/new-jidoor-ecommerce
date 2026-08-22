<div class="container py-5 mt-5">
    <div class="row g-5">
        <!-- Product Image Showcase -->
        <div class="col-lg-7">
            <div class="product-img-box rounded-5 shadow-sm overflow-hidden bg-white p-0" style="aspect-ratio: 16/10;">
                <img src="<?= $product->image && $product->image !== 'default.jpg' ? base_url('uploads/products/' . $product->image) : 'https://images.unsplash.com/photo-1593642532842-98d0fd5ebc1a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80' ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($product->name) ?>">
            </div>
            
            <div class="mt-5">
                <h5 class="fw-bold text-uppercase ls-2 mb-4 pb-2 border-bottom">Overview</h5>
                <div class="lh-lg text-muted">
                    <?= nl2br(htmlspecialchars($product->description)) ?>
                </div>
            </div>
        </div>

        <!-- Product Purchase Info -->
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 120px;">
                <div class="p-4 p-md-5 bg-white rounded-5 shadow-sm border border-light">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="<?= base_url('katalog') ?>" class="text-decoration-none text-muted small text-uppercase ls-1">Katalog</a></li>
                            <li class="breadcrumb-item active small text-uppercase ls-1 fw-bold" aria-current="page"><?= htmlspecialchars($product->category_name) ?></li>
                        </ol>
                    </nav>
                    
                    <h1 class="fw-bold display-6 mb-3 ls-1"><?= htmlspecialchars($product->name) ?></h1>
                    
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="fs-3 fw-800 text-primary" id="priceDisplay">Rp <?= number_format($product->price, 0, ',', '.') ?></div>
                        <div class="text-muted small border-start ps-3"><?= $product->total_sold ?> terjual</div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <button type="button" onclick="toggleLike(<?= $product->id ?>, this)" class="btn btn-sm btn-light border rounded-pill px-3 py-2">
                            <i class="<?= $is_liked ? 'fas text-danger' : 'far' ?> fa-heart me-1"></i><span class="like-count"><?= (int)$like_count ?></span> Suka
                        </button>
                        <span class="small text-muted"><i class="far fa-comment me-1"></i><?= (int)$comment_count ?> Komentar</span>
                    </div>

                    <hr class="opacity-10 my-4">

                    <?php
                        // Varian nyata = lebih dari 1 baris ATAU bukan kombinasi Standar tunggal
                        $has_real_variants = false;
                        if (!empty($variants)) {
                            if (count($variants) > 1) {
                                $has_real_variants = true;
                            } else {
                                $v0 = $variants[0];
                                if ($v0->color !== 'Standar' || $v0->size !== 'Standar') {
                                    $has_real_variants = true;
                                }
                            }
                        }
                    ?>
                    <form method="post" action="<?= base_url('keranjang/tambah') ?>" id="addToCartForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product->id ?>">
                        <input type="hidden" name="variant_id" id="variantIdInput" value="">

                        <?php if ($has_real_variants): ?>
                        <!-- Pemilih Variasi (ala Shopee) -->
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase ls-1 mb-2 d-block" id="label1">Pilih <?= htmlspecialchars($product->variant_name1 ?: 'Warna') ?></label>
                            <div class="d-flex flex-wrap gap-2" id="colorGroup"></div>
                        </div>

                        <div class="mb-3" id="sizeSection" style="display:none;">
                            <label class="small fw-bold text-uppercase ls-1 mb-2 d-block" id="label2">Pilih <?= htmlspecialchars($product->variant_name2 ?: 'Ukuran') ?></label>
                            <div class="d-flex flex-wrap gap-2" id="sizeGroup"></div>
                        </div>

                        <div class="small text-muted mb-4" id="variantSummary" style="display:none;"></div>
                        <?php endif; ?>

                        <?php if (!empty($product->is_custom)): ?>
                        <!-- Permintaan Custom (Revisi #4) -->
                        <div class="mb-4 p-3 rounded-4 bg-light border border-warning border-opacity-50">
                            <label class="small fw-bold text-uppercase ls-1 mb-2 d-block">
                                <i class="fas fa-wand-magic-sparkles me-1 text-warning"></i> Permintaan Custom
                            </label>
                            <textarea name="custom_text" class="form-control bg-white border-0 rounded-3 p-3" rows="3" maxlength="500" placeholder="Tuliskan desain/permintaan khusus Anda, mis. ukiran nama, motif, atau warna kayu..."></textarea>
                            <div class="small text-muted mt-1"><span id="customCounter">0</span>/500 karakter &middot; Gambar referensi dapat diunggah saat checkout.</div>
                        </div>
                        <?php endif; ?>

                        <?php if ($has_real_variants): ?>
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase ls-1 mb-2 d-block">Catatan (Opsional)</label>
                            <textarea name="note" id="noteInput" class="form-control bg-light border-0 rounded-4 p-3" rows="2" maxlength="200" placeholder="Contoh: minta ukiran huruf awalan nama..."></textarea>
                            <div class="small text-muted mt-1 text-end"><span id="noteCounter">0</span>/200 karakter</div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase ls-1 mb-3 d-block">Pilih Jumlah</label>
                            <div class="input-group border rounded-pill overflow-hidden" style="width: 150px;">
                                <button class="btn btn-link text-dark px-3 py-2 text-decoration-none border-0" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()">-</button>
                                <input type="number" name="qty" id="qtyInput" class="form-control border-0 text-center fw-bold bg-transparent" value="1" min="1" max="<?= $product->stock ?>">
                                <button class="btn btn-link text-dark px-3 py-2 text-decoration-none border-0" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()">+</button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 mb-3" id="addBtn">
                            ADD TO BAG
                        </button>

                        <div class="row g-2 mb-3">
                            <div class="col">
                                <button type="button" onclick="chatAboutProduct()" class="btn btn-outline-dark w-100 py-3 rounded-0 fw-bold ls-1">
                                    <i class="fas fa-comment-dots me-2"></i> CHAT PENJUAL
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" onclick="toggleWishlist(<?= $product->id ?>, this)" class="btn btn-outline-dark w-100 py-3 rounded-0 fw-bold ls-1">
                                    <i class="<?= $is_wishlist ? 'fas text-danger' : 'far' ?> fa-heart me-2"></i>
                                    <?= $is_wishlist ? 'IN YOUR WISHLIST' : 'ADD TO WISHLIST' ?>
                                </button>
                            </div>
                        </div>

                        <?php if ($this->session->userdata('user_id')): ?>
                        <!-- Konteks produk untuk widget chat ala Shopee -->
                        <script>
                            window.__jidoorProduct = {
                                id:    <?= (int)$product->id ?>,
                                name:  <?= json_encode($product->name) ?>,
                                slug:  <?= json_encode($product->slug) ?>,
                                price: <?= (float)$product->price ?>,
                                image: <?= json_encode($product->image) ?>
                            };
                            function chatAboutProduct() {
                                if (window.setChatProduct) { window.setChatProduct(window.__jidoorProduct); }
                            }
                        </script>
                        <?php else: ?>
                        <script>
                            function chatAboutProduct() {
                                window.location.href = '<?= site_url("login") ?>';
                            }
                        </script>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <span class="small text-muted" id="stockInfo"><i class="fas fa-check-circle text-success me-1"></i> Stok Tersedia: <?= $product->stock ?> unit</span>
                        </div>
                    </form>
                </div>

                <div class="mt-4 p-4 rounded-5 bg-light border border-light d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle p-3 shadow-sm">
                        <i class="fas fa-shield-alt text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Garansi Resmi</h6>
                        <p class="small text-muted mb-0">Perlindungan penuh selama 3 tahun.</p>
                    </div> <!-- end Garansi Resmi -->
                </div> <!-- end sticky-top -->
            </div> <!-- end col-lg-5 -->
        </div> <!-- end row g-5 -->

        <!-- Premium Customer Reviews Section -->
        <div class="mt-5 pt-5 border-top">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php 
                        $total_rev = count($reviews);
                        $avg_rev = 0;
                        $dist = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
                        $with_comment = 0;
                        if($total_rev > 0) {
                            $sum = 0;
                            foreach($reviews as $r) {
                                $sum += $r->rating;
                                $dist[$r->rating]++;
                                if(!empty($r->review)) $with_comment++;
                            }
                            $avg_rev = round($sum / $total_rev, 1);
                        }
                    ?>
                    
                    <div class="p-4 rounded-4 mb-5" style="background: #fffbf8; border: 1px solid #f9ede5;">
                        <div class="row align-items-center g-4">
                            <!-- Left: Score -->
                            <div class="col-md-3 text-center border-end-md" style="border-color: #f9ede5 !important;">
                                <div class="mb-1"><span class="display-5 fw-bold text-danger"><?= $avg_rev ?></span> <span class="text-muted">dari 5</span></div>
                                <div class="text-danger fs-4">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="fa<?= $i <= round($avg_rev) ? 's' : 'r' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <!-- Right: Filter Buttons -->
                            <div class="col-md-9">
                                <div class="d-flex flex-wrap gap-2 px-md-4" id="ratingFilterGroup">
                                    <button class="btn btn-filter-jidoor active" data-filter-type="all">Semua</button>
                                    <?php for($i=5; $i>=1; $i--): ?>
                                        <button class="btn btn-filter-jidoor" data-filter-type="rating" data-val="<?= $i ?>"><?= $i ?> Bintang (<?= $dist[$i] ?>)</button>
                                    <?php endfor; ?>
                                    <button class="btn btn-filter-jidoor" data-filter-type="comment">Dengan Komentar (<?= $with_comment ?>)</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="review-list">
                        <?php if (empty($reviews)): ?>
                            <div class="p-5 bg-light rounded-5 text-center mb-5 border border-dashed">
                                <i class="fa-solid fa-comment-dots fs-1 text-muted opacity-25 mb-3"></i>
                                <p class="text-muted mb-0">Belum ada ulasan untuk produk ini.</p>
                            </div>
                        <?php else: ?>
                                    <?php foreach ($reviews as $rev): ?>
                                        <div class="review-card p-4 rounded-4 bg-white border border-light mb-4 shadow-sm" data-rating="<?= $rev->rating ?>" data-has-comment="<?= !empty($rev->review) ? 'true' : 'false' ?>">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.8rem;">
                                                        <?= strtoupper(substr($rev->username, 0, 2)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($rev->username) ?></div>
                                                        <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px;"><?= date('d M Y', strtotime($rev->created_at)) ?></div>
                                                    </div>
                                                </div>
                                                <div class="text-warning" style="font-size: 0.75rem;">
                                                    <?php for($i=1; $i<=5; $i++): ?>
                                                        <i class="fa<?= $i <= $rev->rating ? 's' : 'r' ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="review-text text-secondary lh-base" style="font-size: 0.9rem;">
                                                <?= nl2br(htmlspecialchars($rev->review)) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-4 <?= count($reviews) <= 5 ? 'd-none' : '' ?>" id="loadMoreContainer">
                                    <button class="btn btn-outline-dark rounded-pill px-5 py-2 fw-bold small ls-1" id="loadMoreBtn">
                                        MUAT LEBIH BANYAK <i class="fa-solid fa-chevron-down ms-2 small"></i>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <!-- Add Review Form -->
                            <div class="bg-dark p-5 rounded-5 shadow-lg mt-5 border border-warning border-opacity-10">
                                <h5 class="text-white fw-bold mb-4 d-flex align-items-center">
                                    <i class="fa-solid fa-pen-to-square me-3 text-warning"></i> Berikan Ulasan Anda
                                </h5>
                                <?php if (!$this->session->userdata('user_id')): ?>
                                    <div class="text-center py-4">
                                        <p class="text-light opacity-50 mb-4 small">Silakan login untuk memberikan rating dan komentar pada produk ini.</p>
                                        <a href="<?= base_url('login') ?>" class="btn btn-warning px-5 py-3 fw-bold rounded-pill ls-1 shadow-sm">LOGIN SEKARANG</a>
                                    </div>
                                <?php else: ?>
                                    <form action="<?= base_url('welcome/rate') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= $product->id ?>">
                                        
                                        <div class="mb-4">
                                            <label class="text-white small fw-bold text-uppercase ls-1 d-block mb-3 opacity-75">Rating Anda</label>
                                            <div class="star-rating-input d-flex flex-row-reverse justify-content-end gap-2 fs-3">
                                                <?php for($i=5; $i>=1; $i--): ?>
                                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" class="d-none" <?= $i==5?'required':'' ?> />
                                                    <label for="star<?= $i ?>" class="fa-solid fa-star cursor-pointer text-muted-dark transition-all" title="<?= $i ?> Bintang"></label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="text-white small fw-bold text-uppercase ls-1 d-block mb-3 opacity-75">Komentar</label>
                                            <textarea name="review" class="form-control bg-light bg-opacity-10 border-secondary border-opacity-20 text-white p-4 rounded-4" rows="4" placeholder="Apa pendapat Anda tentang produk ini?" required style="font-size: 0.9rem; color: #fff !important;"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill fw-bold ls-1 mt-3 shadow-sm">
                                            POST REVIEW <i class="fa-solid fa-paper-plane ms-2"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 mt-5 border-top">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="fw-bold ls-1 mb-0">Related Products</h2>
            <p class="text-muted mt-2 mb-0">You might also like these premium selections</p>
        </div>
        <a href="<?= base_url('katalog') ?>" class="text-dark fw-bold text-decoration-none small text-uppercase ls-1">View Collection <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
        <?php if (!empty($related)): foreach ($related as $p): ?>
            <div class="col-6 col-md-3">
                <?php 
                    $is_similar = isset($similar_ids) && in_array($p->id, $similar_ids);
                    $this->load->view('frontend/components/product_card', [
                        'p' => $p,
                        'badge_text' => $is_similar ? 'SIMILAR' : null,
                        'recommended_ids' => isset($recommended_ids) ? $recommended_ids : [],
                        'rec_origins' => isset($rec_origins) ? $rec_origins : []
                    ]); 
                ?>
            </div>
        <?php endforeach; endif; ?>
    </div>

</div>

<style>
.btn-filter-jidoor {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.1);
    color: rgba(0,0,0,0.8);
    font-size: 0.85rem;
    font-weight: 500;
    padding: 8px 15px;
    border-radius: 4px;
    transition: all 0.2s;
    cursor: pointer;
}

.btn-filter-jidoor:hover {
    border-color: #ee4d2d;
    color: #ee4d2d;
}

.btn-filter-jidoor.active {
    background: #fff;
    border-color: #ee4d2d;
    color: #ee4d2d;
}

@media (min-width: 768px) {
    .border-end-md { border-right: 1px solid #f9ede5 !important; }
}

.star-rating-input label {
    color: #444;
    transition: all 0.2s ease-in-out;
}

.star-rating-input input:checked ~ label,
.star-rating-input label:hover,
.star-rating-input label:hover ~ label {
    color: #ffc107 !important;
}

.text-muted-dark { color: #555; }
.transition-all { transition: all 0.3s ease; }
.ls-1 { letter-spacing: 1px; }
.ls-2 { letter-spacing: 2px; }

.review-card {
    transition: transform 0.3s ease;
}
.review-card:hover {
    transform: translateY(-5px);
}

.avatar {
    border: 2px solid rgba(255,255,255,0.1);
}

.cursor-pointer { cursor: pointer; }

/* ===== Pemilih Variasi (ala Shopee) ===== */
.variant-btn {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.25);
    color: rgba(0,0,0,0.8);
    font-size: 0.85rem;
    font-weight: 600;
    padding: 8px 22px;
    border-radius: 999px;
    transition: all 0.15s;
    cursor: pointer;
}
.variant-btn:hover { border-color: #000; }
.variant-btn.active {
    border: 2px solid #000;
    padding: 7px 21px; /* seimbangkan border ekstra */
    color: #000;
    font-weight: 700;
}
.variant-btn.disabled-opt {
    opacity: 0.35;
    cursor: not-allowed;
    text-decoration: line-through;
    background: #f5f5f5;
}
.variant-btn .vstock { font-size: 0.65rem; font-weight: 400; display: block; letter-spacing: 0.5px; }
</style>

<script>
    // Tracking View Produk (AI Implicit Signal)
    document.addEventListener('DOMContentLoaded', function() {
        fetch('http://127.0.0.1:8000/track/view', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: <?= $this->session->userdata('user_id') ?? 0 ?>,
                product_id: <?= $product->id ?>,
                session_id: "<?= session_id() ?>"
            }),
            keepalive: true
        }).catch(err => console.log('AI View Tracking Offline'));
    });

// ===== Logika Pemilih Variasi (ala Shopee) =====
document.addEventListener('DOMContentLoaded', function() {
    const variants = <?= json_encode(array_map(function($v) {
        return [
            'id'          => (int)$v->id,
            'color'       => $v->color,
            'size'        => $v->size,
            'stock'       => (int)$v->stock,
            'price_delta' => (float)$v->price_delta,
        ];
    }, isset($variants) ? $variants : [])) ?>;

    const tierNames = {
        n1: <?= json_encode($product->variant_name1 ?: 'Warna') ?>,
        n2: <?= json_encode($product->variant_name2 ?: 'Ukuran') ?>
    };

    const basePrice   = <?= (float)$product->price ?>;
    const productStock = <?= (int)$product->stock ?>;
    const hasVariants = variants.length > 1 || (variants.length === 1 && (variants[0].color !== 'Standar' || variants[0].size !== 'Standar'));
    if (!hasVariants) return;

    const colorGroup   = document.getElementById('colorGroup');
    const sizeGroup    = document.getElementById('sizeGroup');
    const sizeSection  = document.getElementById('sizeSection');
    const variantInput = document.getElementById('variantIdInput');
    const qtyInput     = document.getElementById('qtyInput');
    const stockInfo    = document.getElementById('stockInfo');
    const priceDisplay = document.getElementById('priceDisplay');
    const addBtn       = document.getElementById('addBtn');
    const noteInput    = document.getElementById('noteInput');
    const noteCounter  = document.getElementById('noteCounter');
    const summaryEl    = document.getElementById('variantSummary');

    // Label dinamis sesuai nama variasi produk
    document.getElementById('label1').textContent = 'Pilih ' + tierNames.n1;
    document.getElementById('label2').textContent = 'Pilih ' + tierNames.n2;

    // Produk tingkat kedua hanya tampil bila ada nilai selain Standar
    const hasTier2 = variants.some(v => v.size !== 'Standar');

    const fmt = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
    const totalVariantStock = variants.reduce((s, v) => s + v.stock, 0);
    let selectedColor = null;

    const colors = [...new Set(variants.map(v => v.color))];

    function updateSummary(c, s) {
        if (!summaryEl) return;
        summaryEl.style.display = 'block';
        summaryEl.innerHTML = 'Variasi dipilih: <span class="fw-bold text-dark">' + c + (s ? ' / ' + s : '') + '</span>';
    }

    function renderColors() {
        colorGroup.innerHTML = '';
        colors.forEach(color => {
            const totalStock = variants.filter(v => v.color === color).reduce((s, v) => s + v.stock, 0);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'variant-btn' + (totalStock === 0 ? ' disabled-opt' : '');
            btn.textContent = color;
            btn.dataset.value = color;
            if (totalStock === 0) btn.disabled = true;
            btn.addEventListener('click', () => selectColor(color));
            colorGroup.appendChild(btn);
        });
    }

    function renderSizes(color) {
        sizeGroup.innerHTML = '';
        variants.filter(v => v.color === color).forEach(v => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'variant-btn text-center';
            btn.innerHTML = v.size + '<span class="vstock">' + (v.stock > 0 ? v.stock + ' stok' : 'habis') + '</span>';
            if (v.stock === 0) {
                btn.classList.add('disabled-opt');
                btn.disabled = true;
            } else {
                btn.addEventListener('click', () => selectSize(v, btn));
            }
            sizeGroup.appendChild(btn);
        });
    }

    function applyVariant(v) {
        variantInput.value = v.id;
        priceDisplay.textContent = fmt(basePrice + v.price_delta);
        qtyInput.max = Math.max(v.stock, 1);
        if (parseInt(qtyInput.value) > v.stock) qtyInput.value = v.stock || 1;
        stockInfo.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Stok: <strong>' + v.stock + '</strong> buah';
        addBtn.disabled = v.stock === 0;
    }

    function selectColor(color) {
        selectedColor = color;
        [...colorGroup.children].forEach(b => {
            b.classList.toggle('active', b.dataset.value === color);
        });

        const list = variants.filter(v => v.color === color);

        if (!hasTier2) {
            // Produk 1 tingkat: pilih varian langsung
            const pick = list.find(v => v.stock > 0) || list[0];
            applyVariant(pick);
            updateSummary(color, null);
            return;
        }

        renderSizes(color);
        sizeSection.style.display = 'block';
        resetSelection();
        updateSummary(color, null);
    }

    function selectSize(v, btn) {
        [...sizeGroup.children].forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyVariant(v);
        updateSummary(selectedColor, v.size);
    }

    function resetSelection() {
        variantInput.value = '';
        priceDisplay.textContent = fmt(basePrice);
        qtyInput.max = productStock;
        qtyInput.value = 1;
        stockInfo.innerHTML = '<i class="fas fa-info-circle text-muted me-1"></i> Stok: <strong>' + totalVariantStock + '</strong> buah tersedia';
        addBtn.disabled = false;
    }

    // Validasi sebelum submit: varian wajib dipilih
    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        if (!variantInput.value) {
            e.preventDefault();
            alert('Silakan pilih ' + tierNames.n1.toLowerCase() +
                  (hasTier2 ? ' dan ' + tierNames.n2.toLowerCase() : '') + ' terlebih dahulu.');
        }
    });

    // Penghitung karakter catatan
    if (noteInput && noteCounter) {
        noteInput.addEventListener('input', () => noteCounter.textContent = noteInput.value.length);
    }

    // Penghitung karakter teks custom (Revisi #4)
    const customInput   = document.querySelector('textarea[name="custom_text"]');
    const customCounter = document.getElementById('customCounter');
    if (customInput && customCounter) {
        customInput.addEventListener('input', () => customCounter.textContent = customInput.value.length);
    }

    // Auto-pilih jika hanya ada satu kombinasi tersedia
    resetSelection();
    renderColors();
    if (colors.length === 1) {
        selectColor(colors[0]);
        if (hasTier2) {
            const available = variants.filter(v => v.color === colors[0] && v.stock > 0);
            if (available.length === 1) {
                const onlyBtn = sizeGroup.querySelector('.variant-btn:not(.disabled-opt)');
                if (onlyBtn) onlyBtn.click();
            }
        }
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.btn-filter-jidoor');
    const reviewCards = document.querySelectorAll('.review-card');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    
    let itemsToShow = 5;
    let currentFilter = 'all';
    let currentFilterVal = null;

    function updateVisibility() {
        let visibleCount = 0;
        let totalMatched = 0;

        reviewCards.forEach(card => {
            const cardRating = card.getAttribute('data-rating');
            const hasComment = card.getAttribute('data-has-comment') === 'true';
            
            let matchesFilter = false;
            if (currentFilter === 'all') {
                matchesFilter = true;
            } else if (currentFilter === 'rating') {
                matchesFilter = (cardRating === currentFilterVal);
            } else if (currentFilter === 'comment') {
                matchesFilter = hasComment;
            }
            
            if (matchesFilter) {
                totalMatched++;
                if (visibleCount < itemsToShow) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle Load More Button
        if (loadMoreContainer) {
            if (visibleCount < totalMatched) {
                loadMoreContainer.classList.remove('d-none');
            } else {
                loadMoreContainer.classList.add('d-none');
            }
        }
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update Active State
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            currentFilter = this.getAttribute('data-filter-type');
            currentFilterVal = this.getAttribute('data-val');
            itemsToShow = 5; // Reset limit on filter change
            
            updateVisibility();
        });
    });

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            itemsToShow += 5;
            updateVisibility();
        });
    }

    // Initial run
    updateVisibility();
});
</script>
