<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'JiDoor — Minimalist Premium Doors' ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=1.5') ?>">
</head>
<body>

<?php 
$is_home = (uri_string() == '');
$is_auth = in_array(uri_string(), ['login', 'register', 'auth/login', 'auth/register']);
$user_id = $this->session->userdata('user_id');

// Widget chat (Revisi #7) — hanya untuk customer yang login
$show_chat = false;
if ($user_id && $this->session->userdata('role') !== 'admin') {
    $this->load->library('chat_token');
    $chat_token   = Chat_Token::make($user_id, 'user');
    $chat_user_id = (int)$user_id;
    $show_chat    = true;
}
?>

<!-- Navbar Minimalis -->
<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-transparent fixed-top py-3">
    <div class="container">
        <!-- Left: Navigation Menu -->
        <div class="collapse navbar-collapse flex-grow-1" id="navbarNav">
            <ul class="navbar-nav gap-3">
                <li class="nav-item"><a class="nav-link fw-bold text-uppercase small ls-1" href="<?= base_url() ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link fw-bold text-uppercase small ls-1" href="<?= base_url('katalog') ?>">Shop</a></li>
                <li class="nav-item"><a class="nav-link fw-bold text-uppercase small ls-1" href="#">Pages</a></li>
                <li class="nav-item"><a class="nav-link fw-bold text-uppercase small ls-1" href="#">Blog</a></li>
            </ul>
        </div>

        <!-- Center: Logo -->
        <a class="navbar-brand mx-auto text-center flex-grow-0" href="<?= base_url() ?>">
            <h2 class="fw-bold mb-0" style="letter-spacing: -1px; font-size: 2rem;">JiDoor</h2>
        </a>

        <!-- Right: Icons -->
        <div class="d-flex align-items-center justify-content-end flex-grow-1 gap-4">
            <!-- Functional Search -->
            <form action="<?= base_url('search') ?>" method="post" class="d-none d-md-flex position-relative align-items-center">
                <?= csrf_field() ?>
                <input type="text" name="q" class="search-input-mixtas" placeholder="SEARCH...">
                <button type="submit" class="btn p-0 border-0 bg-transparent text-dark position-absolute end-0 me-1"><i class="fas fa-search fs-5"></i></button>
            </form>
            
            <div class="dropdown">
                <?php if ($user_id): ?>
                    <a href="#" class="text-decoration-none text-dark dropdown-toggle no-caret position-relative" data-bs-toggle="dropdown">
                        <i class="far fa-user fs-5"></i>
                        <?php if (isset($notif_count) && $notif_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                <span class="visually-hidden">New notifications</span>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-3 rounded-0 mt-3">
                        <li><h6 class="dropdown-header px-0 mb-2 small fw-bold ls-1">ACCOUNT</h6></li>
                        <li><a class="dropdown-item small ls-1 py-2" href="<?= base_url('pesanan') ?>">MY ORDERS</a></li>
                        <li><a class="dropdown-item small ls-1 py-2" href="<?= base_url('riwayat-rating') ?>">MY RATINGS</a></li>
                        <li><hr class="dropdown-divider opacity-10"></li>
                        <li><a class="dropdown-item small ls-1 py-2 text-danger fw-bold" href="<?= base_url('logout') ?>">LOGOUT</a></li>
                    </ul>
                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="text-decoration-none text-dark"><i class="far fa-user fs-5"></i></a>
                <?php endif; ?>
            </div>

            <a href="<?= base_url('wishlist') ?>" class="text-decoration-none text-dark d-none d-md-block position-relative">
                <i class="<?= isset($wishlist_count) && $wishlist_count > 0 ? 'fas' : 'far' ?> fa-heart fs-5"></i>
                <?php if (isset($wishlist_count) && $wishlist_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger p-1" style="font-size: 0.6rem; min-width: 18px;"><?= $wishlist_count ?></span>
                <?php endif; ?>
            </a>
            
            <a href="<?= base_url('keranjang') ?>" class="text-decoration-none text-dark position-relative">
                <i class="fas fa-shopping-bag fs-5"></i>
                <?php if (isset($cart_count) && $cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark p-1" style="font-size: 0.6rem; min-width: 18px;"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <button class="navbar-toggler border-0 shadow-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars fs-4"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Global Alert -->
<?php if ($this->session->flashdata('success') || $this->session->flashdata('error')): ?>
<div class="container position-fixed top-0 start-50 translate-middle-x mt-5" style="z-index: 2000; width: auto; min-width: 300px;">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-dark border-0 shadow-lg text-center rounded-0 py-3 px-5" role="alert"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-lg text-center rounded-0 py-3 px-5" role="alert"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($show_chat): ?>
    <?= $this->load->view('frontend/components/v_chat_widget', ['chat_token' => $chat_token, 'chat_user_id' => $chat_user_id], TRUE) ?>
<?php endif; ?>
