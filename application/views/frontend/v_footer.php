<?php
$uri      = uri_string();
$is_auth  = in_array($uri, ['login', 'register', 'lupa-password', 'auth/login', 'auth/register']);
?>

<?php if (!$is_auth): ?>
<footer class="site-footer" id="tentang">
    <div class="container">
        <!-- CTA besar -->
        <div class="foot-cta">
            <h2 data-reveal>Punya desain sendiri? <em>Kami jahit.</em></h2>
            <a href="<?= base_url('chat') ?>" class="btn-ink" data-reveal style="--rd:.12s;">
                Mulai konsultasi <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-5">
            <div class="col-lg-4">
                <div class="foot-brand mb-3"><i class="fas fa-feather-pointed me-2"></i>JiDoor</div>
                <p style="max-width:320px;">Menghadirkan busana premium dengan sentuhan minimalis dan kualitas jahitan terbaik untuk menemani gaya harian Anda.</p>
                <p class="foot-hours mb-0 mt-3">Senin–Sabtu, 09.00–17.00 WIB</p>
            </div>

            <div class="col-lg-2 col-6">
                <h6>Akun</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= base_url('riwayat') ?>">Pesanan saya</a></li>
                    <li><a href="<?= base_url('disukai') ?>">Produk disukai</a></li>
                    <li><a href="<?= base_url('riwayat-rating') ?>">Ulasan saya</a></li>
                    <li><a href="<?= base_url('chat') ?>">Bantuan</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-6">
                <h6>Belanja</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= base_url('katalog') ?>">Semua produk</a></li>
                    <li><a href="<?= base_url('katalog') ?>">Koleksi baru</a></li>
                    <li><a href="<?= base_url('keranjang') ?>">Tas belanja</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h6>Layanan kustom</h6>
                <p>Butuh desain atau produksi massal? Tim kami siap membantu lewat chat — dari pemilihan bahan sampai estimasi jadwal.</p>
            </div>
        </div>

        <!-- Wordmark raksasa -->
        <div class="foot-mark" aria-hidden="true">JiDoor</div>

        <div class="foot-bottom d-flex justify-content-between flex-wrap gap-2">
            <span>&copy; <?= date('Y') ?> JiDoor Store. Hak cipta dilindungi.</span>
            <span>Dibuat dengan teliti di Indonesia.</span>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Scroll reveal engine — dijalankan setelah seluruh konten halaman tersedia -->
<script>
(function () {
    function initReveal() {
        var els = document.querySelectorAll('[data-reveal]:not(.is-in)');
        if (!els.length) return;
        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-in'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.05, rootMargin: '0px 0px -30px 0px' });
        els.forEach(function (el) { io.observe(el); });

        // Failsafe: apa pun yang masih tersembunyi setelah 2.5s, tampilkan.
        setTimeout(function () {
            document.querySelectorAll('[data-reveal]:not(.is-in)').forEach(function (el) {
                var r = el.getBoundingClientRect();
                if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('is-in');
            });
        }, 2500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReveal);
    } else {
        initReveal();
    }
})();
</script>

<!-- Navbar Script -->
<script>
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNavbar');
        if (!navbar) return;
        navbar.classList.toggle('is-scrolled', window.scrollY > 24);
    }, { passive: true });

    function toggleLike(productId, btn) {
        fetch('<?= base_url('welcome/like_toggle/') ?>' + productId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    window.location.href = '<?= base_url('login') ?>';
                    return;
                }

                document.querySelectorAll('[data-like-id="' + productId + '"]').forEach(el => {
                    const icon = el.querySelector('i');
                    const count = el.querySelector('.like-count');
                    if (count) count.textContent = data.like_count;

                    if (el.classList.contains('pcard-heart')) {
                        el.classList.toggle('on', data.liked);
                    } else {
                        el.classList.toggle('is-on', data.liked);
                    }

                    if (icon) {
                        if (data.liked) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            icon.classList.add('text-danger');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.remove('text-danger');
                            icon.classList.add('far');
                        }
                    }
                });
            });
    }
</script>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
