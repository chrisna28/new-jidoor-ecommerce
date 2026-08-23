<div class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="crumb2"><span>Beranda</span> / Produk yang Kamu Sukai</nav>
        <h1 class="page-title">Produk yang Kamu Sukai</h1>
        <p class="fx-hint mt-1 mb-0">Kumpulan produk yang kamu tandai dengan hati.</p>
    </div>
</div>

<section class="sect pt-4">
    <div class="container">
        <?php if (empty($liked)): ?>
            <div class="empty2">
                <i class="far fa-heart"></i>
                <h3>Belum ada yang kamu sukai</h3>
                <p>Tandai produk dengan ikon hati untuk menyimpannya di sini.</p>
                <a href="<?= base_url('katalog') ?>" class="btn-ink">Jelajahi katalog</a>
            </div>
        <?php else: ?>
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4" id="likedGrid">
                <?php foreach ($liked as $w): ?>
                    <div class="col">
                        <?php
                        $p = (object) [
                            'id'            => $w->product_id,
                            'name'          => $w->name,
                            'slug'          => $w->slug,
                            'price'         => $w->price,
                            'image'         => $w->image,
                            'category_name' => $w->category_name ?? 'Produk',
                            'like_count'    => (int)($w->like_count ?? 0),
                            'comment_count' => (int)($w->comment_count ?? 0),
                            'is_liked'      => true
                        ];
                        echo $this->load->view('frontend/components/product_card', ['p' => $p, 'no_badge' => true], true);
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('[data-like-id]');
                    if (!btn || !document.getElementById('likedGrid')) return;
                    var card = btn.closest('.col');
                    if (!card) return;
                    setTimeout(function() {
                        var icon = btn.querySelector('i');
                        if (icon && !icon.classList.contains('fas')) {
                            card.style.transition = 'opacity .35s ease, transform .35s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(.96)';
                            setTimeout(function() {
                                card.remove();
                                if (!document.querySelector('#likedGrid .pcard')) location.reload();
                            }, 360);
                        }
                    }, 700);
                });
            </script>
        <?php endif; ?>
    </div>
</section>
