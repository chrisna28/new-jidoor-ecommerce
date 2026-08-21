<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="mb-5 text-center">
                <a href="<?= base_url('pesanan/detail/' . $order_id) ?>" class="text-decoration-none text-muted small fw-bold ls-1"><i class="fas fa-arrow-left me-2"></i> BACK TO DETAILS</a>
                <h1 class="fw-bold display-6 mt-4 ls-1">UPLOAD PROOF</h1>
                <p class="text-muted small text-uppercase ls-2">ORDER #<?= $order_id ?></p>
            </div>

            <div class="bg-light p-4 p-md-5">
                <form action="<?= base_url('cart/upload_bukti_aksi') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    
                    <div class="mb-5 text-center">
                        <div class="upload-preview mb-4 border-2 border-dashed border-dark p-2 d-inline-block" style="min-width: 200px; min-height: 250px; cursor: pointer;" onclick="document.getElementById('fileInput').click()">
                            <img id="imgPreview" src="https://placehold.co/400x500/fff/999?text=CLICK+TO+SELECT+IMAGE" class="img-fluid" style="max-height: 400px;">
                        </div>
                        <input type="file" name="payment_proof" id="fileInput" class="d-none" accept="image/*" required onchange="previewImage(this)">
                        <p class="small text-muted">Max file size: 2MB (JPG, PNG, WEBP)</p>
                    </div>

                    <div class="mb-5 p-4 bg-white border-start border-dark border-3">
                        <h6 class="fw-bold text-uppercase ls-1 mb-2">Instructions</h6>
                        <ol class="small text-muted ps-3 mb-0">
                            <li class="mb-2">Make sure the transaction reference is visible.</li>
                            <li>The amount must match the order total precisely.</li>
                            <li>Upload a clear photo or screenshot of your transfer.</li>
                        </ol>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 fw-bold ls-1 shadow-lg">
                        SUBMIT PAYMENT PROOF
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
.border-dashed {
    border-style: dashed !important;
}
</style>
