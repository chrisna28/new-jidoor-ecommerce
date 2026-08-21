<div class="mb-5">
    <a href="<?= base_url('admin/produk') ?>" class="btn btn-admin-outline px-4 mb-4">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
    </a>
    <h2 class="fw-bold text-white">Edit <span class="text-info">Produk</span></h2>
    <p class="text-muted">Perbarui informasi produk <strong>#<?= $product->id ?></strong></p>
</div>

<form method="post" action="<?= base_url('admin/produk/update') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $product->id ?>">
    <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="mb-4">
                    <label class="form-label text-muted small text-uppercase fw-700 ls-1">Nama Produk</label>
                    <input type="text" name="name" class="form-control-admin w-100" value="<?= htmlspecialchars($product->name) ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted small text-uppercase fw-700 ls-1">Kategori</label>
                    <select name="category_id" class="form-select form-control-admin" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= $cat->id == $product->category_id ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                
                <div class="mb-0">
                    <label class="form-label text-muted small text-uppercase fw-700 ls-1">Deskripsi Produk</label>
                    <textarea name="description" class="form-control-admin w-100" rows="8"><?= htmlspecialchars($product->description) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar Form -->
        <div class="col-lg-4">
            <div class="admin-card mb-4">
                <div class="admin-card-title">Inventori & Harga</div>
                <div class="mb-4">
                    <label class="form-label text-muted small text-uppercase fw-700 ls-1">Harga Jual (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted rounded-start-3">Rp</span>
                        <input type="number" name="price" class="form-control-admin rounded-start-0" value="<?= $product->price ?>" required min="0">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small text-uppercase fw-700 ls-1">Stok Barang</label>
                    <input type="number" name="stock" class="form-control-admin w-100" value="<?= $product->stock ?>" required min="0">
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-title">Media Produk</div>
                <div class="text-center p-4 rounded-4 border-2 border-dashed border-secondary border-opacity-30 bg-dark bg-opacity-50 cursor-pointer mb-3" onclick="document.getElementById('imgInput').click()">
                    <img id="imgPreview" src="<?= $product->image && $product->image !== 'default.jpg' ? base_url('uploads/products/'.$product->image) : 'https://placehold.co/400x400/0b0e14/8b949e?text=No+Image' ?>" class="img-fluid rounded-4 shadow-sm mb-3">
                    <p class="small text-muted mb-0">Klik untuk ganti gambar produk</p>
                </div>
                <input type="file" id="imgInput" name="image" accept="image/*" class="d-none" onchange="previewImg(this)">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-info py-3 fw-bold shadow-lg text-white">
                    <i class="fas fa-sync-alt me-2"></i> Perbarui Produk
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function previewImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
