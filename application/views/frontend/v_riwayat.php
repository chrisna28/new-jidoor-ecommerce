<div class="container">
    <div class="page-head text-center" data-reveal>
        <span class="eyebrow eyebrow-plain">Riwayat</span>
        <h1 class="page-title mt-3">Pesanan saya</h1>
        <p style="color:var(--muted); font-size:.92rem;" class="mb-0">Lacak dan kelola riwayat pesanan Anda.</p>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="row justify-content-center pb-sect">
            <div class="col-lg-9">
                <?php foreach ($orders as $o): ?>
                    <div class="order-card card-soft flex-column flex-sm-row align-items-sm-center">
                        <div>
                            <div class="order-id">Pesanan #<?= $o->id ?></div>
                            <div class="order-date"><?= tanggal_indo(strtotime($o->created_at)) ?>, <?= date('H:i', strtotime($o->created_at)) ?> WIB</div>
                        </div>
                        <?php
                            $st_map = [
                                'pending'   => 'st-pending',
                                'paid'      => 'st-paid',
                                'processed' => 'st-processed',
                                'shipped'   => 'st-shipped',
                                'delivered' => 'st-delivered',
                                'rejected'  => 'st-rejected',
                                'cancelled' => 'st-cancelled',
                            ];
                            $st_cls = $st_map[$o->status] ?? 'st-cancelled';
                        ?>
                        <span class="st-chip <?= $st_cls ?>"><i class="fas fa-circle" style="font-size:.4rem;"></i> <?= status_label_id($o->status) ?></span>
                        <div class="ms-sm-auto text-sm-end mt-3 mt-sm-0">
                            <div class="order-total tnum mb-1">Rp <?= number_format($o->total_price, 0, ',', '.') ?></div>
                            <a href="<?= base_url('pesanan/detail/' . $o->id) ?>" class="btn-text2 btn-sm2">Detail <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="empty2 card-soft mb-sect">
            <div class="ico"><i class="fas fa-box-open"></i></div>
            <h3>Belum ada pesanan</h3>
            <p>Anda belum memiliki pesanan.</p>
            <a href="<?= base_url('katalog') ?>" class="btn-ink">Jelajahi produk</a>
        </div>
    <?php endif; ?>
</div>
