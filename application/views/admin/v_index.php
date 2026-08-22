<?php
// Turunan metrik dari data yang sudah ada (tanpa query tambahan)
$revSeries = array_map('intval', array_column($revenue_chart ?: [], 'revenue'));
$nRev = count($revSeries);
$lastRev = $nRev ? $revSeries[$nRev - 1] : 0;
$prevRev = $nRev >= 2 ? $revSeries[$nRev - 2] : 0;
$revDelta = null;
if ($prevRev > 0 && $lastRev !== $prevRev) {
    $revDelta = (int) round((($lastRev - $prevRev) / $prevRev) * 100);
}

// Titik sparkline SVG dinormalisasi ke viewBox 100x34
$sparkPoly = '';
if ($nRev >= 2) {
    $min = min($revSeries);
    $max = max($revSeries);
    $range = max(1, $max - $min);
    $step = 100 / max(1, $nRev - 1);
    $pts = [];
    foreach ($revSeries as $i => $v) {
        $x = $i * $step;
        $y = 30 - ((($v - $min) / $range) * 26);
        $pts[] = round($x, 1) . ',' . round($y, 1);
    }
    $sparkPoly = implode(' ', $pts);
}
?>

<div class="row g-3 g-md-4 mb-4">
    <!-- Stat Cards -->
    <div class="col-sm-6 col-xl-3">
        <div class="admin-card kpi-card hover-lift">
            <div class="kpi-top">
                <span class="kpi-label">Total Produk</span>
                <span class="kpi-tile" style="background:var(--accent-soft);color:var(--accent);">
                    <i class="fas fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="kpi-value"><?= $total_products ?></div>
            <div class="kpi-foot">
                <i class="fas fa-layer-group" style="color:var(--text-3);font-size:.72rem;"></i>
                <?= count($category_dist) ?> kategori aktif
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card kpi-card hover-lift">
            <div class="kpi-top">
                <span class="kpi-label">Total Pesanan</span>
                <span class="kpi-tile" style="background:var(--info-bg);color:var(--info);">
                    <i class="fas fa-receipt"></i>
                </span>
            </div>
            <div class="kpi-value"><?= $total_orders ?></div>
            <div class="kpi-foot">
                <a href="<?= base_url('admin/pesanan?status=pending') ?>" class="d-inline-flex align-items-center gap-2">
                    <span class="dot warn"></span>
                    <span class="text-warning-emphasis fw-bold"><?= $pending_orders ?></span> menunggu verifikasi
                </a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card kpi-card hover-lift">
            <div class="kpi-top">
                <span class="kpi-label">Pendapatan</span>
                <span class="kpi-tile" style="background:var(--success-bg);color:var(--success);">
                    <i class="fas fa-money-bill-trend-up"></i>
                </span>
            </div>
            <div class="kpi-value kpi-value-money num">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
            <?php if ($sparkPoly !== ''): ?>
            <svg class="spark" viewBox="0 0 100 34" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="sparkFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.16"/>
                        <stop offset="100%" stop-color="#4f46e5" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <polygon fill="url(#sparkFill)" points="0,34 <?= $sparkPoly ?> 100,34"/>
                <polyline points="<?= $sparkPoly ?>" stroke="#4f46e5" stroke-width="2"/>
            </svg>
            <?php endif ?>
            <div class="kpi-foot">
                <?php if ($revDelta !== null): ?>
                    <?php if ($revDelta > 0): ?>
                        <span class="delta up"><i class="fas fa-arrow-trend-up"></i>+<?= $revDelta ?>%</span>
                    <?php elseif ($revDelta < 0): ?>
                        <span class="delta down"><i class="fas fa-arrow-trend-down"></i><?= $revDelta ?>%</span>
                    <?php endif ?>
                <?php else: ?>
                    <span class="delta flat">—</span>
                <?php endif ?>
                <span>vs bulan lalu</span>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-card kpi-card hover-lift">
            <div class="kpi-top">
                <span class="kpi-label">Total Pengguna</span>
                <span class="kpi-tile" style="background:var(--warning-bg);color:var(--warning);">
                    <i class="fas fa-users"></i>
                </span>
            </div>
            <div class="kpi-value"><?= $total_users ?></div>
            <div class="kpi-foot">
                <i class="fas fa-user-check" style="color:var(--text-3);font-size:.72rem;"></i>
                akun terdaftar
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="fw-bold"><i class="fas fa-chart-line me-2" style="color: var(--accent);"></i>Tren Pendapatan</span>
                <span class="badge-neutral">12 Bulan Terakhir</span>
            </div>
            <div style="height: 320px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="fw-bold mb-4"><i class="fas fa-chart-pie me-2" style="color: var(--accent);"></i>Komposisi Produk</div>
            <div style="height: 250px;">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-4 small text-muted">
                <ul class="list-unstyled mb-0">
                    <?php foreach(array_slice($category_dist, 0, 5) as $cd): ?>
                    <li class="d-flex justify-content-between mb-1">
                        <span><?= htmlspecialchars($cd->category_label) ?></span>
                        <span class="fw-bold" style="color: var(--text-1);"><?= $cd->product_count ?> Item</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">
    <!-- Top Selling Products -->
    <div class="col-lg-6">
        <div class="admin-card h-100 p-0">
            <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-crown me-2" style="color: var(--warning);"></i>Produk Terlaris</span>
                <span class="small text-muted">Top 5 Penjualan</span>
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
                                    <img src="<?= $ts->image && $ts->image !== 'default.jpg' ? base_url('uploads/products/' . $ts->image) : 'https://placehold.co/40x40/f1f5f9/94a3b8?text=IMG' ?>" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                                    <div>
                                        <div class="small fw-bold"><?= htmlspecialchars($ts->name) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Rp <?= number_format($ts->price, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($ts->category_name) ?></td>
                            <td class="text-end fw-bold" style="color: var(--success);"><?= $ts->total_sold ?> <span class="small text-muted fw-normal">Pcs</span></td>
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
        <div class="admin-card h-100 p-0">
            <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-clock-rotate-left me-2" style="color: var(--accent);"></i>Pesanan Terbaru</span>
                <a href="<?= base_url('admin/pesanan') ?>" class="btn btn-sm btn-admin-outline">Semua</a>
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
                                <div class="fw-bold small">#<?= $o->id ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($o->username) ?></div>
                            </td>
                            <td>
                                <?php 
                                    $badge = ['pending'=>'badge-pending','paid'=>'badge-paid','processed'=>'badge-processed','shipped'=>'badge-shipped','rejected'=>'badge-rejected','cancelled'=>'badge-cancelled'][$o->status] ?? 'badge-neutral'; 
                                ?>
                                <span class="admin-badge <?= $badge ?>"><?= $o->status ?></span>
                            </td>
                            <td class="text-end fw-bold small num">Rp <?= number_format($o->total_price, 0, ',', '.') ?></td>
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

<div class="row g-3 g-md-4">
    <!-- Low Stock -->
    <div class="col-lg-8">
        <div class="admin-card p-0">
            <?php
            // Gabungkan peringatan level varian + produk tanpa varian, urut stok terkecil
            $lowRows = [];
            foreach ($low_variants as $v) {
                $parts = [];
                if ($v->color !== 'Standar' && $v->color !== '') $parts[] = $v->color;
                if ($v->size  !== 'Standar' && $v->size  !== '') $parts[] = $v->size;
                $lowRows[] = [
                    'type'     => 'variant',
                    'edit_id'  => $v->product_id,
                    'name'     => $v->product_name,
                    'image'    => $v->image,
                    'category' => $v->category_name,
                    'label'    => implode(' · ', $parts),
                    'stock'    => (int) $v->stock,
                ];
            }
            foreach ($low_simple as $s) {
                $lowRows[] = [
                    'type'     => 'simple',
                    'edit_id'  => $s->id,
                    'name'     => $s->name,
                    'image'    => $s->image,
                    'category' => $s->category_name,
                    'label'    => '',
                    'stock'    => (int) $s->stock,
                ];
            }
            usort($lowRows, function ($a, $b) { return $a['stock'] <=> $b['stock']; });
            $lowRows = array_slice($lowRows, 0, 8);
            ?>
            <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="color: var(--danger);">
                    <i class="fas fa-triangle-exclamation me-2"></i>Peringatan Stok Rendah
                </span>
                <span class="small text-muted">Stok ≤ 5 · per varian</span>
            </div>
            <div class="table-responsive">
                <table class="table table-admin">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th class="text-center">Sisa Stok</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($lowRows)): foreach($lowRows as $lr): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $lr['image'] && $lr['image'] !== 'default.jpg' ? base_url('uploads/products/' . $lr['image']) : 'https://placehold.co/40x40/f1f5f9/94a3b8?text=IMG' ?>" class="rounded border" style="width: 38px; height: 38px; object-fit: cover;">
                                    <div>
                                        <div class="small fw-bold"><?= htmlspecialchars($lr['name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($lr['category'] ?: '-') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($lr['type'] === 'variant'): ?>
                                    <span class="vb-chip" style="cursor: default;"><?= htmlspecialchars($lr['label']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif ?>
                            </td>
                            <td class="text-center">
                                <span class="admin-badge badge-rejected" style="font-size: 0.75rem; padding: 5px 12px;"><?= $lr['stock'] ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('admin/produk/edit/' . $lr['edit_id']) ?>" class="btn btn-sm btn-admin-outline">Update Stok</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Semua stok produk &amp; varian aman.</td></tr>
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

            <a href="<?= base_url('admin/produk/tambah') ?>" class="qa-tile qa-primary mb-2">
                <span class="qa-icon" style="background:var(--accent);color:#fff;"><i class="fas fa-plus"></i></span>
                <span class="qa-label">Tambah Produk</span>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>

            <div class="qa-grid">
                <a href="<?= base_url('admin/pesanan?status=pending') ?>" class="qa-tile">
                    <span class="qa-icon" style="background:var(--warning-bg);color:var(--warning);"><i class="fas fa-clock"></i></span>
                    <span class="qa-label">Verifikasi Bayar</span>
                    <?php if ($pending_orders > 0): ?><span class="qa-count"><?= $pending_orders ?></span><?php endif ?>
                </a>
                <a href="<?= base_url('admin/chat') ?>" class="qa-tile">
                    <span class="qa-icon" style="background:var(--info-bg);color:var(--info);"><i class="fas fa-comments"></i></span>
                    <span class="qa-label">Chat Pelanggan</span>
                    <?php if ($unread_chat > 0): ?><span class="qa-count"><?= $unread_chat ?></span><?php endif ?>
                </a>
                <a href="<?= base_url('admin/ratings') ?>" class="qa-tile">
                    <span class="qa-icon" style="background:var(--success-bg);color:var(--success);"><i class="fas fa-star"></i></span>
                    <span class="qa-label">Moderasi Review</span>
                </a>
                <a href="<?= base_url('admin/kategori') ?>" class="qa-tile">
                    <span class="qa-icon" style="background:var(--accent-soft);color:var(--accent);"><i class="fas fa-tags"></i></span>
                    <span class="qa-label">Kelola Kategori</span>
                </a>
                <a href="<?= base_url('admin/users') ?>" class="qa-tile">
                    <span class="qa-icon" style="background:var(--surface-2);color:var(--text-2);"><i class="fas fa-users"></i></span>
                    <span class="qa-label">Data Pengguna</span>
                </a>
                <a href="<?= base_url('admin/rekomendasi') ?>" class="qa-tile">
                    <span class="qa-icon" style="background:var(--accent-soft);color:#7c3aed;"><i class="fas fa-wand-magic-sparkles"></i></span>
                    <span class="qa-label">Rekomendasi AI</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const css = getComputedStyle(document.documentElement);
    const tickColor = css.getPropertyValue('--chart-tick').trim() || '#94a3b8';
    const gridColor = css.getPropertyValue('--chart-grid').trim() || '#eef2f6';

    // 1. Revenue Chart (Line)
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?= json_encode($revenue_chart) ?>;

    const grad = revCtx.createLinearGradient(0, 0, 0, 320);
    grad.addColorStop(0, 'rgba(79, 70, 229, 0.18)');
    grad.addColorStop(1, 'rgba(79, 70, 229, 0)');

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.month_label),
            datasets: [{
                label: 'Pendapatan',
                data: revenueData.map(d => d.revenue),
                borderColor: '#4f46e5',
                backgroundColor: grad,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3.5,
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
                    border: { display: false },
                    grid: { color: gridColor },
                    ticks: {
                        color: tickColor,
                        font: { size: 11 },
                        callback: function(value) { return 'Rp ' + value.toLocaleString(); }
                    }
                },
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: { color: tickColor, font: { size: 11 } }
                }
            }
        }
    });

    // 2. Category Chart (Doughnut)
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catData = <?= json_encode($category_dist) ?>;

    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catData.map(d => d.category_label),
            datasets: [{
                data: catData.map(d => d.product_count),
                backgroundColor: [
                    '#4f46e5', '#818cf8', '#38bdf8', '#34d399', '#fbbf24',
                    '#fb7185', '#a78bfa', '#94a3b8', '#2dd4bf'
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>
