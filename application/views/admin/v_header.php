<?php if (!$this->session->userdata('user_id') || $this->session->userdata('role') !== 'admin') { redirect('auth'); } ?>

<?php
$ci =& get_instance();
$ci->load->model('M_chat');
$unread_chat = $ci->M_chat->count_unread_admin();
$username = $this->session->userdata('username');
$page_title = isset($title) ? $title : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - JiDoor Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/favicon.svg') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css?v=2.9') ?>">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="admin-sidebar" id="adminSidebar">
    <a class="sidebar-logo" href="<?= site_url('admin') ?>">
        <i class="fa-solid fa-door-open"></i> JiDoor
    </a>
    <nav class="sidebar-nav">
        <a href="<?= site_url('admin') ?>" class="nav-item-admin <?= !trim((string) $this->uri->segment(2)) ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="<?= site_url('admin/produk') ?>" class="nav-item-admin <?= (strpos(current_url(), 'produk') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-box"></i> Produk</a>
        <a href="<?= site_url('admin/kategori') ?>" class="nav-item-admin <?= (strpos(current_url(), 'kategori') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-tags"></i> Kategori</a>
        <a href="<?= site_url('admin/pesanan') ?>" class="nav-item-admin <?= (strpos(current_url(), 'pesanan') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-receipt"></i> Pesanan</a>
        <a href="<?= site_url('admin/chat') ?>" class="nav-item-admin <?= (strpos(current_url(), 'chat') !== FALSE) ? 'active' : '' ?>">
            <i class="fa-solid fa-comments"></i> Chat Pelanggan
            <?php if ($unread_chat > 0): ?>
                <span class="badge rounded-pill bg-danger"><?= $unread_chat ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= site_url('admin/ratings') ?>" class="nav-item-admin <?= (strpos(current_url(), 'ratings') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-star"></i> Ulasan</a>
        <a href="<?= site_url('admin/users') ?>" class="nav-item-admin <?= (strpos(current_url(), 'users') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Pengguna</a>
        <a href="<?= site_url('admin/rekomendasi') ?>" class="nav-item-admin <?= (strpos(current_url(), 'rekomendasi') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-wand-magic-sparkles"></i> Rekomendasi AI</a>
        <a href="<?= site_url('admin/pengaturan') ?>" class="nav-item-admin <?= (strpos(current_url(), 'pengaturan') !== FALSE) ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i> Pengaturan</a>
    </nav>
    <div class="sidebar-logout-wrapper">
        <a href="#" onclick="document.getElementById('logoutForm').submit(); return false;" class="nav-item-admin"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== MAIN ===== -->
<main class="admin-main">

    <!-- Topbar -->
    <header class="admin-topbar">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Buka menu"><i class="fa-solid fa-bars"></i></button>
        <nav class="breadcrumb-admin">
            <span class="crumb-root">Admin</span>
            <span class="crumb-sep">/</span>
            <span class="crumb-current"><?= $page_title ?></span>
        </nav>
        <div class="topbar-right">
            <span class="topbar-date"><i class="fa-regular fa-calendar"></i> <?= tanggal_indo() ?></span>
            <div class="dropdown profile-menu">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar-circle"><?= strtoupper(substr($username, 0, 1)) ?></span>
                    <span class="profile-name">
                        <strong><?= htmlspecialchars($username) ?></strong>
                        <small>Administrator</small>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= site_url('/') ?>" target="_blank"><i class="fa-solid fa-store"></i> Lihat Toko</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="document.getElementById('logoutForm').submit(); return false;"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
                </ul>
            </div>
        </div>
    </header>

    <form id="logoutForm" action="<?= site_url('auth/logout') ?>" method="post" class="d-none"><?= csrf_field() ?></form>

    <div class="admin-page">

<!-- Content injection -->
