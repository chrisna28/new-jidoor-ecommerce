<?php 
$is_auth = in_array(uri_string(), ['login', 'register', 'auth/login', 'auth/register']);
?>

<?php if (!$is_auth): ?>
<footer class="py-5 border-top mt-5" style="background-color: #fff;">
    <div class="container py-4">
        <div class="row g-5">
            <div class="col-lg-4 col-md-12">
                <h2 class="fw-bold mb-4" style="letter-spacing: -1px;">JiDoor</h2>
                <p class="text-muted small lh-lg" style="max-width: 300px;">Providing premium door solutions with a touch of minimalist elegance and modern security technology for your timeless home.</p>
                <div class="d-flex gap-4 mt-4">
                    <a href="#" class="text-dark"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-dark"><i class="fab fa-pinterest"></i></a>
                    <a href="#" class="text-dark"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-6">
                <h6 class="fw-bold text-uppercase ls-2 mb-4" style="font-size: 0.75rem;">Information</h6>
                <ul class="list-unstyled">
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">About Us</a></li>
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">Contact Us</a></li>
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">Privacy Policy</a></li>
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">Terms & Conditions</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-6">
                <h6 class="fw-bold text-uppercase ls-2 mb-4" style="font-size: 0.75rem;">Shop</h6>
                <ul class="list-unstyled">
                    <li class="mb-3"><a href="<?= base_url('katalog') ?>" class="text-decoration-none text-muted small text-uppercase ls-1">All Products</a></li>
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">Best Sellers</a></li>
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">New Arrivals</a></li>
                    <li class="mb-3"><a href="#" class="text-decoration-none text-muted small text-uppercase ls-1">Promotion</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-12">
                <h6 class="fw-bold text-uppercase ls-2 mb-4" style="font-size: 0.75rem;">Newsletter</h6>
                <p class="text-muted small mb-4">Be the first to know about new collections and exclusive offers.</p>
                <div class="position-relative">
                    <input type="email" class="form-control-mixtas w-100" placeholder="YOUR EMAIL ADDRESS">
                    <button class="btn btn-link text-dark position-absolute end-0 top-0 fw-bold text-decoration-none p-0 mt-3 small ls-1">SUBSCRIBE</button>
                </div>
            </div>
        </div>
        
        <div class="pt-5 mt-5 border-top text-center">
            <p class="mb-0 small text-muted ls-1">&copy; <?= date('Y') ?> JIDOOR STORE. ALL RIGHTS RESERVED.</p>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Navbar Scroll Script -->
<script>
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNavbar');
        const isAuthOrHome = <?= (in_array(uri_string(), ['', 'login', 'register', 'auth/login', 'auth/register'])) ? 'true' : 'false' ?>;
        
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
            navbar.classList.remove('navbar-transparent');
        } else {
            navbar.classList.remove('navbar-scrolled');
            navbar.classList.add('navbar-transparent');
        }
    });

    // Initial check for non-home pages to ensure text is visible
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('mainNavbar');
        const isAuthOrHome = <?= (in_array(uri_string(), ['', 'login', 'register', 'auth/login', 'auth/register'])) ? 'true' : 'false' ?>;
        
        if (!isAuthOrHome) {
            navbar.classList.add('navbar-dark-text');
            navbar.classList.add('border-bottom');
        }
    });

    function toggleWishlist(productId, btn) {
        fetch('<?= base_url('welcome/toggle_wishlist/') ?>' + productId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    window.location.href = '<?= base_url('login') ?>';
                    return;
                }
                
                const icon = btn.querySelector('i');
                if (data.status === 'added') {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    icon.classList.add('text-danger');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    icon.classList.remove('text-danger');
                }
                
                location.reload();
            });
    }
</script>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
