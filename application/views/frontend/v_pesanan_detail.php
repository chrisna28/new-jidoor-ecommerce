<div class="container py-5 mt-5">
    <div class="mb-5">
        <a href="<?= base_url('pesanan') ?>" class="text-decoration-none text-muted small fw-bold ls-1"><i class="fas fa-arrow-left me-2"></i> BACK TO MY ORDERS</a>
        <h1 class="fw-bold display-6 mt-3 ls-1">ORDER #<?= $order->id ?></h1>
        <div class="d-flex align-items-center gap-3 mt-2">
            <span class="text-muted small ls-1 text-uppercase"><?= date('d F Y, H:i', strtotime($order->created_at)) ?></span>
            <span class="badge bg-dark rounded-0 px-3 py-2 fw-bold ls-1 text-uppercase" style="font-size: 0.65rem;"><?= $order->status ?></span>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left: Items & Details -->
        <div class="col-lg-8">
            <div class="mb-5 pb-5 border-bottom">
                <h5 class="fw-bold text-uppercase ls-2 mb-4">Ordered Items</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="py-3 ps-0" style="width: 80px;">
                                        <div class="bg-light p-1" style="width: 70px; height: 90px;">
                                            <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/70x90/f5f5f5/000000?text=P' ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->name) ?>">
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <h6 class="fw-bold mb-1 ls-1"><?= htmlspecialchars($item->name) ?></h6>
                                        <p class="small text-muted mb-0">Qty: <?= $item->qty ?> &times; Rp <?= number_format($item->price, 0, ',', '.') ?></p>
                                    </td>
                                    <td class="py-3 text-end fw-bold ls-1 pe-0">
                                        Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="py-4 ps-0 text-uppercase small fw-bold ls-1">Order Total</td>
                                <td class="py-4 pe-0 text-end fw-800 fs-5">Rp <?= number_format($order->total_price, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <h6 class="fw-bold text-uppercase ls-1 mb-3">Shipping Address</h6>
                    <div class="text-muted small lh-lg">
                        <strong class="text-dark d-block mb-1"><?= htmlspecialchars($order->receiver_name) ?></strong>
                        <?= nl2br(htmlspecialchars($order->address)) ?><br>
                        <?= htmlspecialchars($order->city) ?>, <?= htmlspecialchars($order->province) ?><br>
                        <i class="fas fa-phone-alt me-2 mt-2"></i> <?= $order->phone ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-uppercase ls-1 mb-3">Payment Method</h6>
                    <div class="text-muted small lh-lg">
                        <strong class="text-dark d-block mb-1">Bank Transfer (Manual)</strong>
                        Mandiri Transfer: 123-456-7890 (JiDoor Store)<br>
                        Status: <span class="fw-bold <?= $order->status == 'pending' ? 'text-warning' : 'text-success' ?>"><?= strtoupper($order->status) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Proof of Payment & Instructions -->
        <div class="col-lg-4">
            <div class="bg-light p-4 p-md-5">
                <h5 class="fw-bold text-uppercase ls-2 mb-4">Payment Proof</h5>
                
                <?php if ($order->payment_proof): ?>
                    <div class="mb-4">
                        <p class="small text-muted mb-3">You have uploaded your payment proof. Our team will verify it shortly.</p>
                        <div class="bg-white border p-2 mb-3">
                            <img src="<?= base_url('uploads/payments/' . $order->payment_proof) ?>" class="img-fluid w-100" alt="Proof">
                        </div>
                        <?php if ($order->status == 'pending'): ?>
                            <a href="<?= base_url('checkout/bukti/' . $order->id) ?>" class="btn btn-outline-dark btn-sm w-100 rounded-0 fw-bold ls-1">RE-UPLOAD PROOF</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-cloud-upload-alt display-4 text-muted opacity-25 mb-3"></i>
                        <p class="small text-muted mb-4">Please upload your payment proof to complete the verification process.</p>
                        <a href="<?= base_url('checkout/bukti/' . $order->id) ?>" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1">UPLOAD PROOF NOW</a>
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-4 border-top">
                    <h6 class="fw-bold text-uppercase ls-1 mb-3" style="font-size: 0.75rem;">Need Help?</h6>
                    <a href="#" class="text-decoration-none text-dark small fw-bold ls-1"><i class="fab fa-whatsapp me-2"></i> CHAT WITH SUPPORT</a>
                </div>
            </div>
        </div>
    </div>
</div>
