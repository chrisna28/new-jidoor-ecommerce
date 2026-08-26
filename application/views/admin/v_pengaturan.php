<?php if ($s = $this->session->flashdata('success')): ?><div class="js-flash d-none" data-type="success" data-msg="<?= htmlspecialchars($s) ?>"></div><?php endif; ?>
<?php if ($e = $this->session->flashdata('error')): ?><div class="js-flash d-none" data-type="error" data-msg="<?= htmlspecialchars($e) ?>"></div><?php endif; ?>

<div class="page-head toolbar">
    <div>
        <h2 class="page-title">Pengaturan</h2>
        <p class="page-sub">Ubah konfigurasi (database, Midtrans, API rekomendasi) langsung dari browser — tersimpan ke file <code>.env</code>.</p>
    </div>
</div>

<!-- Status file .env -->
<div class="admin-card mb-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px;background:var(--accent-soft);color:var(--accent);"><i class="fa-solid fa-file-lines"></i></span>
        <div class="flex-grow-1 min-w-0">
            <div class="small text-truncate" style="font-family:monospace;"><?= htmlspecialchars($env_path) ?></div>
            <div class="small <?= $env_writable ? 'text-success' : 'text-danger' ?>">
                <i class="fa-solid <?= $env_writable ? 'fa-circle-check' : 'fa-circle-xmark' ?> me-1"></i>
                <?= $env_writable ? 'File .env dapat ditulis' : 'File .env TIDAK dapat ditulis — beri izin tulis ke folder proyek' ?>
            </div>
        </div>
        <span class="badge-neutral"><i class="fa-solid fa-shield-halved me-1"></i> backup otomatis ke .env.bak</span>
    </div>
</div>

<form action="<?= base_url('admin/pengaturan/simpan') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Database -->
    <div class="admin-card mb-4">
        <h5 class="fw-bold mb-1"><i class="fa-solid fa-database me-2" style="color:var(--accent);"></i>Database (MySQL)</h5>
        <p class="text-muted small mb-4">Kredensial koneksi database. Perubahan diuji koneksinya dulu sebelum disimpan.</p>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="small text-uppercase ls-1 d-block mb-2">Host</label>
                <input type="text" name="db_host" class="form-control-admin" value="<?= htmlspecialchars($env['DB_HOST'] ?? '127.0.0.1') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="small text-uppercase ls-1 d-block mb-2">Port</label>
                <input type="number" name="db_port" class="form-control-admin" value="<?= htmlspecialchars($env['DB_PORT'] ?? '3306') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="small text-uppercase ls-1 d-block mb-2">User</label>
                <input type="text" name="db_user" class="form-control-admin" value="<?= htmlspecialchars($env['DB_USER'] ?? 'root') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="small text-uppercase ls-1 d-block mb-2">Password</label>
                <input type="text" name="db_password" class="form-control-admin" value="<?= htmlspecialchars($env['DB_PASSWORD'] ?? '') ?>" placeholder="kosongkan bila tanpa password">
            </div>
            <div class="col-md-12">
                <label class="small text-uppercase ls-1 d-block mb-2">Nama Database</label>
                <input type="text" name="db_name" class="form-control-admin" value="<?= htmlspecialchars($env['DB_NAME'] ?? 'ecommerce_db') ?>" required>
            </div>
        </div>
        <div class="text-muted mt-3" style="font-size:.72rem;"><i class="fa-solid fa-circle-info me-1"></i>XAMPP: port <b>3306</b>, password <b>kosong</b> &nbsp;·&nbsp; MAMP: port <b>8889</b>, password <b>root</b></div>
    </div>

    <!-- Midtrans -->
    <div class="admin-card mb-4">
        <h5 class="fw-bold mb-1"><i class="fa-solid fa-credit-card me-2" style="color:var(--accent);"></i>Pembayaran Midtrans</h5>
        <p class="text-muted small mb-4">Kunci dari dashboard.sandbox.midtrans.com (uji) atau dashboard.midtrans.com (produksi).</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="small text-uppercase ls-1 d-block mb-2">Server Key</label>
                <input type="text" name="midtrans_server_key" class="form-control-admin" value="<?= htmlspecialchars($env['MIDTRANS_SERVER_KEY'] ?? '') ?>" placeholder="SB-MidServer-...">
            </div>
            <div class="col-md-6">
                <label class="small text-uppercase ls-1 d-block mb-2">Client Key</label>
                <input type="text" name="midtrans_client_key" class="form-control-admin" value="<?= htmlspecialchars($env['MIDTRANS_CLIENT_KEY'] ?? '') ?>" placeholder="SB-MidClient-...">
            </div>
            <div class="col-md-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="midtrans_production" id="midtransProd" value="1"
                        <?= in_array(strtolower((string)($env['MIDTRANS_IS_PRODUCTION'] ?? 'false')), ['1','true','yes'], TRUE) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="midtransProd">Mode produksi (non-sandbox)</label>
                </div>
            </div>
        </div>
    </div>

    <!-- API Rekomendasi -->
    <div class="admin-card mb-4">
        <h5 class="fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles me-2" style="color:var(--accent);"></i>API Rekomendasi (FastAPI)</h5>
        <p class="text-muted small mb-4">Alamat server Python mesin rekomendasi. Kosongkan untuk memakai default lokal.</p>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="small text-uppercase ls-1 d-block mb-2">Base URL</label>
                <input type="text" name="api_base_url" class="form-control-admin" value="<?= htmlspecialchars($env['PY_API_BASE_URL'] ?? 'http://127.0.0.1:8000') ?>" placeholder="http://127.0.0.1:8000">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-admin-primary px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Pengaturan</button>
        <a href="<?= base_url('admin') ?>" class="btn btn-admin-outline">Batal</a>
    </div>
</form>
