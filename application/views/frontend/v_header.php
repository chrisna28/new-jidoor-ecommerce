<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'JiDoor Store — Busana Premium Minimalis' ?></title>
    <meta name="description" content="JiDoor Store — konveksi & toko busana premium. Koleksi minimalis berbahan terbaik, jahitan rapi, produksi kustom.">
    <meta property="og:title" content="<?= isset($title) ? htmlspecialchars($title) : 'JiDoor Store — Busana Premium Minimalis' ?>">
    <meta property="og:description" content="Koleksi busana minimalis berbahan premium — jahitan rapi, potongan bersih.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= base_url('assets/images/hero.jpeg') ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/favicon.svg') ?>">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=2.3') ?>">
</head>
<?php
$uri      = uri_string();
$is_auth  = in_array($uri, ['login', 'register', 'lupa-password', 'auth/login', 'auth/register'])
          || strpos($uri, 'reset-password/') === 0;
$user_id = $this->session->userdata('user_id');
?>
<body class="<?= $is_auth ? 'a2-page' : 'store-front' ?>">

<?php
// Widget chat (Revisi #7) — hanya untuk customer yang login
$show_chat = false;
if ($user_id && $this->session->userdata('role') !== 'admin') {
    $this->load->library('chat_token');
    $chat_token   = Chat_Token::make($user_id, 'user');
    $chat_user_id = (int)$user_id;
    $show_chat    = true;
}
?>

<!-- Navbar -->
<?php if (!$is_auth): ?>
<?php
// Badge topbar dihitung terpusat di sini agar konsisten di semua halaman,
// tidak bergantung pada data yang dikirim tiap controller.
// Query langsung via $this->db karena model belum tentu sudah dimuat controller.
$cart_count = 0;
$like_count = 0;
$notif_count = 0;
if ($user_id && $this->session->userdata('role') !== 'admin') {
    $cart_count     = (int) $this->db->where('user_id', $user_id)->count_all_results('cart');
    $like_count = (int) $this->db->where('user_id', $user_id)->count_all_results('likes');
    $notif_count    = (int) $this->db->from('orders')->where('user_id', $user_id)
                        ->where_in('status', ['paid', 'shipped'])->count_all_results();
}
?>
<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-v2 sticky-top">
    <div class="container">
        <!-- Kiri: Logo -->
        <a class="brand-mark" href="<?= base_url() ?>">
            <i class="fas fa-feather-pointed"></i>JiDoor
        </a>

        <!-- Tengah: Menu -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav gap-4">
                <li class="nav-item"><a class="nav-link <?= $uri === '' ? 'is-active' : '' ?>" href="<?= base_url() ?>">Beranda</a></li>
                <li class="nav-item"><a class="nav-link <?= $uri === 'katalog' ? 'is-active' : '' ?>" href="<?= base_url('katalog') ?>">Katalog</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
            </ul>
        </div>

        <!-- Kanan: Aksi -->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <form action="<?= base_url('search') ?>" method="post" class="nav-search d-none d-xl-block">
                <?= csrf_field() ?>
                <input type="text" name="q" placeholder="Cari produk..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="submit" aria-label="Cari"><i class="fas fa-search"></i></button>
            </form>

            <a href="<?= base_url('disukai') ?>" class="nav-icon d-none d-md-inline-flex position-relative" aria-label="Disukai">
                <i class="<?= $like_count > 0 ? 'fas' : 'far' ?> fa-heart"></i>
                <?php if ($like_count > 0): ?>
                    <span class="position-absolute translate-middle badge rounded-pill bg-dark mt-1" style="font-size:.58rem; left:100%; top:0;"><?= $like_count ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= base_url('keranjang') ?>" class="nav-icon position-relative" aria-label="Keranjang">
                <i class="fas fa-bag-shopping"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="position-absolute translate-middle badge rounded-pill bg-dark mt-1" style="font-size:.58rem; left:100%; top:0;"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <div class="dropdown">
                <?php if ($user_id): ?>
                    <a href="#" class="nav-icon dropdown-toggle-no position-relative" data-bs-toggle="dropdown" aria-label="Akun">
                        <i class="far fa-user"></i>
                        <?php if ($notif_count > 0): ?>
                            <span class="position-absolute translate-middle p-1 rounded-circle mt-1" style="background:var(--accent-warm); left:100%; top:0;">
                                <span class="visually-hidden">Notifikasi baru</span>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">AKUN</h6></li>
                        <li><a class="dropdown-item" href="<?= base_url('pesanan') ?>"><i class="far fa-list-alt me-2"></i>Pesanan Saya</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('riwayat-rating') ?>"><i class="far fa-star me-2"></i>Rating Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="fas fa-arrow-right-from-bracket me-2"></i>Keluar</a></li>
                    </ul>
                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="nav-icon" aria-label="Masuk"><i class="far fa-user"></i></a>
                <?php endif; ?>
            </div>

            <button class="nav-icon d-lg-none border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Drawer mobile -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav" style="max-width:340px;">
    <div class="offcanvas-header px-4 pt-4">
        <span class="fw-bold fs-5" style="letter-spacing:-.02em;">JiDoor</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body px-4 d-flex flex-column">
        <form action="<?= base_url('search') ?>" method="post" class="nav-search w-100 mb-4" style="width:100% !important;">
            <?= csrf_field() ?>
            <input type="text" name="q" placeholder="Cari produk...">
            <button type="submit" aria-label="Cari"><i class="fas fa-search"></i></button>
        </form>
        <ul class="list-unstyled d-grid gap-1 mb-auto">
            <li><a class="d-block py-3 fw-semibold text-decoration-none border-bottom" style="border-color:var(--line) !important; color:var(--ink);" href="<?= base_url() ?>">Beranda</a></li>
            <li><a class="d-block py-3 fw-semibold text-decoration-none border-bottom" style="border-color:var(--line) !important; color:var(--ink);" href="<?= base_url('katalog') ?>">Katalog</a></li>
            <li><a class="d-block py-3 fw-semibold text-decoration-none border-bottom" style="border-color:var(--line) !important; color:var(--ink);" href="#tentang">Tentang</a></li>
            <?php if ($user_id): ?>
            <li><a class="d-block py-3 fw-semibold text-decoration-none border-bottom" style="border-color:var(--line) !important; color:var(--ink);" href="<?= base_url('pesanan') ?>">Pesanan Saya</a></li>
            <li><a class="d-block py-3 fw-semibold text-decoration-none border-bottom" style="border-color:var(--line) !important; color:var(--ink);" href="<?= base_url('riwayat-rating') ?>">Rating Saya</a></li>
            <?php endif; ?>
        </ul>
        <?php if (!$user_id): ?>
            <a href="<?= base_url('login') ?>" class="btn-ink btn-block2 mb-3"><i class="far fa-user me-2"></i>Masuk</a>
        <?php else: ?>
            <a href="<?= base_url('logout') ?>" class="btn-line btn-block2 mb-3"><i class="fas fa-arrow-right-from-bracket me-2"></i>Keluar</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Global Alert -->
<?php if ($this->session->flashdata('success') || $this->session->flashdata('error')): ?>
<div class="container position-fixed top-0 start-50 translate-middle-x mt-4" style="z-index: 2000; width: auto; min-width: 300px;">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="flash2" role="alert"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="flash2 flash-err" role="alert"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($show_chat): ?>
    <?= $this->load->view('frontend/components/v_chat_widget', ['chat_token' => $chat_token, 'chat_user_id' => $chat_user_id], TRUE) ?>
<?php endif; ?>
