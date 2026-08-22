<div class="card jidoor-card" data-product-id="<?= $p->id ?>" <?= isset($badge_text) ? 'data-is-recommended="true"' : '' ?>>
    <a href="<?= base_url('produk/' . $p->slug) ?>" class="card-img-container position-relative text-decoration-none">
        <!-- Badge logic (Pure CF) -->
        <?php if (isset($no_badge) && $no_badge): ?>
            <!-- No badge -->
        <?php elseif (isset($badge_text)): ?>
            <?php 
                $bg_class = 'bg-primary';
                if (strpos($badge_text, 'BEST MATCH') !== false) $bg_class = 'bg-success text-white';
                if (strpos($badge_text, 'FOR YOU') !== false) $bg_class = 'bg-dark text-white';
                if (strpos($badge_text, 'STYLE MATCH') !== false) $bg_class = 'bg-info text-white';
                if (strpos($badge_text, 'HOT HITS') !== false) $bg_class = 'bg-danger text-white';
                if (strpos($badge_text, 'TRENDING') !== false) $bg_class = 'bg-warning text-dark';
                if (strpos($badge_text, 'BEST SELLER') !== false) $bg_class = 'bg-danger text-white';
                if (strpos($badge_text, 'New Arrival') !== false) $bg_class = 'bg-primary text-white';
                if (strpos($badge_text, 'SIMILAR') !== false) $bg_class = 'bg-purple text-white';
            ?>
            <div class="product-badge <?= $bg_class ?>" style="font-size: 0.6rem; padding: 4px 10px; border-radius: 4px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                <?= $badge_text ?>
            </div>
        <?php endif; ?>

        <img src="<?= $p->image && $p->image !== 'default.jpg' ? base_url('uploads/products/' . $p->image) : 'https://placehold.co/600x800/f5f5f5/000000?text=' . urlencode($p->name) ?>" alt="<?= htmlspecialchars($p->name) ?>">
        
        <!-- Wishlist button -->
        <button onclick="event.preventDefault(); toggleWishlist(<?= $p->id ?>, this)" class="wishlist-btn-overlay">
            <i class="<?= isset($user_wishlist_ids) && in_array($p->id, $user_wishlist_ids) ? 'fas text-danger' : 'far' ?> fa-heart"></i>
        </button>
    </a>
    
    <div class="card-body">
        <a href="<?= base_url('produk/' . $p->slug) ?>" class="product-name text-decoration-none"><?= htmlspecialchars($p->name) ?></a>
        <span class="product-cat"><?= htmlspecialchars($p->category_name) ?></span>
        <div class="product-price">Rp <?= number_format($p->price, 0, ',', '.') ?></div>
        <div class="d-flex align-items-center gap-3 mt-2 small text-muted">
            <button type="button" onclick="event.preventDefault(); toggleLike(<?= $p->id ?>, this)" class="btn btn-link p-0 m-0 text-decoration-none text-muted small like-toggle-btn">
                <i class="<?= !empty($p->is_liked) ? 'fas text-danger' : 'far' ?> fa-heart me-1"></i><span class="like-count"><?= (int)($p->like_count ?? 0) ?></span>
            </button>
            <span><i class="far fa-comment me-1"></i><?= (int)($p->comment_count ?? 0) ?></span>
        </div>
    </div>
</div>
