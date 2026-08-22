<div class="auth-wrapper">
    <!-- Left Side: Minimalist Image -->
    <div class="auth-image d-none d-lg-flex" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1510074377623-8cf13fb86c08?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');">
        <div>
            <span class="text-uppercase ls-2 small mb-3 d-block">Premium Collection</span>
            <h1 class="display-3 fw-bold mb-4">Timeless<br>Elegance.</h1>
            <p class="fs-5 opacity-75 mb-5 px-5">Experience the ultimate combination of security and minimalist design.</p>
            <a href="<?= base_url() ?>" class="btn-discovery px-5">Explore More</a>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="auth-form-container bg-white">
        <div class="w-100" style="max-width: 450px; margin: 0 auto;">
            <div class="mb-5">
                <h2 class="fw-bold mb-2 ls-1" style="font-size: 2.5rem;">LUPA PASSWORD</h2>
                <p class="text-muted small ls-1">MASUKKAN EMAIL AKUN ANDA. KAMI AKAN MENGIRIMKAN TAUTAN RESET.</p>
            </div>

            <form action="<?= base_url('lupa-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="small fw-bold text-uppercase ls-1">Email</label>
                    <input type="email" name="email" class="form-control-mixtas w-100" placeholder="ENTER YOUR EMAIL" required autofocus>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 mb-4">
                    KIRIM TAUTAN RESET
                </button>

                <div class="text-center">
                    <p class="text-muted small ls-1">INGAT PASSWORD? <a href="<?= base_url('login') ?>" class="text-dark fw-bold text-decoration-none">BACK TO LOGIN</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
