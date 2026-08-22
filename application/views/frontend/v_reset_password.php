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
                <h2 class="fw-bold mb-2 ls-1" style="font-size: 2.5rem;">RESET PASSWORD</h2>
                <p class="text-muted small ls-1">AKUN: <?= htmlspecialchars($masked_email) ?>. BUAT PASSWORD BARU ANDA.</p>
            </div>

            <form action="<?= base_url('reset-password/' . $token) ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <label class="small fw-bold text-uppercase ls-1">Password Baru</label>
                    <input type="password" name="password" class="form-control-mixtas w-100" placeholder="MINIMAL 6 KARAKTER" minlength="6" required autofocus>
                </div>

                <div class="mb-5">
                    <label class="small fw-bold text-uppercase ls-1">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control-mixtas w-100" placeholder="ULANGI PASSWORD BARU" minlength="6" required>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 mb-4">
                    SIMPAN PASSWORD BARU
                </button>

                <div class="text-center">
                    <p class="text-muted small ls-1"><a href="<?= base_url('login') ?>" class="text-dark fw-bold text-decoration-none">BACK TO LOGIN</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
