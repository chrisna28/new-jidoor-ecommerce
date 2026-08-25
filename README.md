# 🚪 JiDoor Store — E-Commerce CodeIgniter 3 + AI Recommendation Engine

JiDoor adalah platform e-commerce lengkap (toko pintu & aksesoris) yang dibangun dengan **CodeIgniter 3** untuk aplikasi utama dan **FastAPI (Python)** untuk mesin rekomendasi berbasis **Collaborative Filtering**. Pembayaran online didukung **Midtrans Snap** (sandbox siap uji), dilengkapi chat realtime, panel admin, dan desain storefront **Editorial Luxe**.

---

## ✨ Fitur Utama

| Modul | Deskripsi |
| --- | --- |
| **Storefront** | Desain "Editorial Luxe" v3.1 — tipografi serif Playfair Display + Jost, scroll-reveal, marquee, grain film (`assets/css/style.css?v=3.1`) |
| **Autentikasi** | Login, register, logout, **lupa password sederhana** (email terdaftar → langsung atur password baru, tanpa email/token). Password di-hash bcrypt; akun MD5 lama otomatis di-upgrade saat login |
| **Katalog** | Listing + filter kategori, pencarian, halaman detail dengan varian (warna/ukuran + stok per varian), ulasan & rating bintang |
| **Wishlist** | Tandai produk disukai (`likes`), halaman "Disukai" |
| **Keranjang & Checkout** | Cart per user, qty stepper, checkout dengan alamat, catatan, gambar referensi custom |
| **Pembayaran Online** | **Midtrans Snap sandbox** — popup pembayaran, verifikasi status via API, perubahan status pesanan otomatis |
| **Pembayaran Manual** | Transfer bank + upload bukti, diverifikasi admin |
| **Pesanan** | Riwayat, detail, timeline tracking (pending → paid → shipped → delivered/cancelled), konfirmasi barang diterima |
| **Chat Realtime** | WebSocket Ratchet (`chat-server.php`, port 8080) antara customer dan admin, plus offline message |
| **Admin Panel** | CRUD produk & varian, kelola pesanan + verifikasi pembayaran, resi & pengiriman, users, moderasi rating, dashboard rekomendasi |
| **AI Recommendation** | Pure Collaborative Filtering (User-Based + Item-Based hybrid) multi-sinyal via `python_api` |

---

## 📁 Struktur Proyek

```
new-jidoor-ecommerce/
├── application/
│   ├── config/            ← routes.php, database.php (TCP 127.0.0.1:8889), midtrans.php, .env loader di index.php
│   ├── controllers/       ← Welcome (storefront), Auth, Cart, Order, Payment (webhook), Chat, Admin
│   ├── models/            ← M_user, M_product, M_cart, M_order, M_rating, M_chat
│   └── views/frontend/    ← v_header, v_footer, v_home, v_katalog, v_detail, v_cart,
│                             v_checkout, v_pesanan_detail, v_lupa_password, auth pages, dst.
├── assets/css/style.css   ← Design system "Editorial Luxe" (cache-bust versi ?v=3.1)
├── uploads/               ← products/, payments/, custom/
├── python_api/            ← recommendation_engine.py, main.py (FastAPI), search_integration.py
├── chat-server.php        ← Daemon WebSocket Ratchet (ws://localhost:8080/chat)
├── ecommerce_db.sql       ← Skema + seed database
├── db_migration_revisi.sql← Migrasi tambahan (varian, tracking, snap token, chat, dll.)
├── .env                   ← Kredensial lokal (JANGAN di-commit; lihat .env.example)
└── vendor/                ← midtrans/midtrans-php, cboden/ratchet (Composer)
```

---

## ✅ Persyaratan

- **MAMP/XAMPP** — Apache (port 8888) + MySQL 8 (port 8889), PHP ≥ 8.1
- **Composer** (`composer install` untuk `vendor/`)
- **Python 3.10+** dengan pip (untuk mesin rekomendasi)

---

## 🚀 Quick Start

### 1. Database

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h127.0.0.1 -P8889 -uroot -proot -e "CREATE DATABASE IF NOT EXISTS ecommerce_db"
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h127.0.0.1 -P8889 -uroot -proot ecommerce_db < ecommerce_db.sql
/Applications/MAMP/Library/bin/mysql80/bin/mysql -h127.0.0.1 -P8889 -uroot -proot ecommerce_db < db_migration_revisi.sql
```

> Aplikasi terhubung via **TCP `127.0.0.1:8889`** (bukan socket) — lihat `application/config/database.php`. Ini menghindari error *socket hilang* pada macOS.

### 2. Environment (.env)

Salin `.env.example` menjadi `.env`, lalu isi:

```ini
CI_ENVIRONMENT        = development

DB_HOST               = 127.0.0.1
DB_PORT               = 8889
DB_USER               = root
DB_PASSWORD           = root
DB_NAME               = ecommerce_db

PY_API_BASE_URL       = http://127.0.0.1:8000

MIDTRANS_SERVER_KEY   = SB-MidServer-xxxxxxxx
MIDTRANS_CLIENT_KEY   = SB-MidClient-xxxxxxxx
MIDTRANS_IS_PRODUCTION= false
```

Kunci sandbox didapat dari **dashboard.sandbox.midtrans.com → Settings → Access Keys**.
File `application/config/midtrans.php` akan menampilkan error jelas jika `.env` belum berisi kunci.

### 3. Dependensi PHP

```bash
composer install
```

### 4. Jalankan Aplikasi

Nyalakan Apache & MySQL dari MAMP, lalu buka:

```
http://localhost:8888/new-jidoor-ecommerce/
```

### 5. Jalankan Mesin Rekomendasi (opsional, untuk home & detail)

```bash
cd python_api
python -m venv venv && source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```

### 6. Jalankan Chat Realtime (opsional)

```bash
php chat-server.php     # listen ws://localhost:8080/chat
```

---

## 👤 Akun Default

| Role | Username | Password | Catatan |
| --- | --- | --- | --- |
| Admin | `admin` | `admin123` | Panel `/admin` — segera ganti setelah instalasi |
| User | — | — | Daftar melalui halaman **Register** |

---

## 💳 Alur Pembayaran Midtrans Sandbox

```
[Detail Pesanan — status: pending]
        │ klik "BAYAR SEKARANG" (#pay-button)
        ▼
GET /pesanan/bayar/{id}                ← Order::bayar_midtrans()
  • order_id unik: JIDOOR-{id}-{timestamp}
  • Minta Snap token (server key) + simpan snap_token & midtrans_order_id ke tabel orders
        ▼
Popup Snap (snap.js, client key)  → pilih metode bayar sandbox
        │ onSuccess / onPending
        ▼
GET /pesanan/midtrans-finish/{id}      ← Order::midtrans_finish()
  • Verifikasi ULANG ke API Transaction::status(midtrans_order_id)  ← anti-pemalsuan
  • capture/settlement    → mark_paid_if_pending()      → status: PAID
  • deny/cancel/expire    → mark_cancelled_if_pending() → status: CANCELLED
        ▼
Redirect ke detail pesanan (+ flash message)
```

**Dua jalur pembaruan status (saling melengkapi, sama-sama idempotent):**

1. **Finish redirect** — berjalan tanpa konfigurasi tambahan, cocok untuk demo lokal (tidak butuh ngrok).
2. **Webhook** `POST /payment/notification` — cadangan bila pengguna menutup browser sebelum redirect; sudah dikecualikan dari CSRF (`config.php` → `csrf_exclude_uris`). Aktif hanya jika server dapat diakses publik.

**Data uji sandbox:**

| Metode | Cara |
| --- | --- |
| Kartu Kredit (sukses) | `4811 1111 1111 1114`, CVV `123`, exp bebas masa depan, OTP simulator `112233` |
| Kartu Kredit (deny) | `4911 1111 1111 1113` |
| BCA/Permata VA | Salin nomor VA lalu "bayar" di `simulator.sandbox.midtrans.com` |

Tidak ada uang sungguhan — sandbox murni simulasi, tetapi alur API dan perubahan statusnya identik dengan produksi.

---

## 🔑 Lupa Password (Alur Sederhana)

Satu langkah, tanpa email/token:

```
/lupa-password → isi Email Terdaftar + Password Baru + Konfirmasi
→ Jika email terdaftar: password diperbarui (bcrypt) → redirect login
→ Jika tidak: flash "Email tidak terdaftar"
```

Implementasi: `Auth::lupa_password()` + `application/views/frontend/v_lupa_password.php`.

---

## 🗺️ Rute Utama

| Rute | Fungsi |
| --- | --- |
| `/` , `/katalog`, `/kategori/{slug}`, `/search` | Beranda & katalog |
| `/produk/{slug}` | Detail produk (varian, ulasan, rekomendasi) |
| `/login`, `/register`, `/logout`, `/lupa-password` | Autentikasi |
| `/keranjang`, `/keranjang/tambah\|update\|hapus/{id}` | Keranjang |
| `/checkout`, `/checkout/proses`, `/checkout/bukti/{id}` | Checkout & upload bukti transfer |
| `/pesanan`, `/pesanan/detail/{id}`, `/pesanan/diterima/{id}` | Riwayat, detail, konfirmasi terima |
| `/pesanan/bayar/{id}`, `/pesanan/midtrans-finish/{id}` | Snap token & verifikasi pembayaran |
| `/payment/notification` | Webhook Midtrans (POST, tanpa CSRF) |
| `/disukai`, `/rate`, `/riwayat-rating` | Wishlist, beri rating, riwayat rating |
| `/chat` | Chat customer–admin |
| `/admin/*` | Panel admin (produk, pesanan, users, ratings, chat) |

---

## 🎨 Design System "Editorial Luxe"

Seluruh storefront memakai satu file `assets/css/style.css` (versi cache-bust di `v_header.php`, saat ini `?v=3.1`):

- **Token warna**: `--paper #f6f2ea`, `--ink #1a1511`, `--coal #17120d`, `--accent #a44e1e`, `--gold #c08a2f`
- **Komponen khas**: announce-bar gelap, navbar glass `.navbar-v2`, hero serif raksasa, band marquee, seksi bernomor, hairline cards, footer wordmark outline, timeline tracking `.tl-*`
- **Motion**: `[data-reveal]` IntersectionObserver (script ada di `v_footer.php`, aman untuk konten dinamis) dengan failsafe 2,5 detik; overlay grain `.grain`
- **Auth**: layout split-screen `a2-*` (panel foto editorial + form)

> Setelah mengubah CSS, naikkan versi `?v=` di `v_header.php` agar browser tidak memakai cache lama.

---

## 🤖 AI Recommendation Engine (Ringkasan)

Mesin CF murni (tanpa content-based): skor akhir gabungan User-Based (60%) + Item-Based (40%) dengan normalisasi global, cosine similarity atas pivot rating multi-sinyal, mean-centering kondisional (sparsity < 85%), co-occurrence penalty, time-decay, dan strategi cold start.

**Sinyal interaksi:** purchase 5.0 · explicit rating 1–5 · wishlist 2.5 · cart 2.0 · view 1.5 (× faktor decay waktu).

**Endpoint utama (`http://127.0.0.1:8000`):**

| Endpoint | Method | Fungsi |
| --- | --- | --- |
| `/recommend/{user_id}?top_n=8&metadata=true` | GET | Rekomendasi utama |
| `/recommend/sections/{user_id}` | GET | Rekomendasi per-seksi (beranda) |
| `/recommend/item/{product_id}?top_n=4` | GET | Produk serupa (halaman detail) |
| `/track/view` | POST | Catat view `{user_id, product_id, session_id}` |
| `/admin/stats` | GET | Statistik model (density, coverage, confidence) |
| `/cache/refresh` | POST | Paksa hitung ulang similarity |

Integrasi PHP memakai `PY_API_BASE_URL` dari `.env`; jika API mati, halaman tetap tampil tanpa seksi rekomendasi. Detail algoritma lengkap tersedia dalam kode `python_api/recommendation_engine.py`.

---

## 🐛 Troubleshooting

| Masalah | Solusi |
| --- | --- |
| "Database Error" / socket MySQL hilang | Pastikan MySQL jalan di **port 8889**; aplikasi sudah dikonfigurasi TCP `127.0.0.1:8889`, bukan socket |
| "Konfigurasi Pembayaran Belum Lengkap" | Isi `MIDTRANS_SERVER_KEY` & `MIDTRANS_CLIENT_KEY` di `.env` |
| Status pesanan tidak berubah setelah bayar | Pastikan redirect `midtrans-finish` terjadi (popup onSuccess); cek `midtrans_order_id` di tabel `orders`; webhook hanya untuk server publik |
| Rekomendasi kosong | Jalankan `uvicorn main:app` (port 8000) dan pastikan `PY_API_BASE_URL` benar |
| Perubahan CSS tidak muncul | Naikkan versi `?v=` pada tag `<link>` style.css |
| Chat tidak konek | Jalankan `php chat-server.php` (WebSocket port 8080) |

---

## 📝 Changelog Singkat

- **Storefront redesign "Editorial Luxe" v3.1** — seluruh halaman frontend & auth ditata ulang; semua kontrak data/JS dipertahankan.
- **Lupa password disederhanakan** — reset langsung via email terdaftar (kode token/email SMTP & `M_password_reset` dihapus).
- **Verifikasi pembayaran dua jalur** — finish-redirect (lokal) + webhook (produksi), keduanya idempotent.
- **Engine CF v5.3** — filter skor 0, dynamic explore distribution, fix KNN assignment.

---

**© 2026 JiDoor Store — CodeIgniter 3 · FastAPI Collaborative Filtering · Midtrans Snap**
