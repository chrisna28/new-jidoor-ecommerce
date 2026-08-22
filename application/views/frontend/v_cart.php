<div class="container py-5 mt-5">
    <div class="mb-5 text-center">
        <h1 class="fw-bold display-5 ls-1">YOUR BAG</h1>
        <p class="text-muted small text-uppercase ls-2">Review your items before checkout</p>
    </div>

    <?php if (!empty($cart_items)): ?>
        <div class="row g-5">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="border-bottom">
                            <tr>
                                <th class="py-3 border-0 small fw-bold text-uppercase ls-1" style="width: 50%;">Product</th>
                                <th class="py-3 border-0 small fw-bold text-uppercase ls-1 text-center">Quantity</th>
                                <th class="py-3 border-0 small fw-bold text-uppercase ls-1 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr class="border-bottom">
                                    <td class="py-4 border-0">
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="product-img-box bg-light p-0 m-0" style="width: 100px; height: 120px; aspect-ratio: unset;">
                                                <img src="<?= $item->image && $item->image !== 'default.jpg' ? base_url('uploads/products/' . $item->image) : 'https://placehold.co/100x120/f5f5f5/000000?text=P' ?>" class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($item->name) ?>">
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1 ls-1"><?= htmlspecialchars($item->name) ?></h6>
                                                <?php if (!empty($item->color) && $item->color !== 'Standar' || !empty($item->size) && $item->size !== 'Standar'): ?>
                                                    <p class="small mb-1">
                                                        <?php if (!empty($item->color) && $item->color !== 'Standar'): ?>
                                                            <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($item->variant_name1 ?: 'Variasi') ?>: <?= htmlspecialchars($item->color) ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($item->size) && $item->size !== 'Standar'): ?>
                                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($item->variant_name2 ?: 'Variasi') ?>: <?= htmlspecialchars($item->size) ?></span>
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (!empty($item->note)): ?>
                                                    <p class="small text-muted mb-2 fst-italic"><i class="fas fa-pen-nib me-1"></i><?= htmlspecialchars($item->note) ?></p>
                                                <?php endif; ?>
                                                <p class="small text-muted mb-2">Rp <?= number_format($item->price, 0, ',', '.') ?></p>
                                                <a href="<?= base_url('keranjang/hapus/' . $item->id) ?>" class="text-decoration-none small text-danger fw-bold ls-1"><i class="fas fa-times me-1"></i> REMOVE</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 border-0 text-center">
                                        <form action="<?= base_url('keranjang/update') ?>" method="post" class="d-inline-block">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="cart_id" value="<?= $item->id ?>">
                                            <div class="input-group border" style="width: 120px; margin: 0 auto;">
                                                <button class="btn btn-link text-dark px-2 py-1 text-decoration-none border-0" type="submit" name="action" value="minus">-</button>
                                                <input type="text" class="form-control border-0 text-center bg-white fw-bold p-0" value="<?= $item->qty ?>" readonly>
                                                <button class="btn btn-link text-dark px-2 py-1 text-decoration-none border-0" type="submit" name="action" value="plus">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="py-4 border-0 text-end">
                                        <span class="fw-bold ls-1">Rp <?= number_format($item->price * $item->qty, 0, ',', '.') ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="<?= base_url('katalog') ?>" class="text-dark fw-bold text-decoration-none small ls-1"><i class="fas fa-arrow-left me-2"></i> CONTINUE SHOPPING</a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="bg-light p-4 p-md-5">
                    <h5 class="fw-bold text-uppercase ls-2 mb-4">Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                        <span class="text-muted small ls-1">SUBTOTAL</span>
                        <span class="fw-bold">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-uppercase ls-1 mb-2">Apply Promo Code</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control-mixtas bg-transparent w-100" placeholder="ENTER CODE">
                            <button class="btn btn-dark rounded-0 px-3 py-2 fw-bold small ls-1">APPLY</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-5">
                        <span class="fw-bold ls-1">ORDER TOTAL</span>
                        <span class="fw-800 fs-4">Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>

                    <a href="<?= base_url('checkout') ?>" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 mb-3">
                        PROCEED TO CHECKOUT
                    </a>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-center gap-3 opacity-50">
                            <i class="fab fa-cc-visa fs-3"></i>
                            <i class="fab fa-cc-mastercard fs-3"></i>
                            <i class="fab fa-cc-paypal fs-3"></i>
                        </div>
                        <p class="small text-muted mt-3 mb-0">Secure checkout powered by JiDoor Security.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-shopping-bag display-1 text-muted opacity-10"></i>
            </div>
            <h2 class="fw-bold ls-1">YOUR BAG IS EMPTY</h2>
            <p class="text-muted">Looks like you haven't added any products to your bag yet.</p>
            <a href="<?= base_url('katalog') ?>" class="btn-discovery d-inline-block mt-4">START SHOPPING</a>
        </div>
    <?php endif; ?>
</div>
