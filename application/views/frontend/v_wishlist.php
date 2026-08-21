<div class="container py-5 mt-5">
    <div class="text-center mb-5 mt-4">
        <h1 class="fw-bold display-4">My Wishlist</h1>
        <p class="text-muted">Simpan produk impian Anda dan beli kapan saja.</p>
    </div>

    <div class="row g-4">
        <?php if (!empty($wishlist)): foreach ($wishlist as $w): ?>
            <div class="col-6 col-md-4 col-lg-3 wishlist-item">
                <?php 
                    // Map wishlist object to standard product object structure
                    $p = (object) [
                        'id' => $w->product_id,
                        'name' => $w->name,
                        'slug' => $w->slug,
                        'price' => $w->price,
                        'image' => $w->image,
                        'category_name' => $w->category_name,
                        'avg_rating' => $w->avg_rating,
                        'total_sold' => $w->total_sold
                    ];
                    // Force heart to be solid since it's the wishlist view
                    $this->load->view('frontend/components/product_card', [
                        'p' => $p, 
                        'user_wishlist_ids' => [$p->id] 
                    ]); 
                ?>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5">
                <div class="mb-4 opacity-25">
                    <i class="far fa-heart fa-5x"></i>
                </div>
                <h3 class="fw-bold">Wishlist Anda Kosong</h3>
                <p class="text-muted">Telusuri katalog kami dan temukan produk favorit Anda.</p>
                <a href="<?= base_url('katalog') ?>" class="btn btn-dark px-5 py-3 mt-3 rounded-pill fw-bold">START SHOPPING</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleWishlist(productId, btn) {
    fetch('<?= base_url('welcome/toggle_wishlist/') ?>' + productId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'removed') {
                // Remove the item from the UI
                const item = btn.closest('.wishlist-item');
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    // Check if wishlist is now empty
                    if (document.querySelectorAll('.wishlist-item').length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        });
}
</script>
