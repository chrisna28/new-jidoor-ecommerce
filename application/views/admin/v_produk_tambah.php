<?php if ($s = $this->session->flashdata('success')): ?><div class="js-flash d-none" data-type="success" data-msg="<?= htmlspecialchars($s) ?>"></div><?php endif; ?>
<?php if ($e = $this->session->flashdata('error')): ?><div class="js-flash d-none" data-type="error" data-msg="<?= htmlspecialchars($e) ?>"></div><?php endif; ?>

<div class="page-head page-head-flex">
    <a href="<?= base_url('admin/produk') ?>" class="btn-back-pill" title="Kembali ke daftar produk">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h2 class="page-title mb-0">Tambah Produk</h2>
        <p class="page-sub mb-0">Masukkan detail produk baru untuk katalog toko Anda.</p>
    </div>
</div>

<form method="post" action="<?= base_url('admin/produk/tambah/aksi') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <!-- ===== Kolom utama ===== -->
        <div class="col-lg-8">

            <!-- Informasi Produk -->
            <div class="admin-card">
                <div class="sec-head">
                    <span class="sec-icon" style="background:var(--accent-soft);color:var(--accent);"><i class="fas fa-circle-info"></i></span>
                    <div>
                        <div class="sec-title">Informasi Produk</div>
                        <div class="sec-hint">Data utama yang ditampilkan di katalog pelanggan.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flabel">Nama Produk <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control-admin w-100" placeholder="Contoh: Pintu Jati Minimalis Modern" required>
                </div>

                <div class="mb-4">
                    <label class="flabel">Kategori <span class="req">*</span></label>
                    <select name="category_id" class="form-select form-control-admin" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="flabel">Deskripsi Produk</label>
                    <textarea name="description" class="form-control-admin w-100" rows="8" placeholder="Tuliskan spesifikasi, material, dan keunggulan produk..."></textarea>
                </div>
            </div>

            <!-- Variasi Produk (ala Shopee) -->
            <div class="admin-card mt-4">
                <div class="sec-head">
                    <span class="sec-icon" style="background:var(--info-bg);color:var(--info);"><i class="fas fa-sliders"></i></span>
                    <div>
                        <div class="sec-title">Variasi Produk</div>
                        <div class="sec-hint">Tentukan nama variasi beserta pilihannya, lalu tabel kombinasi dibuat otomatis. Kosongkan keduanya untuk produk tanpa variasi.</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="tier-box h-100">
                            <div class="tier-tag">Variasi 1</div>
                            <label class="flabel">Nama Variasi 1</label>
                            <input type="text" id="varName1" name="variant_name1" value="Warna" maxlength="50" class="form-control-admin w-100" oninput="VB.rebuild()">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="tier-box h-100">
                            <div class="tier-tag">Pilihan</div>
                            <label class="flabel">Pilihan Variasi 1</label>
                            <input type="text" id="chipInput1" class="form-control-admin w-100" placeholder="Ketik pilihan (mis. Hitam) lalu tekan Enter" autocomplete="off">
                            <div class="tier-tag tier-tag-inline">untuk: <span id="tierTag1">Warna</span></div>
                            <div id="chips1" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="tier-box h-100">
                            <div class="tier-tag">Variasi 2 <span class="fw-normal opacity-50">(opsional)</span></div>
                            <label class="flabel">Nama Variasi 2</label>
                            <input type="text" id="varName2" name="variant_name2" value="" maxlength="50" placeholder="mis. Ukuran / Tinggi" class="form-control-admin w-100" oninput="VB.rebuild()">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="tier-box h-100">
                            <div class="tier-tag">Pilihan</div>
                            <label class="flabel">Pilihan Variasi 2</label>
                            <input type="text" id="chipInput2" class="form-control-admin w-100" placeholder="Ketik pilihan lalu tekan Enter" autocomplete="off">
                            <div class="tier-tag tier-tag-inline">untuk: <span id="tierTag2">Variasi 2</span></div>
                            <div id="chips2" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>
                </div>

                <div id="matrixWrap" class="d-none mt-4">
                    <div class="matrix-bar">
                        <div>
                            <label class="flabel mb-1">Isi semua selisih harga (± Rp)</label>
                            <div class="d-flex gap-2">
                                <input type="number" id="bulkDelta" class="form-control-admin" style="width:120px" value="0">
                                <button type="button" class="btn btn-sm btn-admin-outline px-3" onclick="VB.fillAll('Delta')">Terapkan</button>
                            </div>
                        </div>
                        <div>
                            <label class="flabel mb-1">Isi semua stok</label>
                            <div class="d-flex gap-2">
                                <input type="number" id="bulkStock" class="form-control-admin" style="width:120px" min="0" value="1">
                                <button type="button" class="btn btn-sm btn-admin-outline px-3" onclick="VB.fillAll('Stock')">Terapkan</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0 vb-matrix" id="matrixTable">
                            <thead>
                                <tr id="matrixHead"></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Sidebar ===== -->
        <div class="col-lg-4">
            <div class="sticky-side">
                <div class="admin-card mb-4">
                    <div class="sec-head">
                        <span class="sec-icon" style="background:var(--success-bg);color:var(--success);"><i class="fas fa-wallet"></i></span>
                        <div>
                            <div class="sec-title">Inventori &amp; Harga</div>
                            <div class="sec-hint">Harga dasar produk sebelum selisih variasi.</div>
                        </div>
                    </div>
                    <div class="mb-4">
                    <label class="flabel">Harga Jual <span class="req">*</span></label>
                    <div class="money-field">
                        <span class="money-prefix">Rp</span>
                        <input type="number" name="price" class="form-control-admin num" placeholder="0" required min="0">
                    </div>
                    </div>
                    <div class="mb-0">
                        <label class="flabel">Stok Barang <span class="req">*</span></label>
                        <input type="number" name="stock" class="form-control-admin w-100 num" placeholder="0" required min="0">
                        <small class="form-text text-muted mt-1">Jika produk punya variasi, stok diatur per kombinasi di tabel variasi.</small>
                    </div>
                </div>

                <div class="admin-card mb-4">
                    <div class="sec-head">
                        <span class="sec-icon" style="background:var(--warning-bg);color:var(--warning);"><i class="fas fa-wand-magic-sparkles"></i></span>
                        <div>
                            <div class="sec-title">Fitur Khusus</div>
                        </div>
                    </div>
                    <div class="switch-row">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_custom" value="1" id="isCustomSwitch">
                            <label class="form-check-label small fw-bold" for="isCustomSwitch" style="color: var(--text-1);">
                                Produk bisa custom
                            </label>
                        </div>
                        <span class="switch-badge"><i class="fas fa-pen-ruler me-1"></i>Pesanan Kustom</span>
                    </div>
                    <p class="text-muted small mb-0 mt-3">Pelanggan dapat menulis permintaan custom saat membeli dan mengunggah gambar referensi saat checkout.</p>
                </div>

                <div class="admin-card mb-4">
                    <div class="sec-head">
                        <span class="sec-icon" style="background:var(--accent-soft);color:var(--accent);"><i class="fas fa-image"></i></span>
                        <div>
                            <div class="sec-title">Media Produk</div>
                            <div class="sec-hint">Gunakan foto rasio 1:1 untuk hasil terbaik.</div>
                        </div>
                    </div>
                    <div class="upload-dropzone" onclick="document.getElementById('imgInput').click()">
                        <img id="imgPreview" src="https://placehold.co/400x300/f1f5f9/94a3b8?text=Upload+Foto" class="img-fluid rounded mb-3">
                        <p class="small text-muted mb-0"><i class="fas fa-cloud-arrow-up me-1"></i> Klik untuk pilih gambar produk</p>
                    </div>
                    <input type="file" id="imgInput" name="image" accept="image/*" class="d-none" onchange="previewImg(this)">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-admin-primary py-3 fw-bold">
                        <i class="fas fa-save me-2"></i> Simpan Produk Baru
                    </button>
                    <a href="<?= base_url('admin/produk') ?>" class="btn btn-link text-muted small text-decoration-none">Batalkan tanpa menyimpan</a>
                </div>
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
                    '<td class="fw-semibold">' + c + '</td>' +
                    (e.o2.length ? '<td class="fw-semibold">' + s + '</td>' : '') +
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
[1, 2].forEach(tier => {
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
</script>
<script>
// Sinkronkan label kotak pilihan dengan nama variasi agar tidak tertukar
(function () {
    function syncTierTags() {
        const n1 = document.getElementById('varName1').value.trim();
        const n2 = document.getElementById('varName2').value.trim();
        document.getElementById('tierTag1').textContent = (n1 || 'Variasi 1');
        document.getElementById('tierTag2').textContent = (n2 || 'Variasi 2 (belum dinamai)');
    }
    document.addEventListener('DOMContentLoaded', syncTierTags);
    document.getElementById('varName1').addEventListener('input', syncTierTags);
    document.getElementById('varName2').addEventListener('input', syncTierTags);
})();
</script>
