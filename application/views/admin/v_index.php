<div class="row g-4 mb-5">
    <!-- Stat Cards -->
    <div class="col-md-3">
        <div class="admin-card h-100 border-start border-4 border-warning">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $total_products ?></div>
                    <div class="stat-label">Total Produk</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card h-100 border-start border-4 border-info">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $total_orders ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card h-100 border-start border-4 border-success">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-money-bill-trend-up"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size: 1.1rem;">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
                    <div class="stat-label">Pendapatan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card h-100 border-start border-4 border-primary">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $total_users ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="admin-card-title d-flex justify-content-between align-items-center mb-4">
                <span><i class="fas fa-chart-line me-2 text-admin-primary"></i>Tren Pendapatan</span>
                <span class="badge bg-admin-primary bg-opacity-10 text-admin-primary small fw-normal">12 Bulan Terakhir</span>
            </div>
            <div style="height: 320px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-title mb-4">
                <i class="fas fa-chart-pie me-2 text-info"></i>Komposisi Produk
            </div>
            <div style="height: 250px;">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-4 small text-muted">
                <ul class="list-unstyled mb-0">
                    <?php foreach(array_slice($category_dist, 0, 5) as $cd): ?>
                    <li class="d-flex justify-content-between mb-1">
                        <span><?= htmlspecialchars($cd->category_label) ?></span>
                        <span class="text-white fw-bold"><?= $cd->product_count ?> Item</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Selling Products -->
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <div class="admin-card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-crown me-2 text-warning"></i>Produk Terlaris</span>
                <span class="small text-muted fw-normal">Top 5 Penjualan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-admin align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th class="text-end">Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($top_selling)): foreach($top_selling as $ts): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $ts->image && $ts->image !== 'default.jpg' ? base_url('uploads/products/' . $ts->image) : 'https://placehold.co/40x40/f5f5f5/000000?text=IMG' ?>" class="rounded" style="width: 35px; height: 35px; object-fit: cover;">
                                    <div>
                                        <div class="text-white small fw-bold"><?= htmlspecialchars($ts->name) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Rp <?= number_format($ts->price, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($ts->category_name) ?></td>
                            <td class="text-end fw-bold text-success"><?= $ts->total_sold ?> <span class="small text-muted fw-normal">Pcs</span></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data penjualan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <div class="admin-card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2 text-primary"></i>Pesanan Terbaru</span>
                <a href="<?= base_url('admin/pesanan') ?>" class="btn btn-sm btn-admin-outline" style="padding: 2px 10px; font-size: 0.7rem;">Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-admin align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($recent_orders)): foreach($recent_orders as $o): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white small">#<?= $o->id ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($o->username) ?></div>
                            </td>
                            <td>
                                <?php 
                                    $badge = ['pending'=>'badge-pending','paid'=>'badge-paid','shipped'=>'badge-shipped','rejected'=>'badge-rejected'][$o->status] ?? ''; 
                                ?>
                                <span class="admin-badge <?= $badge ?>" style="font-size: 0.65rem; padding: 2px 8px;"><?= $o->status ?></span>
                            </td>
                            <td class="text-end fw-bold text-white small">Rp <?= number_format($o->total_price, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Low Stock -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-title text-danger">
                <i class="fas fa-triangle-exclamation me-2"></i>Peringatan Stok Rendah
            </div>
            <div class="table-responsive">
                <table class="table table-admin">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Sisa Stok</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($low_stock)): foreach($low_stock as $ls): ?>
                        <tr>
                            <td>
                                <span class="text-white small fw-bold"><?= htmlspecialchars($ls->name) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger text-white rounded-pill px-3" style="font-size: 0.7rem;"><?= $ls->stock ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('admin/produk_edit/' . $ls->id) ?>" class="btn btn-sm btn-admin-outline" style="font-size: 0.7rem; padding: 2px 8px;">Update Stok</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Semua stok produk aman.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-title mb-3">Aksi Cepat</div>
            <div class="d-grid gap-2">
                <a href="<?= base_url('admin/produk/tambah') ?>" class="btn btn-admin-primary text-start d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-plus-circle me-2"></i>Tambah Produk</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>
                <a href="<?= base_url('admin/pesanan?status=pending') ?>" class="btn btn-admin-outline text-start d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2 text-warning"></i>Verifikasi Bayar</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>
                <a href="<?= base_url('admin/ratings') ?>" class="btn btn-admin-outline text-start d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-star me-2 text-info"></i>Moderasi Review</span>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Revenue Chart (Line)
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?= json_encode($revenue_chart) ?>;
    
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.month_label),
            datasets: [{
                label: 'Pendapatan',
                data: revenueData.map(d => d.revenue),
                borderColor: '#ff6b00',
                backgroundColor: 'rgba(255, 107, 0, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ff6b00',
                pointBorderColor: '#1a1a1a',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { 
                        color: '#999',
                        font: { size: 10 },
                        callback: function(value) { return 'Rp ' + value.toLocaleString(); }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#999', font: { size: 10 } }
                }
            }
        }
    });

    // 2. Category Chart (Pie/Doughnut)
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catData = <?= json_encode($category_dist) ?>;
    
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catData.map(d => d.category_label),
            datasets: [{
                data: catData.map(d => d.product_count),
                backgroundColor: [
                    '#ff6b00', '#00cfd5', '#7c4dff', '#ffc107', '#4caf50', 
                    '#e91e63', '#00bcd4', '#ff9800', '#9c27b0'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
