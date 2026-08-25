<div class="container">
    <div class="row justify-content-center pb-sect">
        <div class="col-lg-6">
            <div class="page-head text-center" data-reveal>
                <nav class="crumb2 justify-content-center">
                    <a href="<?= base_url('pesanan/detail/' . $order_id) ?>"><i class="fas fa-arrow-left me-1"></i> Kembali ke detail</a>
                </nav>
                <h1 class="page-title mt-3">Unggah bukti bayar</h1>
                <p style="color:var(--muted); font-size:.92rem;" class="mb-0">Pesanan #<?= $order_id ?></p>
            </div>

            <form action="<?= base_url('cart/upload_bukti_aksi') ?>" method="post" enctype="multipart/form-data" class="card-soft p-4 p-md-5" data-reveal style="--rd:.1s;">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= $order_id ?>">

                <label class="dropzone d-block py-4 px-3 text-center mb-2" onclick="document.getElementById('fileInput').click()">
                    <img id="imgPreview" src="https://placehold.co/400x500/f6f2ea/8b8274?text=KLIK+UNTUK+PILIH+GAMBAR" class="img-fluid rounded-3" style="max-height: 400px;" alt="Preview bukti">
                    <p class="fx-hint mt-3 mb-1"><i class="fas fa-cloud-arrow-up me-1"></i> Klik untuk memilih gambar</p>
                    <p class="fx-hint mb-0">Ukuran maksimal: 2MB (JPG, PNG, WEBP)</p>
                </label>
                <input type="file" name="payment_proof" id="fileInput" class="d-none" accept="image/*" required onchange="previewImage(this)">

                <div class="mt-4 p-4 rounded-3" style="background:var(--paper); border:1px solid var(--line);">
                    <span class="eyebrow eyebrow-plain" style="margin-bottom:12px;">Petunjuk</span>
                    <ol class="small ps-3 mb-0 lh-lg" style="color:var(--muted);">
                        <li>Pastikan nomor referensi transaksi terlihat jelas.</li>
                        <li>Nominal harus sesuai dengan total pesanan.</li>
                        <li>Unggah foto atau tangkapan layar bukti transfer yang jelas.</li>
                    </ol>
                </div>

                <button type="submit" class="btn-ink btn-block2 mt-4">
                    Kirim bukti pembayaran
                </button>
            </form>
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
