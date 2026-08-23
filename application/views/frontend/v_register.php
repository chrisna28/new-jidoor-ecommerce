<div class="a2-shell a2-shell-flip">
    <!-- Panel editorial (kanan) -->
    <aside class="a2-panel d-none d-lg-flex">
        <div class="a2-photo" style="background-image: url('https://images.unsplash.com/photo-1506377295352-e3154d43ea9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');"></div>
        <div class="a2-scrim"></div>
        <div class="a2-grain"></div>

        <a href="<?= base_url() ?>" class="a2-brand">
            <i class="fa-solid fa-door-open"></i> JiDoor
        </a>

        <div class="a2-panel-body">
            <span class="a2-eyebrow">JiDoor Store</span>
            <h1 class="a2-display">Satu akun,<br><em>semua pesanan.</em></h1>
            <p class="a2-lede">Lacak pengiriman, simpan produk favorit, dan checkout lebih cepat di kunjungan berikutnya.</p>
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
                <h1>Buat <em>akun</em> Anda.</h1>
                <p>Cukup semenit saja.</p>
            </header>

            <form action="<?= base_url('auth/register_aksi') ?>" method="post" class="a2-form" data-auth-form>
                <?= csrf_field() ?>
                <div class="a2-row">
                    <div class="a2-field a2-rise">
                        <label for="username">Nama pengguna</label>
                        <div class="a2-control">
                            <input type="text" id="username" name="username" class="a2-input" placeholder="nama pengguna Anda" autocomplete="username" required>
                        </div>
                    </div>
                    <div class="a2-field a2-rise">
                        <label for="email">Email</label>
                        <div class="a2-control">
                            <input type="email" id="email" name="email" class="a2-input" placeholder="anda@contoh.com" autocomplete="email" required>
                        </div>
                    </div>
                </div>

                <div class="a2-field a2-rise">
                    <label for="password">Kata sandi</label>
                    <div class="a2-control a2-pass">
                        <input type="password" id="password" name="password" class="a2-input" placeholder="minimal 6 karakter" autocomplete="new-password" minlength="6" required>
                        <button type="button" class="a2-eye" data-toggle-pass="#password" aria-label="Tampilkan kata sandi">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="a2-field a2-rise">
                    <label for="confirm_password">Konfirmasi kata sandi</label>
                    <div class="a2-control a2-pass">
                        <input type="password" id="confirm_password" name="confirm_password" class="a2-input" placeholder="ulangi kata sandi Anda" autocomplete="new-password" minlength="6" required>
                        <button type="button" class="a2-eye" data-toggle-pass="#confirm_password" aria-label="Tampilkan kata sandi">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="a2-field a2-rise">
                    <label for="phone">No. HP <span class="a2-optional">(opsional)</span></label>
                    <div class="a2-control">
                        <input type="text" id="phone" name="phone" class="a2-input" placeholder="08xx xxxx xxxx" autocomplete="tel">
                    </div>
                </div>

                <button type="submit" class="a2-btn a2-rise" data-auth-btn>
                    <span>Buat Akun</span><i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <p class="a2-alt a2-rise">Sudah punya akun? <a href="<?= base_url('login') ?>">Masuk</a></p>
        </div>
    </main>
</div>

<script>
document.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.querySelector(btn.getAttribute('data-toggle-pass'));
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = '<i class="fa-' + (show ? 'solid' : 'regular') + ' fa-eye' + (show ? '-slash' : '') + '"></i>';
        btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
});
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
