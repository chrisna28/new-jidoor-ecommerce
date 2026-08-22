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

            <!-- Variasi Produk (ala Shopee) -->
            <div class="admin-card mt-4">
                <div class="admin-card-title">Variasi Produk</div>
                <p class="text-muted small mb-4">Tentukan nama variasi beserta pilihannya, lalu tabel kombinasi dibuat otomatis. Perubahan menggantikan seluruh variasi lama. Kosongkan keduanya untuk produk tanpa variasi.</p>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label text-muted small text-uppercase fw-700 ls-1">Nama Variasi 1</label>
                        <input type="text" id="varName1" name="variant_name1" value="<?= htmlspecialchars($product->variant_name1 ?: 'Warna') ?>" maxlength="50" class="form-control-admin w-100" oninput="VB.rebuild()">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label text-muted small text-uppercase fw-700 ls-1">Pilihan Variasi 1</label>
                        <input type="text" id="chipInput1" class="form-control-admin w-100" placeholder="Ketik pilihan (mis. Hitam) lalu tekan Enter" autocomplete="off">
                        <div id="chips1" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label text-muted small text-uppercase fw-700 ls-1">Nama Variasi 2 <span class="fw-normal">(opsional)</span></label>
                        <input type="text" id="varName2" name="variant_name2" value="<?= htmlspecialchars($product->variant_name1 === $product->variant_name2 ? '' : ($product->variant_name2 ?: '')) ?>" maxlength="50" placeholder="mis. Ukuran / Tinggi" class="form-control-admin w-100" oninput="VB.rebuild()">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label text-muted small text-uppercase fw-700 ls-1">Pilihan Variasi 2</label>
                        <input type="text" id="chipInput2" class="form-control-admin w-100" placeholder="Ketik pilihan lalu tekan Enter" autocomplete="off">
                        <div id="chips2" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>

                <div id="matrixWrap" class="d-none mt-4">
                    <div class="d-flex flex-wrap gap-3 align-items-end mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                        <div>
                            <label class="form-label text-muted small mb-1">Isi semua selisih harga (± Rp)</label>
                            <div class="d-flex gap-2">
                                <input type="number" id="bulkDelta" class="form-control-admin" style="width:120px" value="0">
                                <button type="button" class="btn btn-sm btn-admin-outline px-3" onclick="VB.fillAll('Delta')">Terapkan</button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-muted small mb-1">Isi semua stok</label>
                            <div class="d-flex gap-2">
                                <input type="number" id="bulkStock" class="form-control-admin" style="width:120px" min="0" value="1">
                                <button type="button" class="btn btn-sm btn-admin-outline px-3" onclick="VB.fillAll('Stock')">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-borderless align-middle mb-0" id="matrixTable">
                            <thead>
                                <tr id="matrixHead" class="text-muted small text-uppercase ls-1"></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
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
                <div class="admin-card-title">Fitur Khusus</div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_custom" value="1" id="isCustomSwitch" <?= !empty($product->is_custom) ? 'checked' : '' ?>>
                    <label class="form-check-label text-white small fw-bold" for="isCustomSwitch">
                        Produk bisa custom
                    </label>
                </div>
                <p class="text-muted small mb-0 mt-2">Pelanggan dapat menulis permintaan custom saat membeli dan mengunggah gambar referensi saat checkout.</p>
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

<style>
.vb-chip{display:inline-flex;align-items:center;gap:.4rem;background:#23262f;border:1px solid #3a3f4b;color:#fff;border-radius:999px;padding:.35rem .5rem .35rem .9rem;font-size:.8rem;}
.vb-chip button{background:none;border:none;color:#8b949e;font-size:1.05rem;line-height:1;padding:0 .25rem;cursor:pointer;}
.vb-chip button:hover{color:#dc3545;}
#matrixTable td{vertical-align:middle;}
</style>
<script>
// ===== Pembangun Variasi ala Shopee =====
const VB = {
    opts1: [],
    opts2: [],
    store: {},

    addChip(tier) {
        const input = document.getElementById('chipInput' + tier);
        const val = input.value.trim().replace(/,+$/, '');
        if (!val) return;
        input.value = '';
        const list = tier === 1 ? this.opts1 : this.opts2;
        if (list.some(o => o.toLowerCase() === val.toLowerCase()) || list.length >= 30) return;
        list.push(val);
        this.renderChips(tier);
        this.rebuild();
    },

    removeChip(tier, idx) {
        (tier === 1 ? this.opts1 : this.opts2).splice(idx, 1);
        this.renderChips(tier);
        this.rebuild();
    },

    renderChips(tier) {
        const wrap = document.getElementById('chips' + tier);
        const list = tier === 1 ? this.opts1 : this.opts2;
        wrap.innerHTML = '';
        list.forEach((o, i) => {
            const chip = document.createElement('span');
            chip.className = 'vb-chip';
            chip.innerHTML = o + ' <button type="button" aria-label="hapus" onclick="VB.removeChip(' + tier + ',' + i + ')">&times;</button>';
            wrap.appendChild(chip);
        });
    },

    // Nilai variasi & nama yang efektif (jika hanya Variasi 2 diisi, digeser ke posisi 1)
    effective() {
        let o1 = this.opts1.slice(), o2 = this.opts2.slice(), swapped = false;
        const n2raw = document.getElementById('varName2').value.trim();
        if (!o1.length && o2.length) { swapped = true; }
        const n1 = swapped ? (n2raw || 'Variasi') : (document.getElementById('varName1').value.trim() || 'Variasi 1');
        const n2 = swapped ? 'Variasi 2' : (n2raw || 'Variasi 2');
        return { o1, o2, n1, n2, swapped };
    },

    capture() {
        document.querySelectorAll('#matrixTable tbody tr').forEach(tr => {
            this.store[tr.dataset.c + '|' + tr.dataset.s] = {
                stock: tr.querySelector('.vb-stock').value,
                delta: tr.querySelector('.vb-delta').value
            };
        });
    },

    rebuild() {
        this.capture();
        const tbody = document.querySelector('#matrixTable tbody');
        const head  = document.getElementById('matrixHead');
        const wrap  = document.getElementById('matrixWrap');
        const e = this.effective();

        if (!e.o1.length && !e.o2.length) {
            wrap.classList.add('d-none');
            head.innerHTML = ''; tbody.innerHTML = '';
            return;
        }

        head.innerHTML =
            '<th>' + e.n1 + '</th>' +
            (e.o2.length ? '<th>' + e.n2 + '</th>' : '') +
            '<th style="width:26%">Selisih Harga (± Rp)</th>' +
            '<th style="width:18%">Stok</th>';

        tbody.innerHTML = '';
        e.o1.forEach(c => {
            (e.o2.length ? e.o2 : ['']).forEach(s => {
                const saved = this.store[c + '|' + s] || {};
                const tr = document.createElement('tr');
                tr.dataset.c = c;
                tr.dataset.s = s;
                tr.innerHTML =
                    '<td class="text-white">' + c + '</td>' +
                    (e.o2.length ? '<td class="text-white">' + s + '</td>' : '') +
                    '<td><input type="hidden" name="variant_color[]" value="' + c + '">' +
                    '<input type="hidden" name="variant_size[]" value="' + s + '">' +
                    '<input type="number" step="1" name="variant_price_delta[]" class="form-control-admin vb-delta w-100" value="' + (saved.delta !== undefined && saved.delta !== '' ? saved.delta : 0) + '"></td>' +
                    '<td><input type="number" min="0" name="variant_stock[]" class="form-control-admin vb-stock w-100" value="' + (saved.stock || '') + '" placeholder="0"></td>';
                tbody.appendChild(tr);
            });
        });
        wrap.classList.remove('d-none');
    },

    fillAll(kind) {
        const v = document.getElementById('bulk' + kind).value;
        document.querySelectorAll(kind === 'Stock' ? '.vb-stock' : '.vb-delta').forEach(inp => inp.value = v);
    }
};

// Chip input: Enter/koma menambah pilihan
['1', '2'].forEach(tier => {
    document.getElementById('chipInput' + tier).addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter' || ev.key === ',') { ev.preventDefault(); VB.addChip(tier); }
    });
});

// Saat submit: paksa stok terisi & geser nama variasi bila perlu
document.querySelector('form').addEventListener('submit', function () {
    document.querySelectorAll('#matrixTable .vb-stock').forEach(i => { if (i.value === '') i.value = '0'; });
    const e = VB.effective();
    if (e.swapped) {
        document.getElementById('varName1').value = document.getElementById('varName2').value.trim() || 'Variasi';
        document.getElementById('varName2').value = '';
    }
});

// ===== Prefill dari data existing =====
document.addEventListener('DOMContentLoaded', function () {
    const variants = <?= json_encode(array_map(function($v) {
        return [
            'color'       => $v->color,
            'size'        => $v->size,
            'stock'       => (int)$v->stock,
            'price_delta' => (float)$v->price_delta,
        ];
    }, isset($variants) ? $variants : [])) ?>;

    let o1 = [], o2 = [], seen1 = {}, seen2 = {};
    variants.forEach(v => {
        if (v.color !== 'Standar' && !seen1[v.color]) { seen1[v.color] = 1; o1.push(v.color); }
        if (v.size  !== 'Standar' && !seen2[v.size]) { seen2[v.size] = 1; o2.push(v.size); }
    });

    // Legacy: nilai hanya di kolom size → tampilkan sebagai variasi 1
    let swapped = false;
    if (!o1.length && o2.length) { swapped = true; }

    if (o1.length || o2.length) {
        VB.store = {};
        variants.forEach(v => {
            const key = swapped ? (v.size + '|') : (v.color + '|' + v.size);
            VB.store[key] = { stock: v.stock, delta: v.price_delta };
        });
        VB.opts1 = swapped ? o2 : o1;
        VB.opts2 = swapped ? []  : o2;
        VB.renderChips(1);
        VB.renderChips(2);
        VB.rebuild();
    }
});
</script>
