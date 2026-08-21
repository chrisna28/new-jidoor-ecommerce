<!-- 
    JI-DOOR PURE CF INTELLIGENCE DASHBOARD v5.4
    ============================================
-->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-main-content">
    <?php if (!$stats || (isset($stats->status) && $stats->status === 'error')): ?>
    <div class="admin-card text-center py-5">
        <i class="fas fa-exclamation-triangle text-danger fs-1 mb-3"></i>
        <h4 class="text-white fw-bold">Gagal Menghubungkan ke Mesin AI</h4>
        <p class="text-muted"><?= $stats->message ?? 'Pastikan server Python (port 8000) sudah aktif.' ?></p>
    </div>
    <?php elseif (isset($stats->status) && $stats->status === 'empty'): ?>
    <div class="admin-card text-center py-5">
        <i class="fas fa-info-circle text-info fs-1 mb-3"></i>
        <h4 class="text-white fw-bold">Belum Ada Data Rating</h4>
        <p class="text-muted">Mesin AI membutuhkan data rating untuk mulai bekerja.</p>
    </div>
    <?php else: ?>
    <!-- Main Dashboard Header -->
    <div class="d-flex justify-content-between align-items-end mb-5 pb-4 border-bottom border-white border-opacity-10">
        <div>
            <div class="d-flex align-items-center mb-2">
                <div class="bg-warning rounded-3 p-2 me-3 ai-float shadow-lg">
                    <i class="fa-solid fa-microchip text-dark fs-4"></i>
                </div>
                <div>
                    <h3 class="text-white fw-bold mb-0">Recommendation Engine</h3>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="text-muted small">Pure Collaborative Filtering v5.3</span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-0 small" style="font-size: 0.6rem;">
                            <i class="fa-solid fa-microchip me-1"></i> KNN-OPTIMIZED
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0 small ai-pulse" style="font-size: 0.6rem;">
                            <i class="fa-solid fa-circle me-1" style="font-size: 0.4rem;"></i> LIVE
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-dark border-secondary border-opacity-25 rounded-3 px-4 py-2 small d-flex align-items-center hover-lift" onclick="location.reload()">
                <i class="fa-solid fa-arrows-rotate me-2 text-muted"></i> <span class="text-white-50">Sync Data</span>
            </button>
            <button class="btn btn-warning rounded-3 px-4 py-2 small fw-bold shadow-lg d-flex align-items-center hover-lift" onclick="refreshAICache()">
                <i class="fa-solid fa-brain me-2"></i> Retrain Model
            </button>
        </div>
    </div>

    <?php if(!empty($stats->warnings)): ?>
        <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-20 text-warning rounded-4 mb-5 small shadow-sm">
            <h6 class="fw-bold small mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> System Warnings:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach($stats->warnings as $w): ?>
                    <li><?= $w ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $d = isset($stats->data) ? $stats->data : (isset($stats) ? $stats : null); ?>
    
    <!-- Main Matrix Stats -->
    <div class="row g-4 mb-5">
        <?php 
        $matrix = [
            ['label' => 'Total Users', 'value' => $d->total_users ?? 0, 'icon' => 'fa-users', 'color' => '#f97316'],
            ['label' => 'Total Products', 'value' => $d->total_items ?? 0, 'icon' => 'fa-boxes-stacked', 'color' => '#3b82f6'],
            ['label' => 'Data Signals', 'value' => $d->total_signals ?? 0, 'icon' => 'fa-signal', 'color' => '#10b981'],
            ['label' => 'Data Density', 'value' => ($d->density_score ?? 0) . '%', 'icon' => 'fa-database', 'color' => '#8b5cf6']
        ];
        foreach ($matrix as $m): ?>
        <div class="col-md-3">
            <div class="admin-card h-100 shadow-lg border-0 border-start border-5" style="border-left-color: <?= $m['color'] ?> !important; background: rgba(22, 27, 34, 0.4);">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas <?= $m['icon'] ?> me-2 fs-5" style="color: <?= $m['color'] ?>;"></i>
                    <span class="text-muted small fw-bold text-uppercase ls-1 opacity-50" style="font-size: 0.65rem;"><?= $m['label'] ?></span>
                </div>
                <h2 class="text-white fw-bold mb-0 glow-text" style="color: <?= $m['color'] ?> !important;"><?= $m['value'] ?></h2>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Prediction Accuracy Section (Bab 4 Skripsi) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="admin-card h-100 shadow-lg border-0 border-start border-5" style="border-left-color: #f43f5e !important; background: rgba(22, 27, 34, 0.4);">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-bullseye me-2 fs-5" style="color: #f43f5e;"></i>
                    <span class="text-muted small fw-bold text-uppercase ls-1 opacity-50" style="font-size: 0.65rem;">Mean Absolute Error (MAE)</span>
                </div>
                <h2 class="text-white fw-bold mb-0 glow-text" style="color: #f43f5e !important;"><?= $d->mae ?? '0.0000' ?></h2>
                <p class="text-muted small mb-0 mt-1 opacity-50">Rata-rata selisih rating prediksi vs aktual</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card h-100 shadow-lg border-0 border-start border-5" style="border-left-color: #ec4899 !important; background: rgba(22, 27, 34, 0.4);">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-chart-line me-2 fs-5" style="color: #ec4899;"></i>
                    <span class="text-muted small fw-bold text-uppercase ls-1 opacity-50" style="font-size: 0.65rem;">Root Mean Square Error (RMSE)</span>
                </div>
                <h2 class="text-white fw-bold mb-0 glow-text" style="color: #ec4899 !important;"><?= $d->rmse ?? '0.0000' ?></h2>
                <p class="text-muted small mb-0 mt-1 opacity-50">Akar rata-rata kuadrat kesalahan (Stabilitas)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card h-100 shadow-lg border-0 border-start border-5" style="border-left-color: #8b5cf6 !important; background: rgba(22, 27, 34, 0.4);">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-vial me-2 fs-5" style="color: #8b5cf6;"></i>
                    <span class="text-muted small fw-bold text-uppercase ls-1 opacity-50" style="font-size: 0.65rem;">Evaluation Samples</span>
                </div>
                <h2 class="text-white fw-bold mb-0 glow-text" style="color: #8b5cf6 !important;"><?= $d->eval_samples ?? 0 ?></h2>
                <p class="text-muted small mb-0 mt-1 opacity-50">Jumlah data uji (Time-based Splitting)</p>
            </div>
        </div>
    </div>

    <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-20 text-info rounded-4 mb-5 small shadow-sm d-flex align-items-center gap-3">
        <div class="bg-info bg-opacity-20 rounded-circle p-2">
            <i class="fa-solid fa-bolt-lightning"></i>
        </div>
        <div>
            <span class="fw-bold d-block">Engine Optimization Active: Sparse Matrix + KNN + Mean-Centering</span>
            <span class="opacity-75">Model sekarang menggunakan normalisasi rata-rata (Mean-Centering) untuk menangani data kosong secara lebih akurat dan pencarian tetangga tercepat menggunakan K-Nearest Neighbors.</span>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="row g-4 mb-5 d-flex align-items-stretch">
        <!-- Model Confidence -->
        <div class="col-lg-4 d-flex">
            <div class="admin-card w-100 p-4 mb-0 d-flex flex-column align-items-center justify-content-center" style="min-height: 400px; position: relative;">
                <h6 class="text-muted small fw-bold text-uppercase ls-1 w-100 mb-0" style="position: absolute; top: 28px; left: 28px;">Model Confidence</h6>
                
                <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1 w-100 py-4">
                    <div class="position-relative">
                        <svg width="280" height="280" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,0.02)" stroke-width="5"></circle>
                            <circle cx="50" cy="50" r="46" fill="none" stroke="url(#gaugeGradient)" stroke-width="8" 
                                    stroke-dasharray="<?= ($d->final_confidence ?? 0) * 2.89 ?>, 289" 
                                    stroke-linecap="round" transform="rotate(-90 50 50)"
                                    style="transition: stroke-dasharray 2s ease-out; filter: drop-shadow(0 0 15px rgba(249, 115, 22, 0.4));"></circle>
                            <defs>
                                <linearGradient id="gaugeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#f97316;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#fbbf24;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h1 class="text-white fw-bold mb-0" style="font-size: 3.5rem; letter-spacing: -2px;"><?= $d->final_confidence ?? 0 ?><small class="fs-4 opacity-50" style="letter-spacing: 0;">%</small></h1>
                            <span class="text-success small fw-bold ls-1" style="font-size: 0.8rem; letter-spacing: 2px;">OPTIMIZED</span>
                        </div>
                    </div>
                </div>

                <div class="w-100 pt-3 border-top border-white border-opacity-5">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px;">Similarity</span>
                            <span class="text-info fw-bold small"><?= $d->sim_strength ?? 0 ?>%</span>
                            <div class="progress mt-1" style="height: 3px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-info" style="width: <?= $d->sim_strength ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <span class="text-muted d-block" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px;">Coverage</span>
                            <span class="text-success fw-bold small"><?= $d->coverage_score ?? 0 ?>%</span>
                            <div class="progress mt-1 ms-auto" style="height: 3px; width: 100%; background: rgba(255,255,255,0.05);">
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
            <div class="admin-card flex-fill p-4 mb-0 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-muted small fw-bold text-uppercase mb-0 ls-1">Signal Distribution (Data Sources)</h6>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1 small"><?= $d->total_signals ?? 0 ?> total</span>
                </div>
                <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <?php 
                    $source_dist = isset($d->source_distribution) ? (array)$d->source_distribution : [];
                    $signal_colors = [
                        'explicit' => ['label' => 'Rating (Explicit)', 'color' => '#f97316', 'icon' => 'fa-star'],
                        'purchase' => ['label' => 'Purchase', 'color' => '#10b981', 'icon' => 'fa-shopping-bag'],
                        'wishlist' => ['label' => 'Wishlist', 'color' => '#ec4899', 'icon' => 'fa-heart'],
                        'cart' => ['label' => 'Cart', 'color' => '#3b82f6', 'icon' => 'fa-cart-shopping'],
                        'view' => ['label' => 'Product Views', 'color' => '#8b5cf6', 'icon' => 'fa-eye'],
                    ];
                    $total_signals = array_sum($source_dist) ?: 1;
                    foreach ($signal_colors as $key => $info):
                        $count = $source_dist[$key] ?? 0;
                        $pct = round(($count / $total_signals) * 100, 1);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white small"><i class="fas <?= $info['icon'] ?> me-2" style="color: <?= $info['color'] ?>;"></i><?= $info['label'] ?></span>
                            <span class="text-muted small"><?= $count ?> <span class="opacity-50">(<?= $pct ?>%)</span></span>
                        </div>
                        <div class="progress" style="height: 5px; background: rgba(255,255,255,0.03);">
                            <div class="progress-bar" style="width: <?= $pct ?>%; background: <?= $info['color'] ?>; border-radius: 10px; box-shadow: 0 0 8px <?= $info['color'] ?>40;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Active vs Total -->
            <div class="admin-card flex-fill p-4 mb-0">
                <h6 class="text-muted small fw-bold text-uppercase mb-4 ls-1">Active Coverage</h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(249,115,22,0.05); border: 1px solid rgba(249,115,22,0.15);">
                            <span class="text-muted d-block small" style="font-size: 0.65rem;">Active Users (in Model)</span>
                            <span class="text-warning fw-bold fs-4"><?= $d->active_users ?? 0 ?></span>
                            <span class="text-muted small"> / <?= $d->total_users ?? 0 ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(59,130,246,0.05); border: 1px solid rgba(59,130,246,0.15);">
                            <span class="text-muted d-block small" style="font-size: 0.65rem;">Active Products (in Model)</span>
                            <span class="fw-bold fs-4" style="color: #3b82f6;"><?= $d->active_products ?? 0 ?></span>
                            <span class="text-muted small"> / <?= $d->total_items ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Similarity Pairs Section -->
    <div class="admin-card mb-5 p-0 overflow-hidden">
        <div class="p-4 border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="text-white fw-bold mb-1">Item-Item Cosine Similarity</h5>
                <p class="text-muted small mb-0">Top produk yang sering muncul bersamaan dalam perilaku pengguna</p>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">Avg Strength</span>
                <span class="text-warning fw-bold fs-5"><?= $d->sim_strength ?? 0 ?>%</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase ls-1">
                        <th class="ps-4 py-3 border-0" style="width: 35%;">Product A</th>
                        <th class="py-3 border-0 text-center" style="width: 20%;">Cosine Similarity</th>
                        <th class="py-3 border-0 text-center" style="width: 15%;">Co-Occur</th>
                        <th class="py-3 border-0 text-center" style="width: 10%;">Penalty</th>
                        <th class="pe-4 py-3 border-0 text-end" style="width: 20%;">Product B</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($d->top_similarity_pairs)): foreach ($d->top_similarity_pairs as $p): ?>
                    <tr class="border-top border-white border-opacity-5">
                        <td class="ps-4 py-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 rounded-3 p-2 me-3">
                                    <i class="fas fa-box text-white-50"></i>
                                </div>
                                <span class="text-white fw-medium"><?= $p->p1 ?></span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex flex-column align-items-center">
                                <span class="px-3 py-1 rounded-pill small fw-bold mb-1" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                    <?= number_format($p->score * 100, 1) ?>%
                                </span>
                                <div class="d-flex gap-1">
                                    <?php for($i=0; $i<5; $i++): ?>
                                        <div style="width: 10px; height: 3px; border-radius: 2px; background: <?= $i < ($p->score * 5) ? '#10b981' : 'rgba(255,255,255,0.05)' ?>"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center text-white"><?= $p->co_occurrence ?? 0 ?></td>
                        <td class="text-center text-warning"><?= number_format(($p->penalty ?? 0) * 100, 0) ?>%</td>
                        <td class="pe-4 py-4">
                            <div class="d-flex align-items-center justify-content-end">
                                <span class="text-white fw-medium me-3"><?= $p->p2 ?></span>
                                <div class="bg-secondary bg-opacity-10 rounded-3 p-2">
                                    <i class="fas fa-box text-white-50"></i>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-layer-group fs-1 mb-3 opacity-10"></i>
                        <p>No significant correlations found yet. Keep collecting data.</p>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Academic Experiments (Bab 4 Skripsi) -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5 pt-4">
        <div>
            <h5 class="text-white fw-bold mb-1"><i class="fas fa-microscope me-2 text-warning"></i>Eksperimen Akademik (Bab 4)</h5>
            <p class="text-muted small mb-0">Uji coba parameter model untuk menemukan konfigurasi optimal (Optimasi K & K-Fold)</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="runKFoldValidation()">
                <i class="fas fa-vial me-1"></i> Run K-Fold CV
            </button>
            <button class="btn btn-sm btn-warning rounded-pill px-3 fw-bold shadow-lg" onclick="runKOptimization()">
                <i class="fas fa-chart-line me-1"></i> Optimize K
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- K-Optimization Chart -->
        <div class="col-lg-8">
            <div class="admin-card h-100 p-4" id="kOptContainer">
                <h6 class="text-muted small fw-bold text-uppercase mb-4 ls-1">K-Optimization Graph (K vs RMSE)</h6>
                <div style="height: 300px; width: 100%;" class="d-flex align-items-center justify-content-center border border-white border-opacity-5 rounded-4 bg-dark bg-opacity-50">
                    <canvas id="kOptChart"></canvas>
                    <div id="kOptPlaceholder" class="text-muted small opacity-50">Klik "Optimize K" untuk memulai simulasi perbandingan nilai K.</div>
                </div>
            </div>
        </div>

        <!-- K-Fold Results -->
        <div class="col-lg-4">
            <div class="admin-card h-100 p-4" id="kFoldContainer">
                <h6 class="text-muted small fw-bold text-uppercase mb-4 ls-1">K-Fold Cross Validation</h6>
                <div id="kFoldResults" class="d-flex flex-column gap-3">
                    <div class="text-center py-5 text-muted small opacity-50">Hasil K-Fold akan muncul di sini setelah eksekusi.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CF Calculation Detail Section (Unwrapped) -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5 pt-4">
        <div>
            <h5 class="text-white fw-bold mb-1"><i class="fas fa-calculator me-2 text-info"></i>Detail Perhitungan CF</h5>
            <p class="text-muted small mb-0">Step-by-step proses Collaborative Filtering: dari data mentah hingga rekomendasi final</p>
        </div>
        <div class="d-flex gap-2">
            <select id="cfDetailUserSelect" class="form-select form-select-sm bg-dark border-secondary border-opacity-25 text-white rounded-pill px-3" style="width: 200px;">
                <?php if (!empty($user_list)): ?>
                    <?php foreach ($user_list as $u): ?>
                        <option value="<?= $u->id ?>">User #<?= $u->id ?> — <?= htmlspecialchars($u->username) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button class="btn btn-sm btn-info rounded-pill px-3 fw-bold" onclick="loadCFDetail()"><i class="fas fa-search-plus me-1"></i> Analisis</button>
        </div>
    </div>
    
    <div id="cfDetailContainer" class="mt-4">
        <div class="text-center py-5 text-muted small admin-card">Pilih user dan klik "Analisis" untuk melihat detail perhitungan CF.</div>
    </div>

<?php endif; ?>

<style>
    .ls-1 { letter-spacing: 1.5px; }
    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.3) !important; }
    .progress { border-radius: 10px; }
    .glow-text { text-shadow: 0 0 20px currentColor; }
</style>

<script>
    function refreshAICache() {
        const btn = document.querySelector('.btn-warning');
        const icon = btn.querySelector('i');
        btn.classList.add('disabled');
        icon.classList.add('fa-spin');
        fetch('http://127.0.0.1:8000/cache/refresh', { method: 'POST' })
            .then(res => res.json())
            .then(data => { alert('Success: ' + data.message); location.reload(); })
            .catch(err => { alert('Error refreshing cache.'); btn.classList.remove('disabled'); icon.classList.remove('fa-spin'); });
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
                const colors = ['#f97316','#3b82f6','#10b981','#8b5cf6','#ec4899','#06b6d4'];
                const c = colors[idx % colors.length];
                
                html += `<div class="mb-4 p-4 rounded-4" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);">`;
                html += `<div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-circle me-3 d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:${c};font-size:0.8rem;font-weight:bold;">${idx+1}</span>
                    <div><h6 class="text-white fw-bold mb-0 small">${step.title}</h6>
                    <span class="text-muted" style="font-size:0.7rem;">${step.description}</span></div></div>`;

                if (step.formula) {
                    html += `<div class="mb-3 px-3 py-2 rounded-3" style="background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);font-family:monospace;font-size:0.75rem;color:#c4b5fd;">${step.formula}</div>`;
                }

                if (step.data && step.data.length > 0) {
                    html += `<div class="table-responsive" style="max-height:350px;overflow-y:auto;">`;
                    html += `<table class="table table-dark table-sm table-hover align-middle mb-0" style="font-size:0.75rem;">`;
                    
                    // Header
                    const cols = Object.keys(step.data[0]);
                    html += '<thead class="sticky-top" style="z-index:1;"><tr class="text-uppercase" style="font-size:0.6rem;letter-spacing:1px;">';
                    cols.forEach(col => {
                        let label = col.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
                        html += `<th class="border-0 py-2 text-muted" style="background:#0d1117;">${label}</th>`;
                    });
                    html += '</tr></thead><tbody>';
                    
                    // Rows
                    step.data.forEach(row => {
                        html += '<tr>';
                        cols.forEach(col => {
                            let val = row[col];
                            let cls = '';
                            if (typeof val === 'number' && col !== 'user_id' && col !== 'product_id' && col !== 'other_user_id' && col !== 'source_product_id' && col !== 'similar_product_id') {
                                cls = val > 0 ? 'text-success' : 'text-muted';
                                if (col.includes('similarity') || col.includes('pct') || col.includes('score')) {
                                    if (val > 0.5) cls = 'text-warning fw-bold';
                                    else if (val > 0) cls = 'text-info';
                                }
                            }
                            if (col === 'origin') {
                                let bg = '#6b7280';
                                if (val && val.includes('BEST MATCH')) bg = '#10b981';
                                else if (val && val.includes('FOR YOU')) bg = '#3b82f6';
                                else if (val && val.includes('STYLE MATCH')) bg = '#06b6d4';
                                else if (val && (val.includes('HOT HITS') || val.includes('BEST SELLER') || val.includes('TRENDING'))) bg = '#f97316';
                                else if (val && val.includes('NEW ARRIVAL')) bg = '#8b5cf6';
                                val = `<span class="badge rounded-pill px-2 py-1" style="background:${bg};font-size:0.6rem;">${val}</span>`;
                            }
                            if (col === 'source') {
                                let bg2 = '#6b7280';
                                if (val === 'explicit') bg2 = '#f97316';
                                else if (val === 'purchase') bg2 = '#10b981';
                                else if (val === 'wishlist') bg2 = '#ec4899';
                                else if (val === 'cart') bg2 = '#3b82f6';
                                else if (val === 'view') bg2 = '#8b5cf6';
                                val = `<span class="badge rounded-pill px-2 py-1" style="background:${bg2};font-size:0.6rem;">${val}</span>`;
                            }
                            html += `<td class="border-0 ${cls}">${val !== null && val !== undefined ? val : '-'}</td>`;
                        });
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += `<div class="text-center text-muted small py-3 opacity-50"><i class="fas fa-inbox me-1"></i> Tidak ada data</div>`;
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
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#f97316'
                            },
                            {
                                label: 'MAE',
                                data: maeData,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                            legend: { labels: { color: 'rgba(255,255,255,0.7)', font: { size: 10 } } }
                        },
                        scales: {
                            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)' } },
                            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)' } }
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
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-warning mb-3"></div><p class="text-muted small">Membagi data ke 5 Fold & validasi...</p></div>';

        try {
            const res = await fetch('http://127.0.0.1:8000/admin/cross-validate?folds=5');
            const data = await res.json();
            
            if (data.status === 'success') {
                const m = data.metrics;
                container.innerHTML = `
                    <div class="p-3 rounded-4 bg-dark bg-opacity-50 border border-white border-opacity-5">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mean MAE</span>
                            <span class="text-info fw-bold">${m.mae_mean}</span>
                        </div>
                        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar bg-info" style="width: ${Math.max(10, (2-m.mae_mean)*50)}%"></div>
                        </div>
                    </div>
                    <div class="p-3 rounded-4 bg-dark bg-opacity-50 border border-white border-opacity-5">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mean RMSE</span>
                            <span class="text-warning fw-bold">${m.rmse_mean}</span>
                        </div>
                        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar bg-warning" style="width: ${Math.max(10, (2-m.rmse_mean)*50)}%"></div>
                        </div>
                    </div>
                    <div class="mt-2 row g-2">
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-secondary bg-opacity-10 text-center">
                                <span class="text-muted d-block" style="font-size:0.6rem;">MAE Std Dev</span>
                                <span class="text-white small fw-bold">±${m.mae_std}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 bg-secondary bg-opacity-10 text-center">
                                <span class="text-muted d-block" style="font-size:0.6rem;">RMSE Std Dev</span>
                                <span class="text-white small fw-bold">±${m.rmse_std}</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-10 text-success small p-2 mt-2 mb-0" style="font-size:0.65rem;">
                        <i class="fas fa-check-circle me-1"></i> Validated on ${m.folds} Folds successfully.
                    </div>
                `;
            } else {
                container.innerHTML = `<div class="alert alert-danger small">${data.message}</div>`;
            }
        } catch(e) {
            container.innerHTML = '<div class="alert alert-danger small">Gagal menghubungi server AI.</div>';
        }
    }
</script>
