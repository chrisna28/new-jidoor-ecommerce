<div class="container pt-4 pb-sect">
    <nav class="crumb2" aria-label="breadcrumb" data-reveal>
        <a href="<?= base_url() ?>">Beranda</a>
        <span class="sep">/</span>
        <a href="<?= base_url('katalog') ?>">Katalog</a>
        <span class="sep">/</span>
        <strong><?= htmlspecialchars($product->category_name) ?></strong>
    </nav>

    <div class="row g-5 mt-1">
        <!-- Galeri -->
        <div class="col-lg-7">
            <div class="gallery-main" data-reveal>
                <img src="<?= $product->image && $product->image !== 'default.jpg' ? base_url('uploads/products/' . $product->image) : 'https://placehold.co/900x990/f6f2ea/1a1511?text=' . urlencode($product->name) ?>" alt="<?= htmlspecialchars($product->name) ?>">
            </div>

            <div class="mt-5" data-reveal>
                <span class="eyebrow">Deskripsi</span>
                <div class="lh-lg mt-3" style="color:var(--ink-soft); font-weight:300; font-size:1.02rem;">
                    <?= nl2br(htmlspecialchars($product->description)) ?>
                </div>
            </div>
        </div>

        <!-- Buy Box -->
        <div class="col-lg-5">
            <div class="buybox">
                <div class="buybox-card card-soft" data-reveal style="--rd:.12s;">
                    <span class="pcard-cat"><?= htmlspecialchars($product->category_name) ?></span>
                    <h1 class="buybox-title mb-3 mt-1"><?= htmlspecialchars($product->name) ?></h1>

                    <div class="d-flex align-items-baseline gap-3 mb-2">
                        <div class="buybox-price tnum" id="priceDisplay">Rp <?= number_format($product->price, 0, ',', '.') ?></div>
                        <div class="buybox-sold"><?= $product->total_sold ?> terjual</div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-1">
                        <button type="button" onclick="toggleLike(<?= $product->id ?>, this)" class="pcard-like" style="font-size:.85rem;" data-like-id="<?= $product->id ?>">
                            <i class="<?= $is_liked ? 'fas text-danger' : 'far' ?> fa-heart"></i><span class="like-count"><?= (int)$like_count ?></span> suka
                        </button>
                        <span class="pcard-like" style="font-size:.85rem; cursor:default;"><i class="far fa-comment"></i><?= (int)$comment_count ?> komentar</span>
                    </div>

                    <hr style="border-color:var(--line); opacity:1;" class="my-4">

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
                        <div class="mb-3">
                            <label class="fx-field mb-2 d-block fw-semibold" id="label1" style="font-size:.82rem;">Pilih <?= htmlspecialchars($product->variant_name1 ?: 'Warna') ?></label>
                            <div class="d-flex flex-wrap gap-2" id="colorGroup"></div>
                        </div>

                        <div class="mb-3" id="sizeSection" style="display:none;">
                            <label class="fx-field mb-2 d-block fw-semibold" id="label2" style="font-size:.82rem;">Pilih <?= htmlspecialchars($product->variant_name2 ?: 'Ukuran') ?></label>
                            <div class="d-flex flex-wrap gap-2" id="sizeGroup"></div>
                        </div>

                        <?php if (!empty($rec_variant)): ?>
                        <div class="ai-var-hint mb-3" id="aiVariantHint">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Saran untukmu: <strong><?= htmlspecialchars(trim(($rec_variant['color'] ?? '') . ' · ' . ($rec_variant['size'] ?? ''), ' ·')) ?></strong> — cocok dengan aktivitas belanjamu</span>
                            <button type="button" class="ai-var-apply" id="aiApplyBtn">Pakai saran</button>
                        </div>
                        <?php endif; ?>

                        <div class="small mb-4" id="variantSummary" style="display:none; color:var(--muted);"></div>
                        <?php endif; ?>

                        <?php if (!empty($product->is_custom)): ?>
                        <!-- Permintaan Custom (Revisi #4) -->
                        <div class="fx-field">
                            <label><i class="fas fa-wand-magic-sparkles me-1" style="color:var(--accent);"></i> Permintaan custom</label>
                            <textarea name="custom_text" class="fx-input" rows="3" maxlength="500" placeholder="Tuliskan desain atau permintaan khusus Anda..."></textarea>
                            <div class="fx-hint"><span id="customCounter">0</span>/500 karakter · Gambar referensi dapat diunggah saat checkout.</div>
                        </div>
                        <?php endif; ?>

                        <?php if ($has_real_variants): ?>
                        <div class="fx-field">
                            <label>Catatan (opsional)</label>
                            <textarea name="note" id="noteInput" class="fx-input" rows="2" maxlength="200" placeholder="Contoh: minta ukiran huruf awalan nama..."></textarea>
                            <div class="fx-hint text-end"><span id="noteCounter">0</span>/200 karakter</div>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <span class="fw-semibold" style="font-size:.82rem;">Jumlah</span>
                            <div class="qty-stepper">
                                <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()" aria-label="Kurangi">−</button>
                                <input type="number" name="qty" id="qtyInput" value="1" min="1" max="<?= $product->stock ?>">
                                <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()" aria-label="Tambah">+</button>
                            </div>
                        </div>

                        <button type="submit" class="btn-ink btn-block2 mb-3" id="addBtn">
                            Tambah ke tas
                        </button>

                        <div class="mb-2">
                            <button type="button" onclick="chatAboutProduct()" class="btn-line w-100">
                                <i class="far fa-comment-dots"></i> Chat penjual
                            </button>
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

                        <div class="mt-3 small" id="stockInfo" style="color:var(--muted);">
                            <i class="fas fa-circle-check me-1" style="color:var(--ok);"></i> Stok tersedia: <?= $product->stock ?> unit
                        </div>
                    </form>
                </div>

                <div class="trust-row">
                    <div class="trust-item"><i class="fas fa-shield-halved"></i> Garansi resmi 3 tahun</div>
                    <div class="trust-item"><i class="fas fa-scissors"></i> Jahitan premium</div>
                    <div class="trust-item"><i class="fas fa-truck-fast"></i> Kirim se-Indonesia</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ulasan -->
    <div class="sect">
        <div class="sec-head">
            <div data-reveal>
                <span class="sec-num">Penilaian pembeli</span>
                <h2>Ulasan produk</h2>
            </div>
        </div>

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

        <div class="review-sum card-soft mb-4" data-reveal>
            <div class="text-center px-md-3">
                <div class="review-score tnum"><?= $avg_rev ?></div>
                <div class="stars my-2">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <i class="fa<?= $i <= round($avg_rev) ? 's' : 'r' ?> fa-star<?= $i > round($avg_rev) ? ' off' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <small style="color:var(--muted);">dari 5 · <?= $total_rev ?> ulasan</small>
            </div>
            <div class="flex-grow-1">
                <div class="chipbar" id="ratingFilterGroup">
                    <button type="button" class="btn-filter-jidoor chip-f active" data-filter-type="all">Semua</button>
                    <?php for($i=5; $i>=1; $i--): ?>
                        <button type="button" class="btn-filter-jidoor chip-f" data-filter-type="rating" data-val="<?= $i ?>"><?= $i ?> bintang (<?= $dist[$i] ?>)</button>
                    <?php endfor; ?>
                    <button type="button" class="btn-filter-jidoor chip-f" data-filter-type="comment">Dengan komentar (<?= $with_comment ?>)</button>
                </div>
            </div>
        </div>

        <div class="review-list">
            <?php if (empty($reviews)): ?>
                <div class="empty2">
                    <div class="ico"><i class="fa-solid fa-comment-dots"></i></div>
                    <h3>Belum ada ulasan</h3>
                    <p>Jadilah yang pertama memberi penilaian untuk produk ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-card2 card-soft" data-rating="<?= $rev->rating ?>" data-has-comment="<?= !empty($rev->review) ? 'true' : 'false' ?>">
                        <div class="review-head2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar2"><?= strtoupper(substr($rev->username, 0, 2)) ?></div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.92rem;"><?= htmlspecialchars($rev->username) ?></div>
                                    <div style="color:var(--muted); font-size:.75rem;"><?= tanggal_indo(strtotime($rev->created_at)) ?></div>
                                </div>
                            </div>
                            <div class="stars">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa<?= $i <= $rev->rating ? 's' : 'r off' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="lh-base" style="font-size:.92rem; color:var(--ink-soft);">
                            <?= nl2br(htmlspecialchars($rev->review)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="text-center mt-4 <?= count($reviews) <= 5 ? 'd-none' : '' ?>" id="loadMoreContainer">
                    <button type="button" class="btn-line" id="loadMoreBtn">
                        Muat lebih banyak <i class="fas fa-chevron-down" style="font-size:.72rem;"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Form Ulasan -->
            <div class="card-soft p-4 p-md-5 mt-5" data-reveal>
                <h5 class="fw-semibold mb-4 d-flex align-items-center" style="font-family:'Playfair Display',Georgia,serif; font-weight:500 !important; font-size:1.35rem;">
                    <i class="fa-solid fa-pen-to-square me-3" style="color:var(--accent);"></i> Berikan ulasan Anda
                </h5>
                <?php if (!$this->session->userdata('user_id')): ?>
                    <div class="text-center py-3">
                        <p class="mb-4" style="color:var(--muted); font-size:.9rem;">Silakan login untuk memberikan rating dan komentar pada produk ini.</p>
                        <a href="<?= base_url('login') ?>" class="btn-ink">Login sekarang</a>
                    </div>
                <?php else: ?>
                    <form action="<?= base_url('welcome/rate') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product->id ?>">

                        <div class="fx-field">
                            <label>Rating Anda</label>
                            <div class="star-rating-input d-flex flex-row-reverse justify-content-end gap-2 fs-3">
                                <?php for($i=5; $i>=1; $i--): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" class="d-none" <?= $i==5?'required':'' ?> />
                                    <label for="star<?= $i ?>" class="fa-solid fa-star cursor-pointer transition-all" title="<?= $i ?> Bintang"></label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="fx-field">
                            <label>Komentar</label>
                            <textarea name="review" class="fx-input" rows="4" placeholder="Apa pendapat Anda tentang produk ini?" required></textarea>
                        </div>

                        <button type="submit" class="btn-ink mt-2">
                            Kirim ulasan <i class="fa-solid fa-paper-plane" style="font-size:.74rem;"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Produk Terkait -->
<div class="container sect pb-sect">
    <div class="sec-head">
        <div data-reveal>
            <span class="sec-num">Lanjutkan penjelajahan</span>
            <h2>Produk terkait</h2>
        </div>
        <a href="<?= base_url('katalog') ?>" class="sec-link d-none d-md-inline-flex" data-reveal style="--rd:.1s;">Lihat koleksi <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="row g-4">
        <?php if (!empty($related)): foreach ($related as $p): ?>
            <div class="col-6 col-md-3" data-reveal>
                <?php
                    $is_similar = isset($similar_ids) && in_array($p->id, $similar_ids);
                    $this->load->view('frontend/components/product_card', [
                        'p' => $p,
                        'badge_text' => $is_similar ? 'SERUPA' : null,
                        'recommended_ids' => isset($recommended_ids) ? $recommended_ids : [],
                        'rec_origins' => isset($rec_origins) ? $rec_origins : []
                    ]);
                ?>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

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
    const recVariant  = <?= json_encode(isset($rec_variant) && !empty($rec_variant) ? ['color' => $rec_variant['color'] ?? null, 'size' => $rec_variant['size'] ?? null] : NULL) ?>;
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
        summaryEl.innerHTML = 'Variasi dipilih: <strong style="color:var(--ink);">' + c + (s ? ' / ' + s : '') + '</strong>';
    }

    function renderColors() {
        colorGroup.innerHTML = '';
        colors.forEach(color => {
            const totalStock = variants.filter(v => v.color === color).reduce((s, v) => s + v.stock, 0);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'variant-btn' + (totalStock === 0 ? ' disabled-opt' : '');
            btn.innerHTML = color;
            if (recVariant && recVariant.color && color === recVariant.color && totalStock > 0) {
                btn.classList.add('ai-suggest');
                btn.innerHTML = color + '<i class="fa-solid fa-wand-magic-sparkles ai-mark" title="Cocok dengan aktivitasmu"></i>';
            }
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
            let inner = v.size + '<span class="vstock">' + (v.stock > 0 ? v.stock + ' stok' : 'habis') + '</span>';
            if (recVariant && recVariant.size && v.size === recVariant.size && v.stock > 0) {
                btn.classList.add('ai-suggest');
                inner += '<i class="fa-solid fa-wand-magic-sparkles ai-mark" title="Cocok dengan aktivitasmu"></i>';
            }
            btn.innerHTML = inner;
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
        stockInfo.innerHTML = '<i class="fas fa-circle-check me-1" style="color:var(--ok);"></i> Stok: <strong>' + v.stock + '</strong> buah';
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
        stockInfo.innerHTML = '<i class="fas fa-circle-info me-1"></i> Stok: <strong>' + totalVariantStock + '</strong> buah tersedia';
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

    // Tombol "Pakai saran" — terapkan varian rekomendasi AI
    const aiApplyBtn = document.getElementById('aiApplyBtn');
    if (aiApplyBtn && recVariant && recVariant.color) {
        aiApplyBtn.addEventListener('click', () => {
            const target = [...colorGroup.children].find(b => b.dataset.value === recVariant.color && !b.disabled);
            if (!target) { aiApplyBtn.closest('.ai-var-hint').style.display = 'none'; return; }
            target.click();
            if (hasTier2 && recVariant.size) {
                const sizeBtn = [...sizeGroup.children].find(b => !b.disabled && b.textContent.includes(recVariant.size));
                if (sizeBtn) sizeBtn.click();
            }
            aiApplyBtn.textContent = 'Saran diterapkan';
            aiApplyBtn.disabled = true;
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.btn-filter-jidoor');
    const reviewCards = document.querySelectorAll('.review-card2');
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
