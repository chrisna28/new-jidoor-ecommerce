<div class="container py-5 mt-5">
    <div class="mb-5 text-center">
        <h1 class="fw-bold display-5 ls-1">MY ORDERS</h1>
        <p class="text-muted small text-uppercase ls-2">Track and manage your order history</p>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4 border-0 small fw-bold text-uppercase ls-1">Order ID</th>
                                <th class="py-3 border-0 small fw-bold text-uppercase ls-1">Date</th>
                                <th class="py-3 border-0 small fw-bold text-uppercase ls-1">Total</th>
                                <th class="py-3 border-0 small fw-bold text-uppercase ls-1 text-center">Status</th>
                                <th class="py-3 px-4 border-0 small fw-bold text-uppercase ls-1 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr class="border-bottom">
                                    <td class="py-4 px-4 border-0">
                                        <span class="fw-bold ls-1 text-dark">#<?= $o->id ?></span>
                                    </td>
                                    <td class="py-4 border-0 text-muted small">
                                        <?= date('d M Y, H:i', strtotime($o->created_at)) ?>
                                    </td>
                                    <td class="py-4 border-0 fw-bold">
                                        Rp <?= number_format($o->total_price, 0, ',', '.') ?>
                                    </td>
                                    <td class="py-4 border-0 text-center">
                                        <?php 
                                            $config = [
                                                'pending'  => ['bg' => 'bg-warning', 'icon' => 'fa-clock'],
                                                'paid'     => ['bg' => 'bg-success', 'icon' => 'fa-check-circle'],
                                                'shipped'  => ['bg' => 'bg-info', 'icon' => 'fa-shipping-fast'],
                                                'rejected' => ['bg' => 'bg-danger', 'icon' => 'fa-times-circle']
                                            ];
                                            $st = $config[$o->status] ?? ['bg' => 'bg-secondary', 'icon' => 'fa-info-circle'];
                                        ?>
                                        <span class="badge <?= $st['bg'] ?> bg-opacity-10 <?= str_replace('bg-', 'text-', $st['bg']) ?> border border-<?= str_replace('bg-', '', $st['bg']) ?> border-opacity-25 px-3 py-2 rounded-pill fw-bold text-uppercase ls-1" style="font-size: 0.65rem;">
                                            <i class="fas <?= $st['icon'] ?> me-1"></i> <?= $o->status ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 border-0 text-end">
                                        <a href="<?= base_url('pesanan/detail/' . $o->id) ?>" class="btn btn-dark btn-sm rounded-0 px-4 py-2 fw-bold ls-1">DETAILS</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-box-open display-1 text-muted opacity-10"></i>
            </div>
            <h2 class="fw-bold ls-1">NO ORDERS YET</h2>
            <p class="text-muted">You haven't placed any orders with us yet.</p>
            <a href="<?= base_url('katalog') ?>" class="btn-discovery d-inline-block mt-4">DISCOVER PRODUCTS</a>
        </div>
    <?php endif; ?>
</div>
