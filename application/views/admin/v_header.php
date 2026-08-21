<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — JiDoor Store</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Admin Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css?v=1.3') ?>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar">
    <a href="<?= base_url('admin') ?>" class="sidebar-logo text-decoration-none">
        <i class="fas fa-door-open text-warning"></i>
        <span>JiDoor Admin</span>
    </a>

    <nav class="sidebar-nav">
        <a href="<?= base_url('admin') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= base_url('admin/produk') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'produk' ? 'active' : '' ?>">
            <i class="fas fa-boxes-stacked"></i>
            <span>Produk</span>
        </a>
        <a href="<?= base_url('admin/kategori') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'kategori' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span>Kategori</span>
        </a>
        <a href="<?= base_url('admin/pesanan') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'pesanan' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i>
            <span>Pesanan</span>
        </a>
        <a href="<?= base_url('admin/ratings') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'ratings' ? 'active' : '' ?>">
            <i class="fas fa-star"></i>
            <span>Ratings & Review</span>
        </a>
        <a href="<?= base_url('admin/users') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Pengguna</span>
        </a>
        <a href="<?= base_url('admin/rekomendasi') ?>" class="nav-item-admin <?= isset($active_tab) && $active_tab == 'rekomendasi' ? 'active' : '' ?>">
            <i class="fas fa-brain"></i>
            <span>Rekomendasi AI</span>
        </a>
    </nav>

    <div class="sidebar-logout-wrapper border-top border-secondary border-opacity-10 mt-auto pt-4">
        <a href="<?= base_url('logout') ?>" class="nav-item-admin text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!-- Main Content Area -->
<main class="admin-main">
    <!-- Topbar Info -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h4 class="fw-bold mb-0 text-white"><?= isset($title) ? $title : 'Dashboard Overview' ?></h4>
            <p class="text-muted small mb-0"><?= date('l, d F Y') ?></p>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <h6 class="fw-bold mb-0 text-white"><?= $this->session->userdata('username') ?></h6>
                <small class="text-warning">Administrator</small>
            </div>
            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 1.2rem;">
                <?= strtoupper(substr($this->session->userdata('username'), 0, 1)) ?>
            </div>
        </div>
    </div>

    <!-- Content injection -->
