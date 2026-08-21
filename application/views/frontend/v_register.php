<div class="auth-wrapper flex-row-reverse">
    <!-- Right Side: Image -->
    <div class="auth-image d-none d-lg-flex" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1506377295352-e3154d43ea9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');">
        <div>
            <span class="text-uppercase ls-2 small mb-3 d-block">Join the Community</span>
            <h1 class="display-3 fw-bold mb-4">Create Your<br>Account.</h1>
            <p class="fs-5 opacity-75 mb-5 px-5">Join JiDoor to get exclusive updates and personalized door recommendations.</p>
        </div>
    </div>

    <!-- Left Side: Form -->
    <div class="auth-form-container bg-white">
        <div class="w-100" style="max-width: 500px; margin: 0 auto;">
            <div class="mb-5">
                <h2 class="fw-bold mb-2 ls-1" style="font-size: 2.5rem;">REGISTER</h2>
                <p class="text-muted small ls-1">PLEASE FILL IN YOUR INFORMATION BELOW.</p>
            </div>

            <form action="<?= base_url('auth/register_aksi') ?>" method="post">
                <?= csrf_field() ?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase ls-1">Username</label>
                            <input type="text" name="username" class="form-control-mixtas w-100" placeholder="USERNAME" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase ls-1">Email</label>
                            <input type="email" name="email" class="form-control-mixtas w-100" placeholder="EMAIL ADDRESS" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase ls-1">Password</label>
                            <input type="password" name="password" class="form-control-mixtas w-100" placeholder="CHOOSE A PASSWORD" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase ls-1">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control-mixtas w-100" placeholder="REPEAT YOUR PASSWORD" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-5">
                            <label class="small fw-bold text-uppercase ls-1">Phone Number</label>
                            <input type="text" name="phone" class="form-control-mixtas w-100" placeholder="YOUR PHONE NUMBER">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 mb-4">
                    REGISTER NOW
                </button>

                <div class="text-center">
                    <p class="text-muted small ls-1">ALREADY HAVE AN ACCOUNT? <a href="<?= base_url('login') ?>" class="text-dark fw-bold text-decoration-none">LOGIN HERE</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
