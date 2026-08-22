# Rencana: Redesign Admin CMS Profesional (Light)

## Keputusan terkunci
| Aspek | Pilihan |
|---|---|
| Tema | Light professional (ala Shopify/Stripe) |
| Netral | Keluarga **slate** (`#f8fafc` bg, `#e2e8f0` border, `#0f172a` teks) |
| Aksen | **Indigo korporat `#4f46e5`** (tidak bentrok warna semantik status) |
| Font | Tetap **Outfit** — rapikan skala/bobot + `tabular-nums` untuk angka |
| Cakupan | Visual + UX (toast, modal konfirmasi, breadcrumb, drawer, empty state) |
| Batasan | **Nol perubahan controller/model/route** — hanya CSS + view |

## Audit singkat (kondisi lama)
- Perang `!important` memaksa semua teks putih/bold; ukuran font inline acak (0.6–0.72rem ×20+)
- `badge-paid/pending/shipped/rejected` dipakai 2 view tapi tak pernah didefinisikan di CSS
- Tanpa topbar/breadcrumb/drawer mobile; konten tanpa max-width
- `confirm()` native ×4; alert flashdata hanya di v_produk; tanpa empty state/toast/skeleton
- Chart.js masih palet gelap lama; ~40+ inline style

## Kontrak kompatibilitas (nama kelas DIPERTAHANKAN)
`.admin-sidebar .sidebar-logo .nav-item-admin .admin-main .admin-card .admin-card-title .glass-card .table-admin .form-control-admin .form-select-admin .stat-card/-icon/-value/-label .btn-admin-primary .btn-admin-outline .admin-badge badge-pending/paid/shipped/rejected/processed/cancelled .text-admin-accent .bg-admin-accent-soft .ai-float .ai-pulse .hover-lift .glow-text .admin-row-hover .btn-delete-admin .vb-chip`

Kelas baru: `.admin-topbar .breadcrumb-admin(.crumb-root/-sep/-current) .topbar-right .topbar-date .sidebar-toggle .sidebar-overlay .profile-menu .avatar-circle(.avatar-customer/.avatar-sm) .page-head .page-title .page-sub .toolbar .filter-pills .filter-pill .icon-btn(.btn-delete) .toast-stack .toast-item(.t-success/t-error/t-info) .empty-state(.empty-icon) .info-panel(.p-success/p-warning/p-danger/p-info) .status-steps li(.done/.current) .badge-neutral .badge-role-admin/.badge-role-customer .upload-dropzone .admin-page .num`

## Urutan eksekusi

### 1. `assets/css/admin.css` — tulis ulang penuh (~700 baris)
- Token: slate netral, indigo aksen, semantik (emerald/amber/red/sky), bayangan tinted slate, radius 8/12/16
- Base: hapus perang !important; fokus `:focus-visible`; scrollbar halus; `.num` tabular-nums
- Shell: sidebar putih + drawer transform + overlay; topbar sticky blur; `.admin-page` max-width 1280px
- Komponen: card, stat, button (+`.icon-btn`), form (+dropzone), table (padat 13px, hover surface-2), badge status lengkap (termasuk yang tadinya hilang), avatar, pagination, dropdown profil, modal, toast, empty state, vb-chip versi light, info-panel, timeline `.status-steps`
- Utilitas lama dipertahankan: ai-float/pulse (diredam), glow-text→netral

### 2. `v_header.php` — shell baru
- Sidebar light: logo JiDoor (ikon pintu indigo), nav + badge chat unread (query existing `count_unread_admin`)
- Topbar sticky: hamburger (mobile), breadcrumb "Admin / {title}", chip tanggal, dropdown profil (Lihat Toko, Logout)
- Overlay drawer + `<div class="admin-page">` pembuka (ditutup footer)
- Toast stack container

### 3. `v_footer.php` — helper JS global
- `showToast(msg, type)` + auto-convert elemen `.js-flash[data-type][data-msg]` → toast
- Delegasi `[data-confirm]`: form/link dengan atribut itu membuka modal konfirmasi reusable (pengganti `confirm()` native)
- Tooltip init tetap

### 4. Pass per-halaman (11 view) — buang utilitas gelap (`text-white`, `bg-dark`, `border-secondary`), ganti ke token; Chart.js pakai var `--chart-grid/--chart-tick` + palet indigo
1. `v_index` — page-head, stat cards (ikon soft-bg semantik), chart line+doughnut restyle, tabel terlaris/terbaru/stok rendah + link edit stok diperbaiki ke `/admin/produk/edit/`
2. `v_pesanan` — filter pills emoji→badge dot, tabel rapi, tombol Manage outline
3. `v_pesanan_detail` — timeline status `.status-steps` (pending→paid→processed→shipped→received), panel info, form verifikasi + `data-confirm`
4. `v_produk` — toolbar + pencarian klien sederhana, ikon-btn aksi + `data-confirm` hapus
5. `v_users` — avatar gradient→`.avatar-circle`, badge role baru
6. `v_ratings` — filter panel light, `data-confirm` hapus, bintang amber
7. `v_kategori` — modal light (hapus `bg-admin-card text-white btn-close-white`), `data-confirm` hapus
8. `v_produk_tambah` / `v_produk_edit` — **builder varian Shopee (objek VB, submit handler, prefill) TIDAK disentuh**; hanya markup sekitar + dropzone + chip (CSS pusat)
9. `v_chat_inbox` / `v_chat_thread` — **WS, kartu produk, picker, chip balasan TIDAK disentuh**; hanya wrapper/kelas visual
10. `v_rekomendasi` — restyle panel/stat/tabel/chart ke light; logic fetch AJAX & K-optimization tidak diubah

### 5. QA
- `php -l` semua file tersentuh
- Smoke test curl: login admin → 200 untuk /admin, produk, kategori, pesanan(+detail), ratings, users, chat(+thread), rekomendasi
- Alur kritis manual: tambah+edit produk dengan matriks varian; verifikasi pesanan; kirim chat admin→customer
- Responsif 3 breakpoint (drawer <992px)

## Risiko & mitigasi
| Risiko | Mitigasi |
|---|---|
| Builder varian rusak saat edit markup | Hanya sentuh tag pembungkus; uji tambah/edit produk setelahnya |
| Chat WS rusak | JS chat tidak diubah sama sekali, hanya kelas visual |
| Cache CSS lama | Bump `admin.css?v=2.0` di header |
| View lama masih punya kelas gelap sesaat | Semua nama kelas inti tetap hidup di CSS baru sehingga tidak pernah "pecah" di tengah jalan |
