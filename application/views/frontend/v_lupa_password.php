<div class="a2-shell">
    <!-- Panel editorial -->
    <aside class="a2-panel d-none d-lg-flex">
        <div class="a2-photo" style="background-image: url('https://images.unsplash.com/photo-1510074377623-8cf13fb86c08?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');"></div>
        <div class="a2-scrim"></div>
        <div class="a2-grain"></div>

        <a href="<?= base_url() ?>" class="a2-brand">
            <i class="fa-solid fa-door-open"></i> JiDoor
        </a>

        <div class="a2-panel-body">
            <span class="a2-eyebrow">JiDoor Store</span>
            <h1 class="a2-display">Dijahit untuk dipakai,<br><em>dibuat untuk bertahan.</em></h1>
            <p class="a2-lede">Busana kelas seragam dari bahan terbaik &mdash; tangguh untuk bekerja, nyaman dipakai di mana saja.</p>
            <a href="<?= base_url('katalog') ?>" class="a2-panel-link">Jelajahi koleksi <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </aside>

    <!-- Kolom formulir -->
    <main class="a2-main">
        <div class="a2-card">
            <div class="a2-topbar">
                <a href="<?= base_url() ?>" class="a2-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke toko</a>
                <a href="<?= base_url() ?>" class="a2-brand a2-brand-sm d-lg-none"><i class="fa-solid fa-door-open"></i> JiDoor</a>
            </div>

            <header class="a2-head a2-rise">
                <h1>Atur ulang <em>kata sandi</em> Anda.</h1>
                <p>Masukkan email akun Anda dan kami akan mengirimkan tautan reset.</p>
            </header>

            <form action="<?= base_url('lupa-password') ?>" method="post" class="a2-form" data-auth-form>
                <?= csrf_field() ?>
                <div class="a2-field a2-rise">
                    <label for="email">Email</label>
                    <div class="a2-control">
                        <input type="email" id="email" name="email" class="a2-input" placeholder="anda@contoh.com" autocomplete="email" required autofocus>
                    </div>
                </div>

                <button type="submit" class="a2-btn a2-rise" data-auth-btn>
                    <span>Kirim Tautan Reset</span><i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <p class="a2-alt a2-rise">Sudah ingat? <a href="<?= base_url('login') ?>">Kembali ke halaman masuk</a></p>
        </div>
    </main>
</div>

<script>
document.querySelectorAll('[data-auth-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
        var btn = form.querySelector('[data-auth-btn]');
        if (!btn || btn.dataset.loading) { return; }
        btn.dataset.loading = '1';
        btn.disabled = true;
        var label = btn.querySelector('span');
        if (label) { label.textContent = 'Mohon tunggu'; }
    });
});
</script>
