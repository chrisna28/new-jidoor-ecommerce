<div class="container py-5 mt-5">
    <div class="mb-5">
        <h1 class="fw-bold display-5 ls-1">CHECKOUT</h1>
        <p class="text-muted small text-uppercase ls-2">Enter your information to complete the order</p>
    </div>

    <form action="<?= base_url('checkout/proses') ?>" method="post">
        <?= csrf_field() ?>
        <div class="row g-5">
            <!-- Column 1: Shipping Details -->
            <div class="col-lg-7">
                <div class="mb-5">
                    <h5 class="fw-bold text-uppercase ls-2 mb-4 pb-2 border-bottom">1. Shipping Information</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-uppercase ls-1">Full Name</label>
                            <input type="text" name="receiver_name" class="form-control-mixtas w-100" placeholder="ENTER YOUR FULL NAME" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-uppercase ls-1">Phone Number</label>
                            <input type="text" name="phone" class="form-control-mixtas w-100" placeholder="ENTER PHONE NUMBER" required>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold text-uppercase ls-1">Detailed Address</label>
                            <textarea name="address" class="form-control-mixtas w-100" rows="3" placeholder="STREET NAME, HOUSE NUMBER, ETC." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-uppercase ls-1">Province / State</label>
                            <input type="text" name="province" class="form-control-mixtas w-100" placeholder="PROVINCE" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-uppercase ls-1">City / District</label>
                            <input type="text" name="city" class="form-control-mixtas w-100" placeholder="CITY" required>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h5 class="fw-bold text-uppercase ls-2 mb-4 pb-2 border-bottom">2. Payment Method</h5>
                    <div class="p-4 bg-light border-start border-dark border-3">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" checked>
                            <label class="form-check-label fw-bold ls-1" for="bank_transfer">
                                BANK TRANSFER (MANUAL VERIFICATION)
                            </label>
                        </div>
                        <div class="ps-4">
                            <p class="small text-muted mb-0">Please transfer to our Bank Mandiri account: <strong>123-456-7890 (JiDoor Store)</strong>. You will need to upload proof of payment after checkout.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h5 class="fw-bold text-uppercase ls-2 mb-4 pb-2 border-bottom">3. Additional Notes</h5>
                    <textarea name="note" class="form-control-mixtas w-100" rows="2" placeholder="OPTIONAL: ANY SPECIAL INSTRUCTIONS?"></textarea>
                </div>
            </div>

            <!-- Column 2: Order Summary -->
            <div class="col-lg-5">
                <div class="bg-light p-4 p-md-5 sticky-top" style="top: 120px;">
                    <h5 class="fw-bold text-uppercase ls-2 mb-4">Your Order</h5>
                    
                    <div class="mb-4">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white border text-center p-1" style="width: 50px; height: 60px;">
                                        <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/50x60/f5f5f5/000000?text=P' ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->name) ?>">
                                    </div>
                                    <div>
                                        <div class="small fw-bold ls-1"><?= htmlspecialchars($item->name) ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Qty: <?= $item->qty ?></div>
                                    </div>
                                </div>
                                <div class="small fw-bold">Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-top pt-4 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small ls-1">SUBTOTAL</span>
                            <span class="small fw-bold">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small ls-1">SHIPPING</span>
                            <span class="small fw-bold text-success">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <span class="fw-bold text-uppercase ls-2">Total</span>
                            <span class="fw-800 fs-3">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 shadow-lg">
                        PLACE ORDER NOW
                    </button>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0"><i class="fas fa-lock me-2"></i> Your transaction is encrypted and secure.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
