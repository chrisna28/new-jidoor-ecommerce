<!--
    JI-DOOR RECOMMENDATION DASHBOARD v5.5 (redesign)
    ==================================================
    Urutan section (logis, atas → bawah):
      1. Header + aksi
      2. Panel status pelatihan (muncul saat retrain)
      3. KPI inti (4 kartu)
      4. Kualitas Model — evaluasi ranking + kesehatan
      5. Preview Rekomendasi Personal (interaktif)
      6. Parameter Aktif + Distribusi Sinyal
      7. Similaritas Produk (visualisasi CF)
    Bagian akademik (K-Fold, K-Optimization, CF-detail) dihapus.
-->
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
            <p>Mesin AI membutuhkan data interaksi untuk mulai bekerja.</p>
        </div>
    </div>
    <?php else: ?>
    <?php $d = isset($stats->data) ? $stats->data : (isset($stats) ? $stats : null); ?>
    <?php $ap = isset($d->active_params) ? $d->active_params : null; ?>
    <?php $rm = isset($d->ranking_metrics) ? $d->ranking_metrics : null; ?>

    <!-- 1. Header -->
    <div class="page-head d-flex justify-content-between align-items-end flex-wrap gap-3 pb-4" style="border-bottom: 1px solid var(--border);">
        <div class="d-flex align-items-center">
            <div class="rounded-3 p-2 me-3 ai-float d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background: var(--accent-soft); color: var(--accent); font-size: 1.35rem;">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1">Mesin Rekomendasi</h3>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small">Pure CF Hybrid v5.5</span>
                    <span class="badge-neutral"><i class="fa-solid fa-microchip me-1"></i> AUTO-TUNED</span>
                    <span class="badge-neutral"><i class="fa-solid fa-tags me-1"></i> LAPISAN VARIAN</span>
                    <span class="badge-neutral ai-pulse" style="color: var(--success); border-color: var(--success-border);" id="liveBadge"><i class="fa-solid fa-circle me-1" style="font-size: 0.45rem;"></i> LIVE</span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-admin-outline d-flex align-items-center" onclick="location.reload()">
                <i class="fa-solid fa-arrows-rotate me-2"></i> Sinkron Data
            </button>
            <button id="btnRetrain" class="btn btn-admin-primary d-flex align-items-center" onclick="refreshAICache()">
                <i class="fa-solid fa-brain me-2"></i> <span id="btnRetrainLabel">Latih Ulang Model</span>
            </button>
        </div>
    </div>

    <!-- 2. Panel Status Pelatihan -->
    <div class="admin-card mb-4 p-0" id="trainingPanel" style="display:none;">
        <div class="p-4">
            <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center train-orb" id="trainOrb">
                    <i class="fa-solid fa-brain" id="trainOrbIcon"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1" id="trainingTitle">Melatih ulang model...</h6>
                    <span class="text-muted small" id="trainingSubtext">Mengambil data interaksi terbaru dari database</span>
                </div>
                <div class="text-end">
                    <span class="fw-bold num" id="trainingElapsed" style="color: var(--accent); font-size: 1.05rem;">0.0s</span>
                    <span class="text-muted small d-block" style="font-size: .68rem;"> waktu berjalan</span>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="train-step p-3 rounded-3" id="step0">
                        <span class="train-step-icon"><i class="fa-solid fa-database"></i></span>
                        <div>
                            <div class="fw-semibold small">Muat Data Interaksi</div>
                            <div class="text-muted" style="font-size: .68rem;">Rating · order · view · like</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="train-step p-3 rounded-3" id="step1">
                        <span class="train-step-icon"><i class="fa-solid fa-diagram-project"></i></span>
                        <div>
                            <div class="fw-semibold small">Hitung Similarity</div>
                            <div class="text-muted" style="font-size: .68rem;">User-KNN + Item-Based CF</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="train-step p-3 rounded-3" id="step2">
                        <span class="train-step-icon"><i class="fa-solid fa-tags"></i></span>
                        <div>
                            <div class="fw-semibold small">Segarkan Lapisan Varian</div>
                            <div class="text-muted" style="font-size: .68rem;">Saran warna &amp; ukuran personal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. KPI Inti -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card kpi-card hover-lift">
                <div class="kpi-top">
                    <span class="kpi-label">Total Pengguna</span>
                    <span class="kpi-tile" style="background:var(--warning-bg);color:var(--warning);"><i class="fas fa-users"></i></span>
                </div>
                <div class="kpi-value"><?= $d->total_users ?? 0 ?></div>
                <div class="kpi-foot"><i class="fas fa-user-check" style="color:var(--text-3);font-size:.72rem;"></i> <?= $d->active_users ?? 0 ?> aktif dalam model</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card kpi-card hover-lift">
                <div class="kpi-top">
                    <span class="kpi-label">Total Produk</span>
                    <span class="kpi-tile" style="background:var(--accent-soft);color:var(--accent);"><i class="fas fa-boxes-stacked"></i></span>
                </div>
                <div class="kpi-value"><?= $d->total_items ?? 0 ?></div>
                <div class="kpi-foot"><i class="fas fa-cubes" style="color:var(--text-3);font-size:.72rem;"></i> <?= $d->active_products ?? 0 ?> dalam matriks CF</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card kpi-card hover-lift">
                <div class="kpi-top">
                    <span class="kpi-label">Sinyal Data</span>
                    <span class="kpi-tile" style="background:var(--success-bg);color:var(--success);"><i class="fas fa-signal"></i></span>
                </div>
                <div class="kpi-value"><?= $d->total_signals ?? 0 ?></div>
                <div class="kpi-foot"><i class="fas fa-table-cells" style="color:var(--text-3);font-size:.72rem;"></i> pasangan user × produk terisi</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card kpi-card hover-lift">
                <div class="kpi-top">
                    <span class="kpi-label">Kerapatan Data</span>
                    <span class="kpi-tile" style="background:var(--info-bg);color:var(--info);"><i class="fas fa-database"></i></span>
                </div>
                <div class="kpi-value"><?= $ap ? ($ap->density_pct ?? 0) : 0 ?>%</div>
                <div class="kpi-foot"><i class="fas fa-layer-group" style="color:var(--text-3);font-size:.72rem;"></i> skala data <?= $ap ? ucfirst($ap->scale_class ?? '-') : '-' ?></div>
            </div>
        </div>
    </div>

    <!-- 4. Kualitas Model — Evaluasi Ranking + Kesehatan -->
    <div class="admin-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-chart-line me-2" style="color: var(--accent);"></i>Kualitas Model</h5>
                <p class="text-muted small mb-0">Evaluasi leave-last-out + indikator kesehatan model</p>
            </div>
            <?php if ($rm && !empty($rm->eligible)): ?>
                <span class="badge-neutral"><i class="fa-solid fa-flask me-1"></i> K = <?= $rm->k ?> · <?= $rm->eval_users ?> user diuji</span>
            <?php endif; ?>
        </div>

        <div class="row g-3">
            <!-- Evaluasi Ranking -->
            <div class="col-lg-7">
                <?php if ($rm && !empty($rm->eligible)): ?>
                <div class="row g-3">
                    <?php
                    $metric_tiles = [
                        ['label' => 'Precision@K', 'value' => $rm->precision_at_k, 'icon' => 'fa-bullseye', 'color' => '#4f46e5', 'fmt' => 'rank', 'hint' => 'Proporsi rekomendasi top-K yang relevan'],
                        ['label' => 'Recall@K', 'value' => $rm->recall_at_k, 'icon' => 'fa-circle-check', 'color' => '#059669', 'fmt' => 'rank', 'hint' => 'Item relevan yang berhasil ditemukan (hit-rate)'],
                        ['label' => 'NDCG@K', 'value' => $rm->ndcg_at_k, 'icon' => 'fa-ranking-star', 'color' => '#db2777', 'fmt' => 'rank', 'hint' => 'Kualitas peringkat — makin tinggi makin tepat urutan'],
                    ];
                    if (!empty($d->eval_eligible)) {
                        $metric_tiles[] = ['label' => 'MAE', 'value' => $d->mae, 'icon' => 'fa-minus', 'color' => '#0284c7', 'fmt' => 'rank', 'hint' => 'Rata-rata galat prediksi rating (' . ($d->eval_samples ?? 0) . ' sampel)'];
                        $metric_tiles[] = ['label' => 'RMSE', 'value' => $d->rmse, 'icon' => 'fa-wave-square', 'color' => '#7c3aed', 'fmt' => 'rank', 'hint' => 'Galat prediksi rating (menekan outlier)'];
                    }
                    foreach ($metric_tiles as $mt): ?>
                    <div class="col-6 col-md-4<?= count($metric_tiles) > 3 ? ' col-xl' : '' ?>">
                        <div class="metric-tile" style="border-left: 3px solid <?= $mt['color'] ?>;">
                            <div class="kpi-top">
                                <span class="kpi-label"><?= $mt['label'] ?></span>
                                <i class="fas <?= $mt['icon'] ?>" style="color: <?= $mt['color'] ?>;"></i>
                            </div>
                            <div class="kpi-value"><?= $mt['value'] !== null ? number_format((float)$mt['value'], 4) : '-' ?></div>
                            <div class="text-muted" style="font-size: .66rem; line-height: 1.35;"><?= $mt['hint'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="d-flex align-items-center gap-3 h-100 p-3 rounded-3" style="background: var(--surface-2);">
                    <i class="fa-solid fa-flask" style="color: var(--text-3); font-size: 1.5rem;"></i>
                    <div>
                        <div class="small fw-semibold">Evaluasi ranking belum tersedia</div>
                        <div class="text-muted" style="font-size: .75rem;"><?= $rm->reason ?? 'Butuh lebih banyak data interaksi.' ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Kesehatan Model -->
            <div class="col-lg-5">
                <?php
                $conf = isset($d->final_confidence) ? (float)$d->final_confidence : 0;
                $cov  = isset($d->coverage_score) ? (float)$d->coverage_score : 0;
                $sim  = isset($d->sim_strength) ? (float)$d->sim_strength : 0;
                $health_bars = [
                    ['label' => 'Confidence Model', 'value' => $conf, 'suffix' => '%', 'color' => ($conf > 70 ? '#059669' : ($conf > 40 ? '#d97706' : '#dc2626')), 'icon' => 'fa-gauge-high'],
                    ['label' => 'Coverage (user terjangkau)', 'value' => $cov, 'suffix' => '%', 'color' => '#0284c7', 'icon' => 'fa-user-check'],
                    ['label' => 'Kekuatan Similarity', 'value' => $sim, 'suffix' => '%', 'color' => '#7c3aed', 'icon' => 'fa-link'],
                ];
                ?>
                <div class="d-flex flex-column gap-3 h-100 justify-content-center">
                    <?php foreach ($health_bars as $hb): ?>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted"><i class="fas <?= $hb['icon'] ?> me-2" style="color: <?= $hb['color'] ?>;"></i><?= $hb['label'] ?></span>
                            <span class="fw-bold small" style="color: <?= $hb['color'] ?>;"><?= number_format($hb['value'], 1) ?><?= $hb['suffix'] ?></span>
                        </div>
                        <div class="progress" style="height: 7px; background: var(--surface-2);">
                            <div class="progress-bar" style="width: <?= min(100, $hb['value']) ?>%; background: <?= $hb['color'] ?>; border-radius: 10px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Preview Rekomendasi Personal -->
    <div class="admin-card mb-4 p-0">
        <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3" style="border-bottom: 1px solid var(--border);">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-wand-magic-sparkles me-2" style="color: var(--accent);"></i>Preview Rekomendasi Personal</h5>
                <p class="text-muted small mb-0">Output mesin per pengguna — barang + saran varian warna &amp; ukuran, deterministik selama data tidak berubah</p>
            </div>
            <div class="d-flex gap-2">
                <select id="recPreviewUserSelect" class="form-select form-control-admin form-select-sm" style="width: 220px;">
                    <?php if (!empty($user_list)): ?>
                        <?php foreach ($user_list as $u): ?>
                            <option value="<?= $u->id ?>">Pengguna #<?= $u->id ?> — <?= htmlspecialchars($u->username) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button class="btn btn-sm btn-admin-primary rounded-pill px-3" onclick="loadRecPreview()"><i class="fas fa-eye me-1"></i> Tampilkan</button>
            </div>
        </div>
        <div class="p-4" id="recPreviewContainer">
            <div class="empty-state py-4">
                <div class="empty-icon"><i class="fas fa-cart-shopping"></i></div>
                <p>Pilih pengguna lalu klik "Tampilkan" untuk melihat hasil rekomendasi.</p>
            </div>
        </div>
    </div>

    <!-- 6. Parameter Aktif + Distribusi Sinyal -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-lg-5">
            <div class="admin-card h-100">
                <h6 class="small fw-bold text-uppercase mb-4 ls-1 text-muted">Parameter Aktif Model</h6>
                <?php if ($ap): ?>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Kelas Skala Data</span>
                        <span class="badge-neutral"><?= ucfirst($ap->scale_class ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Matriks Model</span>
                        <span class="fw-semibold small"><?= ($ap->n_users ?? 0) ?> user × <?= ($ap->n_items ?? 0) ?> produk</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Alpha (bobot User-CF)</span>
                        <span class="fw-bold small" style="color: var(--info);"><?= $ap->alpha_user_cf ?? '-' ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Threshold High-Confidence</span>
                        <span class="fw-bold small" style="color: var(--warning);"><?= $ap->base_threshold ?? '-' ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">K Tetangga KNN</span>
                        <span class="fw-bold small" style="color: #7c3aed;"><?= min(($ap->knn_k ?? 0), ($ap->n_users ?? 0)) ?: '-' ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Lambda Shrinkage (λ)</span>
                        <span class="fw-bold small" style="color: #0f766e;"><?= $ap->shrinkage_lambda ?? 3 ?></span>
                    </div>
                    <div class="pt-3" style="border-top: 1px dashed var(--border);">
                        <span class="text-muted" style="font-size: .68rem;"><i class="fa-solid fa-circle-info me-1"></i> Parameter dipilih otomatis mengikuti skala data — skor = alpha·UserCF + (1−alpha)·ItemCF.</span>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-muted small">Parameter tidak tersedia.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="admin-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="small fw-bold text-uppercase mb-0 ls-1 text-muted">Distribusi Sinyal (Sumber Data)</h6>
                    <span class="badge-neutral"><?= $raw_total ?? 0 ?> sinyal</span>
                </div>
                <div class="text-muted mb-3" style="font-size: .68rem; line-height: 1.4;"><i class="fa-solid fa-circle-info me-1"></i> Setiap interaksi dihitung — satu user×produk dapat menyumbang lebih dari satu sinyal (akumulasi).</div>
                <?php 
                $source_dist = isset($d->source_distribution) ? (array)$d->source_distribution : [];
                $raw_total = array_sum($source_dist) ?: 0;
                $signal_colors = [
                    'explicit' => ['label' => 'Rating (Eksplisit)', 'color' => '#4f46e5', 'icon' => 'fa-star'],
                    'purchase' => ['label' => 'Pembelian', 'color' => '#059669', 'icon' => 'fa-shopping-bag'],
                    'like'     => ['label' => 'Like', 'color' => '#db2777', 'icon' => 'fa-heart'],
                    'cart' => ['label' => 'Keranjang', 'color' => '#0284c7', 'icon' => 'fa-cart-shopping'],
                    'view' => ['label' => 'Kunjungan Produk', 'color' => '#7c3aed', 'icon' => 'fa-eye'],
                ];
                $total_signals = $raw_total ?: 1;
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
    </div>

    <!-- 7. Similaritas Produk (Visualisasi CF) -->
    <?php $top_pairs = isset($d->top_similarity_pairs) ? (array)$d->top_similarity_pairs : []; ?>
    <?php if (!empty($top_pairs)): ?>
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1"><i class="fas fa-link me-2" style="color: var(--accent);"></i>Similaritas Produk</h5>
                <p class="text-muted small mb-0">Pasangan produk yang dianggap mirip oleh model (item-item CF, shrinkage λ)</p>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ($top_pairs as $pair): ?>
            <div class="col-md-6">
                <div class="sim-pair d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--surface-2);">
                    <div class="d-flex flex-column flex-grow-1 gap-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small"><?= htmlspecialchars($pair->p1 ?? '') ?></span>
                            <span class="fw-semibold small text-end"><?= htmlspecialchars($pair->p2 ?? '') ?></span>
                        </div>
                        <div class="progress" style="height: 6px; background: var(--surface);">
                            <div class="progress-bar" style="width: <?= round(($pair->score ?? 0) * 100) ?>%; background: var(--accent); border-radius: 10px;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: .66rem;">
                            <span>skor <?= number_format((float)($pair->score ?? 0), 3) ?></span>
                            <span><?= $pair->co_occurrence ?? 0 ?> user bersama · shrink <?= $pair->shrink ?? '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>
</div>

<style>
    .train-orb {
        width: 48px; height: 48px; flex: 0 0 48px;
        background: var(--accent-soft); color: var(--accent);
        font-size: 1.15rem;
        animation: trainPulse 1.6s ease-in-out infinite;
    }
    @keyframes trainPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, .35); }
        50% { box-shadow: 0 0 0 10px rgba(99, 102, 241, 0); }
    }
    .train-orb.done { background: var(--success-bg); color: var(--success); animation: none; }
    .train-orb.failed { background: var(--danger-bg); color: var(--danger); animation: none; }
    .train-step {
        display: flex; align-items: center; gap: 12px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        transition: all .3s ease;
        opacity: .55;
    }
    .train-step .train-step-icon {
        width: 34px; height: 34px; flex: 0 0 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--text-3); font-size: .85rem;
    }
    .train-step.active { opacity: 1; border-color: var(--accent-ring, var(--accent)); background: var(--accent-soft); }
    .train-step.active .train-step-icon { color: var(--accent); border-color: var(--accent-ring, var(--accent)); }
    .train-step.done { opacity: 1; }
    .train-step.done .train-step-icon { background: var(--success-bg); color: var(--success); border-color: var(--success-border); }

    .metric-tile {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px;
        height: 100%;
        transition: all .2s ease;
    }
    .metric-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15, 23, 42, .07); }
    .metric-tile .kpi-value { font-size: 1.35rem; }

    .rec-prev-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px;
        transition: all .2s ease;
    }
    .rec-prev-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15, 23, 42, .07); }
    .rec-prev-thumb {
        width: 100%; height: 64px;
        display: flex; align-items: center; justify-content: center;
        background: var(--surface-2); color: var(--text-3);
        border-radius: 10px; font-size: 1.3rem;
        margin-bottom: 10px;
    }
    .rec-prev-var {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: .68rem; font-weight: 600;
        padding: 4px 11px; border-radius: 999px;
        background: var(--accent-soft); color: var(--accent);
        border: 1px solid var(--accent-ring, var(--accent));
    }
</style>

<script>
    function setTrainStep(idx, state) {
        const el = document.getElementById('step' + idx);
        if (!el) return;
        el.classList.remove('active', 'done');
        if (state) el.classList.add(state);
    }

    function refreshAICache() {
        const btn = document.getElementById('btnRetrain');
        if (btn.classList.contains('disabled')) return;

        const label   = document.getElementById('btnRetrainLabel');
        const icon    = btn.querySelector('i');
        const panel   = document.getElementById('trainingPanel');
        const orb     = document.getElementById('trainOrb');
        const orbIcon = document.getElementById('trainOrbIcon');
        const title   = document.getElementById('trainingTitle');
        const subtext = document.getElementById('trainingSubtext');
        const elapsed = document.getElementById('trainingElapsed');
        const live    = document.getElementById('liveBadge');

        document.querySelectorAll('.train-step-icon').forEach(el => el.dataset.base = el.innerHTML);

        // === STATE: RUNNING ===
        btn.classList.add('disabled'); btn.setAttribute('aria-disabled', 'true');
        icon.classList.add('fa-spin');
        label.textContent = 'Sedang Berjalan...';
        live.style.display = 'none';

        panel.style.display = 'block';
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        orb.classList.remove('done', 'failed');
        title.textContent = 'Melatih ulang model...';
        [0, 1, 2].forEach(i => {
            setTrainStep(i, i === 0 ? 'active' : null);
            const ic = document.querySelector('#step' + i + ' .train-step-icon');
            ic.innerHTML = ic.dataset.base;
        });

        const t0 = Date.now();
        const timer = setInterval(() => {
            elapsed.textContent = ((Date.now() - t0) / 1000).toFixed(1) + 's';
        }, 100);

        fetch('http://127.0.0.1:8000/cache/refresh', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                clearInterval(timer);

                if (data.status !== 'success') throw new Error(data.message || 'unknown');

                // === STATE: DONE ===
                [0, 1, 2].forEach((i) => {
                    setTrainStep(i, 'done');
                    document.querySelector('#step' + i + ' .train-step-icon').innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                });
                orb.classList.add('done');
                orbIcon.className = 'fa-solid fa-check';
                title.textContent = 'Pelatihan selesai';
                subtext.textContent = 'Model & lapisan varian diperbarui — memuat ulang statistik terbaru';
                showToast("Berhasil: " + data.message, "success");
                setTimeout(() => location.reload(), 1200);
            })
            .catch(err => {
                clearInterval(timer);

                // === STATE: FAILED ===
                orb.classList.add('failed');
                orbIcon.className = 'fa-solid fa-triangle-exclamation';
                title.textContent = 'Pelatihan gagal';
                subtext.textContent = 'Pastikan server Python (port 8000) aktif, lalu coba lagi';
                elapsed.textContent = ((Date.now() - t0) / 1000).toFixed(1) + 's';
                showToast('Gagal menyegarkan model.', 'error');

                btn.classList.remove('disabled'); btn.removeAttribute('aria-disabled');
                icon.classList.remove('fa-spin');
                label.textContent = 'Coba Lagi';
                live.style.display = '';
            });
    }

    async function loadRecPreview() {
        const uid = document.getElementById('recPreviewUserSelect').value;
        const c = document.getElementById('recPreviewContainer');
        c.innerHTML = '<div class="text-center py-4 text-muted small"><div class="spinner-border spinner-border-sm me-2"></div>Menghitung rekomendasi untuk Pengguna #' + uid + '...</div>';

        const ORIGIN_BG = {
            'BEST MATCH': '#059669', 'MED MATCH': '#0891b2',
            'FOR YOU': '#0284c7', 'STYLE MATCH': '#0891b2',
            'HOT HITS': '#4f46e5', 'BEST SELLER': '#4f46e5', 'TRENDING': '#4f46e5',
            'NEW ARRIVAL': '#7c3aed', 'DISCOVERY': '#94a3b8', 'FRESH': '#94a3b8'
        };
        const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));

        try {
            const res = await fetch(`http://127.0.0.1:8000/admin/rec-preview/${uid}?top_n=8`);
            const data = await res.json();

            if (!data.items || !data.items.length) {
                c.innerHTML = '<div class="empty-state py-4"><div class="empty-icon"><i class="fas fa-inbox"></i></div><p>Belum ada rekomendasi untuk pengguna ini.</p></div>';
                return;
            }

            let html = '<div class="row g-3">';
            data.items.forEach(it => {
                const bg = ORIGIN_BG[it.origin] || '#94a3b8';
                const v = it.variant;
                const varLabel = v ? [v.color, v.size].filter(Boolean).join(' · ') : '';
                html += `
                <div class="col-sm-6 col-xl-3">
                    <div class="rec-prev-card h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge rounded-pill px-2 py-1" style="background:${bg};font-size:0.58rem;color:#fff;letter-spacing:.5px;">${it.origin}</span>
                            <span class="fw-bold small num" style="color:var(--accent);">${fmtRp(it.price || 0)}</span>
                        </div>
                        <div class="rec-prev-thumb"><i class="fas fa-box"></i></div>
                        <div class="fw-semibold" style="font-size:.82rem;">${it.name}</div>
                        ${varLabel
                            ? `<span class="rec-prev-var mt-2"><i class="fa-solid fa-tags"></i>${varLabel}</span>`
                            : `<span class="text-muted mt-2 d-block" style="font-size:.7rem;">— tanpa varian —</span>`}
                    </div>
                </div>`;
            });
            html += '</div>';
            c.innerHTML = html;
        } catch (e) {
            c.innerHTML = '<div class="text-center py-4 text-danger small"><i class="fas fa-plug-circle-xmark me-2"></i>Gagal memuat. Pastikan FastAPI aktif.</div>';
        }
    }
</script>
