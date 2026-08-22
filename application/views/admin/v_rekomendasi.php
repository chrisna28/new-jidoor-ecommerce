<!-- 
    JI-DOOR PURE CF INTELLIGENCE DASHBOARD v5.4 (light)
    =====================================================
    Catatan: seluruh fungsi fetch / K-Optimization / K-Fold /
    CF-detail TIDAK diubah — hanya lapisan visual.
-->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-main-content">
    <?php if (!$stats || (isset($stats->status) && $stats->status === 'error')): ?>
    <div class="admin-card text-center py-5">
        <div class="empty-state py-2">
            <div class="empty-icon" style="background: var(--danger-bg); color: var(--danger);"><i class="fas fa-exclamation-triangle"></i></div>
            <h4 class="fw-bold">Gagal Menghubungkan ke Mesin AI</h4>
            <p><?= $stats->message ?? 'Pastikan server Python (port 8000) sudah aktif.' ?></p>
        </div>
    </div>
    <?php elseif (isset($stats->status) && $stats->status === 'empty'): ?>
    <div class="admin-card text-center py-5">
        <div class="empty-state py-2">
            <div class="empty-icon"><i class="fas fa-info-circle"></i></div>
            <h4 class="fw-bold">Belum Ada Data Rating</h4>
            <p>Mesin AI membutuhkan data rating untuk mulai bekerja.</p>
        </div>
    </div>
    <?php else: ?>
    <!-- Main Dashboard Header -->
    <div class="page-head d-flex justify-content-between align-items-end flex-wrap gap-3 pb-4" style="border-bottom: 1px solid var(--border);">
        <div class="d-flex align-items-center">
            <div class="rounded-3 p-2 me-3 ai-float d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: var(--accent-soft); color: var(--accent); font-size: 1.35rem;">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Recommendation Engine</h3>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small">Pure Collaborative Filtering v5.3</span>
                    <span class="badge-neutral"><i class="fa-solid fa-microchip me-1"></i> KNN-OPTIMIZED</span>
                    <span class="badge-neutral ai-pulse" style="color: var(--success); border-color: var(--success-border);"><i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> LIVE</span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-admin-outline d-flex align-items-center" onclick="location.reload()">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Sync Data
            </button>
            <button id="btnRetrain" class="btn btn-admin-primary d-flex align-items-center" onclick="refreshAICache()">
                <i class="fa-solid fa-brain me-2"></i> Retrain Model
            </button>
        </div>
    </div>

    <?php if(!empty($stats->warnings)): ?>
        <div class="info-panel p-warning mb-4 small">
            <h6 class="fw-bold small mb-2" style="color: var(--warning);"><i class="fa-solid fa-triangle-exclamation me-2"></i> System Warnings:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach($stats->warnings as $w): ?>
                    <li><?= $w ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $d = isset($stats->data) ? $stats->data : (isset($stats) ? $stats : null); ?>

    <!-- Main Matrix Stats -->
    <div class="row g-3 g-md-4 mb-4">
        <?php 
        $matrix = [
            ['label' => 'Total Users', 'value' => $d->total_users ?? 0, 'icon' => 'fa-users', 'bg' => 'var(--accent-soft)', 'fg' => 'var(--accent)'],
            ['label' => 'Total Products', 'value' => $d->total_items ?? 0, 'icon' => 'fa-boxes-stacked', 'bg' => 'var(--info-bg)', 'fg' => 'var(--info)'],
            ['label' => 'Data Signals', 'value' => $d->total_signals ?? 0, 'icon' => 'fa-signal', 'bg' => 'var(--success-bg)', 'fg' => 'var(--success)'],
            ['label' => 'Data Density', 'value' => ($d->density_score ?? 0) . '%', 'icon' => 'fa-database', 'bg' => '#f5f3ff', 'fg' => '#7c3aed']
        ];
        foreach ($matrix as $m): ?>
        <div class="col-md-3">
            <div class="admin-card h-100 hover-lift">
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?= $m['bg'] ?>; color: <?= $m['fg'] ?>;">
                        <i class="fas <?= $m['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size: 1.25rem; color: <?= $m['fg'] ?>;"><?= $m['value'] ?></div>
                        <div class="stat-label"><?= $m['label'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Prediction Accuracy Section (Bab 4 Skripsi) -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-md-4">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-bullseye me-2 fs-5" style="color: #e11d48;"></i>
                    <span class="stat-label">Mean Absolute Error (MAE)</span>
                </div>
                <h2 class="fw-bold mb-0 num" style="font-size: 1.6rem; color: #e11d48;"><?= $d->mae ?? '0.0000' ?></h2>
                <p class="text-muted small mb-0 mt-1">Rata-rata selisih rating prediksi vs aktual</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-chart-line me-2 fs-5" style="color: #db2777;"></i>
                    <span class="stat-label">Root Mean Square Error (RMSE)</span>
                </div>
                <h2 class="fw-bold mb-0 num" style="font-size: 1.6rem; color: #db2777;"><?= $d->rmse ?? '0.0000' ?></h2>
                <p class="text-muted small mb-0 mt-1">Akar rata-rata kuadrat kesalahan (Stabilitas)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-vial me-2 fs-5" style="color: #7c3aed;"></i>
                    <span class="stat-label">Evaluation Samples</span>
                </div>
                <h2 class="fw-bold mb-0 num" style="font-size: 1.6rem; color: #7c3aed;"><?= $d->eval_samples ?? 0 ?></h2>
                <p class="text-muted small mb-0 mt-1">Jumlah data uji (Time-based Splitting)</p>
            </div>
        </div>
    </div>

    <div class="info-panel p-info mb-4 small d-flex align-items-start gap-3">
        <i class="fa-solid fa-bolt-lightning mt-1" style="color: var(--info); font-size: 1.1rem;"></i>
        <div>
            <span class="fw-bold d-block">Engine Optimization Active: Sparse Matrix + KNN + Mean-Centering</span>
            <span class="opacity-75">Model sekarang menggunakan normalisasi rata-rata (Mean-Centering) untuk menangani data kosong secara lebih akurat dan pencarian tetangga tercepat menggunakan K-Nearest Neighbors.</span>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="row g-3 g-md-4 mb-4 d-flex align-items-stretch">
        <!-- Model Confidence -->
        <div class="col-lg-4 d-flex">
            <div class="admin-card w-100 mb-0 d-flex flex-column align-items-center justify-content-center position-relative" style="min-height: 400px;">
                <h6 class="small fw-bold text-uppercase ls-1 w-100 mb-0 text-muted position-absolute" style="top: 28px; left: 28px;">Model Confidence</h6>

                <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1 w-100 py-4">
                    <div class="position-relative">
                        <svg width="280" height="280" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="var(--surface-2)" stroke-width="6"></circle>
                            <circle cx="50" cy="50" r="46" fill="none" stroke="url(#gaugeGradient)" stroke-width="8" 
                                    stroke-dasharray="<?= ($d->final_confidence ?? 0) * 2.89 ?>, 289" 
                                    stroke-linecap="round" transform="rotate(-90 50 50)"
                                    style="transition: stroke-dasharray 2s ease-out;"></circle>
                            <defs>
                                <linearGradient id="gaugeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h1 class="fw-bold mb-0" style="font-size: 3.25rem; letter-spacing: -2px; color: var(--text-1);"><?= $d->final_confidence ?? 0 ?><small class="fs-4 opacity-50" style="letter-spacing: 0;">%</small></h1>
                            <span class="small fw-bold ls-1" style="font-size: 0.75rem; letter-spacing: 2px; color: var(--success);">OPTIMIZED</span>
                        </div>
                    </div>
                </div>

                <div class="w-100 pt-3" style="border-top: 1px solid var(--border);">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.62rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">Similarity</span>
                            <span class="fw-bold small" style="color: var(--info);"><?= $d->sim_strength ?? 0 ?>%</span>
                            <div class="progress mt-1" style="height: 4px; background: var(--surface-2);">
                                <div class="progress-bar bg-info" style="width: <?= $d->sim_strength ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <span class="text-muted d-block" style="font-size: 0.62rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">Coverage</span>
                            <span class="fw-bold small" style="color: var(--success);"><?= $d->coverage_score ?? 0 ?>%</span>
                            <div class="progress mt-1 ms-auto" style="height: 4px; width: 100%; background: var(--surface-2);">
                                <div class="progress-bar bg-success" style="width: <?= $d->coverage_score ?? 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signal Distribution & Active Users -->
        <div class="col-lg-8 d-flex flex-column gap-4">
            <!-- Signal Distribution -->
            <div class="admin-card flex-fill mb-0 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="small fw-bold text-uppercase mb-0 ls-1 text-muted">Signal Distribution (Data Sources)</h6>
                    <span class="badge-neutral"><?= $d->total_signals ?? 0 ?> total</span>
                </div>
                <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <?php 
                    $source_dist = isset($d->source_distribution) ? (array)$d->source_distribution : [];
                    $signal_colors = [
                        'explicit' => ['label' => 'Rating (Explicit)', 'color' => '#4f46e5', 'icon' => 'fa-star'],
                        'purchase' => ['label' => 'Purchase', 'color' => '#059669', 'icon' => 'fa-shopping-bag'],
                        'wishlist' => ['label' => 'Wishlist', 'color' => '#db2777', 'icon' => 'fa-heart'],
                        'cart' => ['label' => 'Cart', 'color' => '#0284c7', 'icon' => 'fa-cart-shopping'],
                        'view' => ['label' => 'Product Views', 'color' => '#7c3aed', 'icon' => 'fa-eye'],
                    ];
                    $total_signals = array_sum($source_dist) ?: 1;
                    foreach ($signal_colors as $key => $info):
                        $count = $source_dist[$key] ?? 0;
                        $pct = round(($count / $total_signals) * 100, 1);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-medium"><i class="fas <?= $info['icon'] ?> me-2" style="color: <?= $info['color'] ?>;"></i><?= $info['label'] ?></span>
                            <span class="text-muted small"><?= $count ?> <span class="opacity-50">(<?= $pct ?>%)</span></span>
                        </div>
                        <div class="progress" style="height: 6px; background: var(--surface-2);">
                            <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $info['color'] ?>; border-radius: 10px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Active vs Total -->
            <div class="admin-card flex-fill mb-0">
                <h6 class="small fw-bold text-uppercase mb-4 ls-1 text-muted">Active Coverage</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: var(--accent-soft); border: 1px solid var(--accent-ring);">
                            <span class="text-muted d-block small" style="font-size: 0.68rem; font-weight: 600;">Active Users (in Model)</span>
                            <span class="fw-bold fs-4" style="color: var(--accent);"><?= $d->active_users ?? 0 ?></span>
                            <span class="text-muted small"> / <?= $d->total_users ?? 0 ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: var(--info-bg); border: 1px solid var(--info-border);">
                            <span class="text-muted d-block small" style="font-size: 0.68rem; font-weight: 600;">Active Products (in Model)</span>
                            <span class="fw-bold fs-4" style="color: var(--info);"><?= $d->active_products ?? 0 ?></span>
                            <span class="text-muted small"> / <?= $d->total_items ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Similarity Pairs Section -->
    <div class="admin-card mb-4 p-0">
        <div class="p-4 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border);">
            <div>
                <h5 class="fw-bold mb-1">Item-Item Cosine Similarity</h5>
                <p class="text-muted small mb-0">Top produk yang sering muncul bersamaan dalam perilaku pengguna</p>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">Avg Strength</span>
                <span class="fw-bold fs-5" style="color: var(--warning);"><?= $d->sim_strength ?? 0 ?>%</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-admin table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 35%;">Product A</th>
                        <th class="text-center" style="width: 20%;">Cosine Similarity</th>
                        <th class="text-center" style="width: 15%;">Co-Occur</th>
                        <th class="text-center" style="width: 10%;">Penalty</th>
                        <th class="pe-4 text-end" style="width: 20%;">Product B</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($d->top_similarity_pairs)): foreach ($d->top_similarity_pairs as $p): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2" style="background: var(--surface-2); color: var(--text-3);">
                                    <i class="fas fa-box"></i>
                                </div>
                                <span class="fw-medium"><?= $p->p1 ?></span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex flex-column align-items-center">
                                <span class="px-3 py-1 rounded-pill small fw-bold mb-1" style="background: var(--success-bg); color: var(--success); border: 1px solid var(--success-border);">
                                    <?= number_format($p->score * 100, 1) ?>%
                                </span>
                                <div class="d-flex gap-1">
                                    <?php for($i=0; $i<5; $i++): ?>
                                        <div style="width: 10px; height: 3px; border-radius: 2px; background: <?= $i < ($p->score * 5) ? '#059669' : 'var(--surface-2)' ?>"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center num"><?= $p->co_occurrence ?? 0 ?></td>
                        <td class="text-center" style="color: var(--warning);"><?= number_format(($p->penalty ?? 0) * 100, 0) ?>%</td>
                        <td class="pe-4">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <span class="fw-medium me-1"><?= $p->p2 ?></span>
                                <div class="rounded-3 p-2" style="background: var(--surface-2); color: var(--text-3);">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-layer-group"></i></div>
                            <h6>Belum ada korelasi signifikan</h6>
                            <p>Kumpulkan lebih banyak data interaksi pengguna.</p>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Academic Experiments (Bab 4 Skripsi) -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5 pt-4 flex-wrap gap-2" style="border-top: 1px solid var(--border);">
        <div>
            <h5 class="fw-bold mb-1"><i class="fas fa-microscope me-2" style="color: var(--accent);"></i>Eksperimen Akademik (Bab 4)</h5>
            <p class="text-muted small mb-0">Uji coba parameter model untuk menemukan konfigurasi optimal (Optimasi K &amp; K-Fold)</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-admin-outline rounded-pill px-3" onclick="runKFoldValidation()">
                <i class="fas fa-vial me-1"></i> Run K-Fold CV
            </button>
            <button class="btn btn-sm btn-admin-primary rounded-pill px-3" onclick="runKOptimization()">
                <i class="fas fa-chart-line me-1"></i> Optimize K
            </button>
        </div>
    </div>

    <div class="row g-3 g-md-4 mb-4">
        <!-- K-Optimization Chart -->
        <div class="col-lg-8">
            <div class="admin-card h-100" id="kOptContainer">
                <h6 class="small fw-bold text-uppercase mb-4 ls-1 text-muted">K-Optimization Graph (K vs RMSE)</h6>
                <div style="height: 300px; width: 100%;" class="d-flex align-items-center justify-content-center rounded-3 info-panel">
                    <canvas id="kOptChart"></canvas>
                    <div id="kOptPlaceholder" class="text-muted small opacity-75 px-3 text-center">Klik "Optimize K" untuk memulai simulasi perbandingan nilai K.</div>
                </div>
            </div>
        </div>

        <!-- K-Fold Results -->
        <div class="col-lg-4">
            <div class="admin-card h-100" id="kFoldContainer">
                <h6 class="small fw-bold text-uppercase mb-4 ls-1 text-muted">K-Fold Cross Validation</h6>
                <div id="kFoldResults" class="d-flex flex-column gap-3">
                    <div class="empty-state py-4">
                        <div class="empty-icon"><i class="fas fa-vials"></i></div>
                        <p>Hasil K-Fold akan muncul di sini setelah eksekusi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CF Calculation Detail Section (Unwrapped) -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5 pt-4 flex-wrap gap-2" style="border-top: 1px solid var(--border);">
        <div>
            <h5 class="fw-bold mb-1"><i class="fas fa-calculator me-2" style="color: var(--info);"></i>Detail Perhitungan CF</h5>
            <p class="text-muted small mb-0">Step-by-step proses Collaborative Filtering: dari data mentah hingga rekomendasi final</p>
        </div>
        <div class="d-flex gap-2">
            <select id="cfDetailUserSelect" class="form-select form-control-admin form-select-sm" style="width: 220px;">
                <?php if (!empty($user_list)): ?>
                    <?php foreach ($user_list as $u): ?>
                        <option value="<?= $u->id ?>">User #<?= $u->id ?> — <?= htmlspecialchars($u->username) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button class="btn btn-sm btn-admin-primary rounded-pill px-3" onclick="loadCFDetail()"><i class="fas fa-search-plus me-1"></i> Analisis</button>
        </div>
    </div>
    
    <div id="cfDetailContainer" class="mt-4">
        <div class="admin-card text-center text-muted small">Pilih user dan klik "Analisis" untuk melihat detail perhitungan CF.</div>
    </div>

<?php endif; ?>

<script>
    function refreshAICache() {
        const btn = document.getElementById('btnRetrain');
        const icon = btn.querySelector('i');
        btn.classList.add('disabled');
        icon.classList.add('fa-spin');
        fetch('http://127.0.0.1:8000/cache/refresh', { method: 'POST' })
            .then(res => res.json())
            .then(data => { showToast('Success: ' + data.message, 'success'); setTimeout(() => location.reload(), 800); })
            .catch(err => { showToast('Error refreshing cache.', 'error'); btn.classList.remove('disabled'); icon.classList.remove('fa-spin'); });
    }

    async function loadCFDetail() {
        const userId = document.getElementById('cfDetailUserSelect').value;
        const container = document.getElementById('cfDetailContainer');
        container.innerHTML = '<div class="text-center py-5 text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Menghitung CF untuk User #' + userId + '...</div>';

        try {
            const res = await fetch(`http://127.0.0.1:8000/admin/cf-detail/${userId}`);
            const data = await res.json();
            let html = '';

            // Render each step
            Object.keys(data.steps).sort().forEach((key, idx) => {
                const step = data.steps[key];
                const colors = ['#4f46e5','#0284c7','#059669','#7c3aed','#db2777','#0891b2'];
                const c = colors[idx % colors.length];
                
                html += `<div class="mb-4 p-4 rounded-4 admin-card mb-0">`;
                html += `<div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle me-3 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:${c};color:#fff;font-size:0.8rem;font-weight:bold;">${idx+1}</span>
                    <div><h6 class="fw-bold mb-0 small">${step.title}</h6>
                    <span class="text-muted" style="font-size:0.72rem;">${step.description}</span></div></div>`;

                if (step.formula) {
                    html += `<div class="mb-3 px-3 py-2 rounded-3" style="background:var(--accent-soft);border:1px solid var(--accent-ring);font-family:monospace;font-size:0.78rem;color:var(--accent);">${step.formula}</div>`;
                }

                if (step.data && step.data.length > 0) {
                    html += `<div class="table-responsive rounded-3" style="max-height:350px;overflow-y:auto;border:1px solid var(--border);">`;
                    html += `<table class="table table-sm table-hover align-middle mb-0" style="font-size:0.76rem;">`;
                    
                    // Header
                    const cols = Object.keys(step.data[0]);
                    html += '<thead class="sticky-top" style="z-index:1;"><tr>';
                    cols.forEach(col => {
                        let label = col.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `<th class="py-2 text-muted" style="background:var(--surface-2);font-size:0.62rem;text-transform:uppercase;letter-spacing:1px;">${label}</th>`;
                    });
                    html += '</tr></thead><tbody>';
                    
                    // Rows
                    step.data.forEach(row => {
                        html += '<tr>';
                        cols.forEach(col => {
                            let val = row[col];
                            let cls = '';
                            if (typeof val === 'number' && col !== 'user_id' && col !== 'product_id' && col !== 'other_user_id' && col !== 'source_product_id' && col !== 'similar_product_id') {
                                cls = val > 0 ? 'text-success fw-semibold' : 'text-muted';
                                if (col.includes('similarity') || col.includes('pct') || col.includes('score')) {
                                    if (val > 0.5) cls = 'text-warning fw-bold';
                                    else if (val > 0) cls = 'text-info';
                                }
                            }
                            if (col === 'origin') {
                                let bg = '#94a3b8';
                                if (val && val.includes('BEST MATCH')) bg = '#059669';
                                else if (val && val.includes('FOR YOU')) bg = '#0284c7';
                                else if (val && val.includes('STYLE MATCH')) bg = '#0891b2';
                                else if (val && (val.includes('HOT HITS') || val.includes('BEST SELLER') || val.includes('TRENDING'))) bg = '#4f46e5';
                                else if (val && val.includes('NEW ARRIVAL')) bg = '#7c3aed';
                                val = `<span class="badge rounded-pill px-2 py-1" style="background:${bg};font-size:0.6rem;color:#fff;">${val}</span>`;
                            }
                            if (col === 'source') {
                                let bg2 = '#94a3b8';
                                if (val === 'explicit') bg2 = '#4f46e5';
                                else if (val === 'purchase') bg2 = '#059669';
                                else if (val === 'wishlist') bg2 = '#db2777';
                                else if (val === 'cart') bg2 = '#0284c7';
                                else if (val === 'view') bg2 = '#7c3aed';
                                val = `<span class="badge rounded-pill px-2 py-1" style="background:${bg2};font-size:0.6rem;color:#fff;">${val}</span>`;
                            }
                            html += `<td class="${cls}">${val !== null && val !== undefined ? val : '-'}</td>`;
                        });
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += `<div class="text-center text-muted small py-3 opacity-75"><i class="fas fa-inbox me-1"></i> Tidak ada data</div>`;
                }
                html += '</div>';
            });

            container.innerHTML = html;
        } catch(e) {
            container.innerHTML = '<div class="text-center py-5 text-danger small"><i class="fas fa-plug-circle-xmark me-2"></i>Gagal memuat data. Pastikan FastAPI aktif.</div>';
        }
    }
    async function runKOptimization() {
        const container = document.getElementById('kOptContainer');
        const placeholder = document.getElementById('kOptPlaceholder');
        const canvas = document.getElementById('kOptChart');
        
        placeholder.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Mensimulasikan nilai K (1-20)...';
        canvas.style.display = 'none';

        try {
            const res = await fetch('http://127.0.0.1:8000/admin/optimize-k?start=1&end=20');
            const data = await res.json();
            
            if (data.status === 'success') {
                placeholder.style.display = 'none';
                canvas.style.display = 'block';
                
                const labels = data.results.map(r => r.k);
                const maeData = data.results.map(r => r.mae);
                const rmseData = data.results.map(r => r.rmse);

                if (window.kChart) window.kChart.destroy();
                
                window.kChart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'RMSE (Lower is Better)',
                                data: rmseData,
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.06)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 2.5,
                                pointRadius: 3,
                                pointBackgroundColor: '#4f46e5'
                            },
                            {
                                label: 'MAE',
                                data: maeData,
                                borderColor: '#0ea5e9',
                                backgroundColor: 'transparent',
                                tension: 0.4,
                                borderDash: [5, 5],
                                borderWidth: 2,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#475569', font: { size: 11 }, boxWidth: 14 } }
                        },
                        scales: {
                            x: { grid: { color: '#eef2f6' }, ticks: { color: '#94a3b8' } },
                            y: { grid: { color: '#eef2f6' }, ticks: { color: '#94a3b8' } }
                        }
                    }
                });
            }
        } catch(e) {
            placeholder.innerHTML = '<span class="text-danger">Gagal menjalankan optimasi.</span>';
        }
    }

    async function runKFoldValidation() {
        const container = document.getElementById('kFoldResults');
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm mb-3" style="color: var(--accent);"></div><p class="text-muted small">Membagi data ke 5 Fold & validasi...</p></div>';

        try {
            const res = await fetch('http://127.0.0.1:8000/admin/cross-validate?folds=5');
            const data = await res.json();
            
            if (data.status === 'success') {
                const m = data.metrics;
                container.innerHTML = `
                    <div class="info-panel">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mean MAE</span>
                            <span class="fw-bold" style="color: var(--info);">${m.mae_mean}</span>
                        </div>
                        <div class="progress" style="height: 4px; background: var(--border);">
                            <div class="progress-bar bg-info" style="width: ${Math.max(10, (2-m.mae_mean)*50)}%"></div>
                        </div>
                    </div>
                    <div class="info-panel">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mean RMSE</span>
                            <span class="fw-bold" style="color: var(--warning);">${m.rmse_mean}</span>
                        </div>
                        <div class="progress" style="height: 4px; background: var(--border);">
                            <div class="progress-bar" style="width: ${Math.max(10, (2-m.rmse_mean)*50)}%; background: var(--warning) !important;"></div>
                        </div>
                    </div>
                    <div class="mt-2 row g-2">
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background: var(--surface-2);">
                                <span class="text-muted d-block" style="font-size:0.62rem;">MAE Std Dev</span>
                                <span class="small fw-bold">±${m.mae_std}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background: var(--surface-2);">
                                <span class="text-muted d-block" style="font-size:0.62rem;">RMSE Std Dev</span>
                                <span class="small fw-bold">±${m.rmse_std}</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success small p-2 mt-2 mb-0" style="font-size:0.68rem;border-radius:10px;background:var(--success-bg);border-color:var(--success-border);color:var(--success);">
                        <i class="fas fa-check-circle me-1"></i> Validated on ${m.folds} Folds successfully.
                    </div>
                `;
            } else {
                container.innerHTML = `<div class="alert alert-danger small" style="border-radius:10px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger);">${data.message}</div>`;
            }
        } catch(e) {
            container.innerHTML = '<div class="alert alert-danger small" style="border-radius:10px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger);">Gagal menghubungi server AI.</div>';
        }
    }
</script>
