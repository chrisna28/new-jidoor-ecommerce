<?php
$uri      = uri_string();
$is_auth  = in_array($uri, ['login', 'register', 'lupa-password', 'auth/login', 'auth/register'])
          || strpos($uri, 'reset-password/') === 0;
?>

<?php if (!$is_auth): ?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-5 pb-5">
            <div class="col-lg-4">
                <div class="foot-brand mb-3">JiDoor</div>
                <p style="font-size:.9rem; line-height:1.7; max-width:300px;">Menghadirkan busana premium dengan sentuhan minimalis dan kualitas jahitan terbaik untuk menemani gaya harian Anda.</p>
                <p class="mt-4 mb-0" style="font-size:.82rem; color:rgba(247,244,239,.55);">Senin–Sabtu, 09.00–17.00 WIB</p>
            </div>

            <div class="col-lg-2 col-6">
                <h6>Akun</h6>
                <ul class="list-unstyled" id="tentang">
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
                <p style="font-size:.88rem;">Butuh desain atau produksi massal? Tim kami siap membantu lewat chat — dari pemilihan bahan sampai estimasi jadwal.</p>
                <a href="<?= base_url('chat') ?>" class="btn-line btn-sm2" style="color:#f7f4ef; border-color:rgba(247,244,239,.35);">Mulai konsultasi</a>
            </div>
        </div>

        <div class="foot-bottom d-flex justify-content-between flex-wrap gap-2">
            <span>&copy; <?= date('Y') ?> JiDoor Store. Hak cipta dilindungi.</span>
            <span>Dibuat dengan teliti di Indonesia.</span>
        </div>
    </div>
</footer>
<?php endif; ?>

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
