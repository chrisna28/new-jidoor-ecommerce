# 📋 Dokumentasi Revisi & Penambahan Fitur — JiDoor E-Commerce

> **Dokumen kerja resmi** untuk seluruh perubahan sistem dan penambahan fitur.
> Semua spesifikasi di sini mengacu pada struktur kode aktual proyek (CodeIgniter 3).

| Informasi | Detail |
|---|---|
| Proyek | JiDoor E-Commerce (skripsi) |
| Nilai awal | Rp 900.000 |
| Nilai revisi & fitur baru | Rp 1.750.000 |
| Stack | CodeIgniter 3 · PHP 8.4 (MAMP) · MySQL · Bootstrap · Python API (rekomendasi) |
| Tanggal dokumen | 21 Agustus 2026 |

---

## Daftar Isi

1. [Lingkup Pekerjaan](#1-lingkup-pekerjaan)
2. [Perubahan Database](#2-perubahan-database)
3. [Spesifikasi Fitur](#3-spesifikasi-fitur)
   - [F1 — Count Like & Komentar](#f1--count-like--komentar-gratis)
   - [F2 — Lupa Password](#f2--lupa-password-gratis)
   - [F3 — Varian Warna/Ukuran + Redesign Detail](#f3--varian-warnaukuran--redesign-detail-produk)
   - [F4 — Produk Custom (Gambar + Teks)](#f4--produk-custom-gambar--teks)
   - [F5 — Tracking Barang (Opsi A Manual)](#f5--tracking-barang-opsi-a-manual)
   - [F6 — Transaksi Midtrans Snap](#f6--transaksi-midtrans-snap-sandbox)
   - [F7 — Chat Real-time (WebSocket)](#f7--chat-real-time-websocket-ratchet)
4. [Catatan Keamanan](#4-catatan-keamanan)
5. [Checklist Demo Sidang](#5-checklist-demo-sidang)
6. [Checklist Pengujian](#6-checklist-pengujian)

---

## 1. Lingkup Pekerjaan

### Perubahan Sistem

| # | Item | Status Biaya |
|---|---|---|
| 1 | Penambahan count/jumlah like dan komentar | ✅ Gratis — termasuk revisi perjanjian awal |
| 2 | Redesign produk detail: 1 barang banyak warna & ukuran + catatan | 💰 Rp 300–400rb |
| 3 | Barang custom: pelanggan memasukkan gambar & teks | 💰 Rp 200–300rb |
| 4 | Lupa password | ✅ Gratis — termasuk revisi perjanjian awal |

### Penambahan Fitur

| # | Item | Status Biaya |
|---|---|---|
| 1 | Chat real-time (WebSocket/Ratchet) | 💰 Rp 450–600rb |
| 2 | Transaksi pembelian (Midtrans Snap sandbox + transfer manual) | 💰 Rp 400–500rb |
| 3 | Tracking barang (Opsi A — manual timeline) | 💰 Rp 150–250rb |

### Keputusan yang Disepakati

- Checkout menampilkan **dua metode**: Midtrans Snap **dan** transfer manual (berdampingan).
- Chat hanya **customer ↔ admin** (inbox di sisi admin).
- Fitur custom berlaku **per produk** melalui flag `is_custom` yang dicentang admin.
- Tracking memakai **Opsi A murni** (tanpa API kurir eksternal) — aman untuk demo offline.

---

## 2. Perubahan Database

Simpan sebagai `db_migration_revisi.sql` lalu eksekusi sekali. **Aman terhadap data lama**
(tidak ada kolom yang dihapus; enum baru tetap memuat nilai lama).

```sql
-- ============================================================
-- MIGRASI REVISI JIDOOR — jalankan sekali
-- ============================================================

-- ------------------------------------------------------------
-- 1. VARIAN PRODUK (warna & ukuran per produk)
-- ------------------------------------------------------------
CREATE TABLE product_variants (
  id          INT NOT NULL AUTO_INCREMENT,
  product_id  INT NOT NULL,
  color       VARCHAR(50) DEFAULT NULL COMMENT 'Nama warna, mis. Hitam',
  size        VARCHAR(50) DEFAULT NULL COMMENT 'Ukuran, mis. XL / 42',
  stock       INT NOT NULL DEFAULT 0,
  price_delta DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Selisih harga dari harga dasar',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_variant_product (product_id),
  CONSTRAINT fk_variant_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 2. LIKE PRODUK
-- ------------------------------------------------------------
CREATE TABLE likes (
  id         INT NOT NULL AUTO_INCREMENT,
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_like (user_id, product_id),
  KEY fk_like_user (user_id),
  KEY fk_like_product (product_id),
  CONSTRAINT fk_like_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_like_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 3. TOKEN RESET PASSWORD
-- ------------------------------------------------------------
CREATE TABLE password_resets (
  id         INT NOT NULL AUTO_INCREMENT,
  email      VARCHAR(150) NOT NULL,
  token_hash CHAR(64) NOT NULL COMMENT 'SHA-256 dari token, bukan token mentah',
  expires_at DATETIME NOT NULL,
  used       TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pr_email (email),
  KEY idx_pr_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 4. PERCAKAPAN CHAT (1 baris per customer)
-- ------------------------------------------------------------
CREATE TABLE conversations (
  id              INT NOT NULL AUTO_INCREMENT,
  user_id         INT NOT NULL,
  last_message_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  unread_admin    INT NOT NULL DEFAULT 0 COMMENT 'Pesan belum dibaca admin',
  unread_user     INT NOT NULL DEFAULT 0 COMMENT 'Pesan belum dibaca customer',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_conversation (user_id),
  CONSTRAINT fk_conv_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 5. PESAN CHAT
-- ------------------------------------------------------------
CREATE TABLE messages (
  id              INT NOT NULL AUTO_INCREMENT,
  conversation_id INT NOT NULL,
  sender_id       INT NOT NULL,
  sender_role     ENUM('user','admin') NOT NULL,
  message         TEXT NOT NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_msg_conversation (conversation_id),
  CONSTRAINT fk_msg_conversation FOREIGN KEY (conversation_id)
    REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 6. RIWAYAT TRACKING PESANAN
-- ------------------------------------------------------------
CREATE TABLE order_tracking (
  id          INT NOT NULL AUTO_INCREMENT,
  order_id    INT NOT NULL,
  status      VARCHAR(30) NOT NULL,
  description VARCHAR(255) DEFAULT NULL COMMENT 'Keterangan bebas dari admin',
  resi        VARCHAR(100) DEFAULT NULL,
  courier     VARCHAR(50)  DEFAULT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_track_order (order_id),
  CONSTRAINT fk_track_order FOREIGN KEY (order_id)
    REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 7. ALTER TABEL LAMA
-- ------------------------------------------------------------

-- Produk custom (flag oleh admin)
ALTER TABLE products
  ADD COLUMN is_custom TINYINT(1) NOT NULL DEFAULT 0 AFTER stock;

-- Keranjang: varian + catatan
ALTER TABLE cart
  ADD COLUMN variant_id INT NULL DEFAULT NULL AFTER product_id,
  ADD COLUMN note TEXT NULL AFTER qty,
  ADD CONSTRAINT fk_cart_variant FOREIGN KEY (variant_id)
    REFERENCES product_variants(id) ON DELETE SET NULL;

-- Item pesanan: snapshot varian + catatan + data custom
ALTER TABLE order_items
  ADD COLUMN variant_id   INT NULL AFTER product_id,
  ADD COLUMN color        VARCHAR(50) NULL AFTER variant_id,
  ADD COLUMN size         VARCHAR(50) NULL AFTER color,
  ADD COLUMN note         TEXT NULL AFTER size,
  ADD COLUMN custom_image VARCHAR(255) NULL AFTER note,
  ADD COLUMN custom_text  TEXT NULL AFTER custom_image;

-- Pesanan: resi, kurir, token Midtrans, perluasan status
-- Enum baru TETAP memuat semua nilai lama (pending/paid/shipped/rejected)
ALTER TABLE orders
  ADD COLUMN resi       VARCHAR(100) NULL AFTER status,
  ADD COLUMN courier    VARCHAR(50)  NULL AFTER resi,
  ADD COLUMN snap_token VARCHAR(255) NULL AFTER courier,
  MODIFY COLUMN status ENUM(
    'pending','paid','processed','shipped','delivered','rejected','cancelled'
  ) NOT NULL DEFAULT 'pending';

-- ------------------------------------------------------------
-- 8. DATA AWAL: satu varian default utk produk lama agar tidak error
-- ------------------------------------------------------------
INSERT INTO product_variants (product_id, color, size, stock, price_delta)
SELECT id, 'Standar', 'Standar', stock, 0 FROM products
WHERE id NOT IN (SELECT product_id FROM product_variants);
```

> ⚠️ **Perhatian:** query pada langkah 8 mengisi varian "Standar" untuk semua produk lama
> sehingga keranjang & stok lama tetap berfungsi sebelum admin mengisi varian asli.

---

## 3. Spesifikasi Fitur

Urutan pengerjaan = nomor fitur. Setiap fitur mencantumkan **file aktual** yang dibuat/diubah.

---

### F1 — Count Like & Komentar (Gratis)

**Alur:** pengguna login menekan tombol ❤️ pada produk → tersimpan unik per user per produk
(tekan lagi = batal like). Jumlah like & jumlah komentar tampil di kartu produk dan halaman detail.

**Perubahan:**

| File | Perubahan |
|---|---|
| `application/models/M_product.php` | Tambah method: `toggle_like($user_id, $product_id)`, `count_likes($product_id)`, `is_liked_by($user_id, $product_id)` |
| `application/models/M_rating.php` | Tambah `count_reviews($product_id)` → `COUNT(review)` dengan `review IS NOT NULL AND review <> ''` |
| `application/controllers/Welcome.php` | Endpoint AJAX `like_toggle($product_id)` — cek session, return JSON `{liked, total}` |
| `application/views/frontend/v_home.php` | Badge ❤️ n + 💬 n pada kartu produk |
| `application/views/frontend/v_katalog.php` | Sama dengan v_home |
| `application/views/frontend/v_detail.php` | Tombol like besar + counter komentar di bagian ulasan |

**Logika kunci:**

```php
// M_product.php — toggle aman duplikat
public function toggle_like($user_id, $product_id) {
    $exists = $this->db->get_where('likes', [
        'user_id' => $user_id, 'product_id' => $product_id
    ])->row();
    if ($exists) {
        $this->db->delete('likes', ['id' => $exists->id]);
        $liked = FALSE;
    } else {
        $this->db->insert('likes', [
            'user_id' => $user_id, 'product_id' => $product_id
        ]);
        $liked = TRUE;
    }
    $total = $this->db->where('product_id', $product_id)
                      ->count_all_results('likes');
    return ['liked' => $liked, 'total' => $total];
}
```

Tamu (belum login) yang menekan like → redirect ke `login` dengan flash message.

---

### F2 — Lupa Password (Gratis)

**Alur:** halaman login → tautan "Lupa password?" → masukkan email → sistem kirim tautan
`reset-password/{token}` (berlaku 60 menit, sekali pakai) → form password baru → login ulang.

**Perubahan:**

| File | Perubahan |
|---|---|
| `application/config/email.php` | **Baru** — konfigurasi SMTP |
| `application/controllers/Auth.php` | Method baru: `lupa_password()` (form + proses), `reset_password($token)` (form + proses) |
| `application/models/M_user.php` | Tambah `get_by_email($email)`, `update_password($user_id, $hash)` |
| `application/models/M_password_reset.php` | **Baru** — simpan/validasi token |
| `application/views/frontend/v_lupa_password.php` | **Baru** |
| `application/views/frontend/v_reset_password.php` | **Baru** |
| `application/views/frontend/v_login.php` | Tambah tautan "Lupa password?" |

**Konfigurasi SMTP (`application/config/email.php`):**

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol']   = 'smtp';
$config['smtp_host']  = 'ssl://smtp.gmail.com';
$config['smtp_port']  = 465;
$config['smtp_user']  = 'emailanda@gmail.com';       // ganti
$config['smtp_pass']  = 'APP-PASSWORD-16-KARAKTER';  // Gmail App Password
$config['charset']    = 'utf-8';
$config['mailtype']   = 'html';
$config['newline']    = "\r\n";
```

**Logika kunci (Auth.php):**

```php
public function lupa_password() {
    if ($this->input->post()) {
        $email = $this->input->post('email', TRUE);
        $user  = $this->M_user->get_by_email($email);
        if ($user) {
            $token     = bin2hex(random_bytes(32));           // 64 char acak
            $token_hash = hash('sha256', $token);             // DB menyimpan hash saja
            $this->M_password_reset->create([
                'email'      => $email,
                'token_hash' => $token_hash,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+60 minutes')),
            ]);
            $link = site_url('reset-password/' . $token);
            // Kirim email (lihat template di bawah)
            $this->_send_reset_email($email, $user->username, $link);
        }
        // Respons sama baik email terdaftar atau tidak (anti user-enumeration)
        $this->session->set_flashdata('success',
            'Jika email terdaftar, tautan reset telah dikirim.');
        redirect('login');
    }
    // ... load view v_lupa_password
}

public function reset_password($token = NULL) {
    if (!$token) redirect('login');
    $row = $this->M_password_reset->find_valid($hash('sha256', $token));
    // find_valid: WHERE token_hash = ? AND used = 0 AND expires_at > NOW()
    if (!$row) {
        $this->session->set_flashdata('error', 'Tautan tidak valid atau kedaluwarsa.');
        redirect('login');
    }
    if ($this->input->post()) {
        // validasi min_length[6] + matches[confirm]
        $this->M_user->update_password_by_email(
            $row->email, password_hash($this->input->post('password'), PASSWORD_DEFAULT)
        );
        $this->M_password_reset->mark_used($row->id);
        redirect('login');
    }
    // ... load view v_reset_password
}
```

**Fallback demo tanpa internet:** jika `$this->email->send()` gagal, tulis tautan reset ke
`application/logs/reset_links.log` dan tampilkan tautan lewat flash message **hanya saat**
`ENVIRONMENT === 'development'`.

**⚠️ Migrasi MD5 → password_hash (sekalian, wajib):**

Sistem lama memakai `md5()` (Auth.php:45). Strategi tanpa merusak akun lama:

```php
// login_aksi() — verifikasi ganda
if ($user && password_verify($password, $user->password)) {
    // hash modern: langsung lolos
} elseif ($user && $user->password === md5($password)) {
    // akun lama: upgrade diam-diam ke bcrypt
    $this->M_user->update_password($user->id,
        password_hash($password, PASSWORD_DEFAULT));
} else {
    // gagal login
}
```

Registrasi baru (`register_aksi()`) diganti menjadi `password_hash($password, PASSWORD_DEFAULT)`.

---

### F3 — Varian Warna/Ukuran + Redesign Detail Produk

**Alur:** admin mendefinisikan kombinasi warna × ukuran + stok per kombinasi.
Pelanggan memilih warna (swatch) → ukuran aktif menyesuaikan stok → isi catatan opsional → keranjang.

**Perubahan:**

| File | Perubahan |
|---|---|
| `application/controllers/Admin.php` | Form produk: array input varian (`color[]`, `size[]`, `stock[]`, `price_delta[]`); simpan via transaksi DB |
| `application/models/M_product.php` | `save_variants($product_id, $array)`, `get_variants($product_id)`, `get_variant($variant_id)`, `reduce_variant_stock($variant_id, $qty)` |
| `application/views/admin/v_produk_tambah.php` | Repeater baris varian (JS tambah/hapus baris) |
| `application/views/admin/v_produk_edit.php` | Sama + varian existing terisi |
| `application/views/frontend/v_detail.php` | **Redesign total**: galeri, swatch warna, pilihan ukuran, indikator stok per varian, field catatan, like/komentar |
| `application/controllers/Cart.php` | Validasi kombinasi varian; kunci unik `(user_id, product_id, variant_id)` |
| `application/models/M_cart.php` | Query join `product_variants`; harga final = `products.price + price_delta` |
| `application/views/frontend/v_cart.php` | Tampilkan warna/ukuran/catatan per item |

**Logika kunci frontend (v_detail.php):**

```javascript
// Data varian dirender PHP ke JSON
const variants = <?= json_encode($variants) ?>;

document.querySelectorAll('.color-swatch').forEach(el => {
  el.addEventListener('click', () => selectColor(el.dataset.color));
});

function selectColor(color) {
  const sizes = variants.filter(v => v.color === color);
  renderSizeButtons(sizes);               // ukuran dinonaktifkan jika stock == 0
  updateStockInfo(sizes[selectedSize]);   // "Sisa 5" / "Habis"
}
```

**Aturan bisnis:**

1. Produk tanpa varian admin → otomatis punya varian "Standar" (dari migrasi §2 langkah 8).
2. Stok divalidasi **per varian** saat add-to-cart, saat checkout, dan saat verifikasi pembayaran.
3. Pengurangan stok terjadi saat admin **memverifikasi pembayaran** (bukan saat checkout).
4. `note` (catatan) maksimal 200 karakter, bersifat opsional, ikut tersimpan ke `order_items.note`.

---

### F4 — Produk Custom (Gambar + Teks)

**Alur:** admin centang "Produk bisa custom" pada form produk. Di halaman detail produk tsb,
pelanggan mengunggah gambar referensi (jpg/png, maks 2 MB) + menulis teks permintaan custom.

**Perubahan:**

| File | Perubahan |
|---|---|
| `application/views/admin/v_produk_tambah.php` / `v_produk_edit.php` | Checkbox `is_custom` |
| `application/views/frontend/v_detail.php` | Blok kondisional `if ($product->is_custom)`: input file + textarea |
| `application/controllers/Cart.php` | Teruskan `custom_text` ke sesi keranjang; validasi panjang maks 500 karakter |
| `application/controllers/Order.php` / `Cart.php` (proses checkout) | Upload file: `uploads/custom/{order_id}/{item}_{timestamp}.jpg` |
| `application/views/frontend/v_pesanan_detail.php` | Tampilkan thumbnail gambar custom + teks |
| `application/views/admin/v_pesanan_detail.php` | Sama, agar admin tahu permintaan custom sebelum produksi |

**Logika kunci upload (saat proses checkout, bukan saat add-to-cart):**

```php
// Validasi & simpan gambar custom per item
if (!empty($_FILES['custom_image']['name'][$i])) {
    $_FILES['file']['name']     = $_FILES['custom_image']['name'][$i];
    $_FILES['file']['type']     = $_FILES['custom_image']['type'][$i];
    $_FILES['file']['tmp_name'] = $_FILES['custom_image']['tmp_name'][$i];
    $_FILES['file']['error']    = $_FILES['custom_image']['error'][$i];
    $_FILES['file']['size']     = $_FILES['custom_image']['size'][$i];

    $config['upload_path']   = './uploads/custom/' . $order_id . '/';
    $config['allowed_types'] = 'jpg|jpeg|png';
    $config['max_size']      = 2048;                       // KB
    $config['encrypt_name']  = TRUE;
    // mkdir uploads/custom/{order_id} dengan mode 0755 sebelum upload
    $this->load->library('upload', $config);

    if ($this->upload->do_upload('file')) {
        $custom_image = 'uploads/custom/' . $order_id . '/' .
                        $this->upload->data('file_name');
    } else {
        // batalkan transaksi order, tampilkan error validasi
    }
}
```

**Aturan bisnis:**

1. Gambar custom **tidak wajib** — pelanggan boleh hanya mengisi teks (atau sebaliknya), tapi minimal salah satu terisi jika produk custom dipesan.
2. File disaat checkout (bukan keranjang) supaya tidak menumpuk file yatim jika keranjang ditinggal.
3. Folder `uploads/custom/` ditambahkan ke `.gitignore` (isi bersifat data user).

---

### F5 — Tracking Barang (Opsi A Manual)

**Alur:** setiap perubahan status oleh admin otomatis tercatat di `order_tracking`.
Pelanggan melihat timeline vertikal di detail pesanan + nomor resi saat status "dikirim".

**Perubahan:**

| File | Perubahan |
|---|---|
| `application/models/M_order.php` | Ubah `update_status()` → menerima `keterangan, resi, courier`, insert baris `order_tracking`; tambah `get_tracking($order_id)` |
| `application/controllers/Admin.php` | `verify_payment()` juga insert tracking "Pembayaran diverifikasi"; form ubah status baru |
| `application/views/admin/v_pesanan_detail.php` | Panel "Update Status": dropdown status + keterangan + (resi & kurir, wajib jika status = shipped) |
| `application/views/frontend/v_pesanan_detail.php` | Timeline vertikal + kartu resi + tombol "Pesanan Diterima" |
| `application/controllers/Order.php` | Method `terima($order_id)` — customer konfirmasi terima (shipped → delivered) |
| `application/config/routes.php` | Tambah `$route['pesanan/diterima/(:num)'] = 'order/terima/$1';` |

**Alur status lengkap:**

```
pending ──verifikasi──▶ paid ──admin──▶ processed ──admin(+resi)──▶ shipped
                                                                      │
                                              customer "Diterima" ◀───┘
                                                        ▼
                                                   delivered

rejected : verifikasi pembayaran ditolak (dari pending)
cancelled: dibatalkan admin/customer (opsional)
```

**Logika kunci (M_order.php):**

```php
public function update_status($order_id, $status, $keterangan = NULL,
                              $resi = NULL, $courier = NULL) {
    $this->db->trans_start();

    $data = ['status' => $status];
    if ($resi)    { $data['resi']    = $resi; }
    if ($courier) { $data['courier'] = $courier; }
    $this->db->where('id', $order_id)->update('orders', $data);

    $this->db->insert('order_tracking', [
        'order_id'    => $order_id,
        'status'      => $status,
        'description' => $keterangan,
        'resi'        => $resi,
        'courier'     => $courier,
    ]);

    $this->db->trans_complete();
    return $this->db->trans_status();
}

public function get_tracking($order_id) {
    return $this->db->where('order_id', $order_id)
                    ->order_by('created_at', 'ASC')
                    ->get('order_tracking')->result();
}
```

**Auto-tracking awal:** saat checkout sukses, insert baris pertama
`status='pending', description='Pesanan dibuat, menunggu pembayaran'`.

**Timeline UI (v_pesanan_detail.php):** daftar vertikal dengan ikon ✓ untuk status terlewati,
titik berdenyut untuk status aktif, abu-abu untuk yang belum. Kartu resi menampilkan
`{courier} — {resi}` dengan tombol salin.

---

### F6 — Transaksi Midtrans Snap (Sandbox)

**Alur:** checkout → pilih "Bayar Online" → order dibuat (status `pending`) → Snap popup muncul
(QRIS/VA/kartu/e-wallet) → bayar sukses → status otomatis jadi `paid` → admin lanjut proses.

**Instalasi:**

```bash
composer require midtrans/midtrans-php
```

**File baru/diubah:**

| File | Perubahan |
|---|---|
| `index.php` | Tambah `require_once 'vendor/autoload.php';` di atas bootstrap CI |
| `application/config/midtrans.php` | **Baru** — server/client key sandbox |
| `application/controllers/Order.php` | `bayar_midtrans($order_id)` buat token; `midtrans_finish()` cek status via API |
| `application/controllers/Payment.php` | **Baru** — endpoint notifikasi webhook |
| `application/views/frontend/v_checkout.php` | Tab "Transfer Manual" & "Bayar Online" |
| `application/views/frontend/v_pesanan_detail.php` | Tombol "Bayar Sekarang" untuk order pending yang belum dibayar |
| `application/config/routes.php` | `payment/notification`, `pesanan/bayar/(:num)`, `pesanan/midtrans-finish` |
| `application/config/config.php` | Tambah `'payment/notification'` ke `$config['csrf_exclude_uris']` |

**Konfigurasi (`application/config/midtrans.php`):**

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Ambil dari https://dashboard.sandbox.midtrans.com/settings/config_info
define('MIDTRANS_SERVER_KEY', 'SB-MidServer-xxxxxxxxxxxxxxxxxxxx');
define('MIDTRANS_CLIENT_KEY', 'SB-MidClient-xxxxxxxxxxxxxxxxxxxx');
define('MIDTRANS_IS_PRODUCTION', FALSE);
```

**Buat token Snap (Order.php) — kode resmi Midtrans:**

```php
private function _init_midtrans() {
    require_once APPPATH . 'config/midtrans.php';
    \Midtrans\Config::$serverKey    = MIDTRANS_SERVER_KEY;
    \Midtrans\Config::$isProduction = MIDTRANS_IS_PRODUCTION;
    \Midtrans\Config::$isSanitized  = TRUE;
    \Midtrans\Config::$is3ds        = TRUE;
}

public function bayar_midtrans($order_id) {
    $user_id = $this->session->userdata('user_id');
    $order   = $this->M_order->get_order_detail($order_id);
    if (!$order || $order->user_id != $user_id || $order->status !== 'pending') {
        redirect('pesanan');
    }
    $this->_init_midtrans();

    $params = [
        'transaction_details' => [
            'order_id'     => 'JIDOOR-' . $order_id . '-' . time(), // harus unik
            'gross_amount' => (int) $order->total_price,
        ],
        'customer_details' => [
            'first_name' => $order->receiver_name ?: $this->session->userdata('username'),
            'email'      => $this->session->userdata('email'),
            'phone'      => $order->phone,
        ],
        'item_details' => $this->_build_item_details($order_id), // dari order_items
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        $this->M_order->save_snap_token($order_id, $snapToken);
        echo json_encode(['token' => $snapToken]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
```

**Popup Snap (v_pesanan_detail.php) — kode resmi Midtrans:**

```html
<!-- TODO: hapus ".sandbox" dari URL script jika nanti production -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<script>
document.getElementById('pay-button').onclick = function () {
    fetch('<?= site_url("pesanan/bayar/{$order->id}") ?>')
        .then(r => r.json())
        .then(res => {
            snap.pay(res.token, {
                onSuccess: function (result) {
                    window.location = '<?= site_url("pesanan/midtrans-finish/{$order->id}") ?>';
                },
                onPending: function (result) {
                    window.location = '<?= site_url("pesanan/detail/{$order->id}") ?>';
                },
                onError: function (result) {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                }
            });
        });
};
</script>
```

**Verifikasi status — dua jalur:**

1. **Webhook (production-ready):** `Payment::notification()` menerima POST dari Midtrans.
   **Selalu verifikasi ulang ke API Get Status — jangan percaya payload mentah:**

   ```php
   public function notification() {
       $this->_init_midtrans();
       try {
           $notif = new \Midtrans\Notification();
           // Verifikasi ulang ke server Midtrans (anti-forgery)
           $transaction = \Midtrans\Transaction::status($notif->order_id);

           $order_id = (int) explode('-', $transaction->order_id)[1];

           if (in_array($transaction->transaction_status, ['capture', 'settlement'])) {
               $this->M_order->mark_paid_if_pending($order_id); // idempotent
           } elseif (in_array($transaction->transaction_status, ['deny', 'cancel', 'expire'])) {
               $this->M_order->mark_cancelled_if_pending($order_id);
           }
           http_response_code(200);
       } catch (Exception $e) {
           log_message('error', 'Midtrans notify: ' . $e->getMessage());
           http_response_code(500);
       }
   }
   ```

2. **Demo lokal (localhost tidak bisa menerima webhook):** saat Snap callback `onSuccess`
   mengarahkan ke `pesanan/midtrans-finish/{id}`, server memanggil
   `\Midtrans\Transaction::status()` sendiri lalu memperbarui status. **Inilah alasan demo
   sidang tidak butuh ngrok / hosting publik.**

**Transfer manual tetap ada:** tab pertama checkout mempertahankan alur lama
(upload bukti → `v_upload_bukti.php` → verifikasi admin).

---

### F7 — Chat Real-time (WebSocket/Ratchet)

**Alur:** pelanggan menekan tombol chat mengambang → panel chat terbuka → pesan terkirim
instan via WebSocket → admin melihat inbox + balas dari panel admin. Riwayat persisten di MySQL.

**Instalasi:**

```bash
composer require cboden/ratchet:^0.4.4
```

**File baru/diubah:**

| File | Perubahan |
|---|---|
| `chat-server.php` | **Baru (root)** — daemon WebSocket, port 8080 |
| `application/libraries/Chat_Token.php` | **Baru** — buat/validasi token HMAC utk autentikasi WS |
| `application/controllers/Chat.php` | **Baru** — halaman/history/unread utk customer |
| `application/controllers/Admin.php` | Tambah `chat()` (inbox) & `chat_thread($conversation_id)` |
| `application/views/frontend/components/v_chat_widget.php` | **Baru** — tombol mengambang + panel |
| `application/views/frontend/v_header.php` | Include widget + generate token |
| `application/views/admin/v_chat_inbox.php` | **Baru** |
| `application/views/admin/v_chat_thread.php` | **Baru** |
| `application/models/M_chat.php` | **Baru** — get_or_create conversation, kirim pesan, unread counters |
| `application/config/routes.php` | `chat`, `chat/history`, `admin/chat`, `admin/chat/(:num)` |

**Daemon (`chat-server.php`) — pola resmi Ratchet:**

```php
<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/vendor/autoload.php';

class ChatServer implements MessageComponentInterface {
    protected $clients;   // SplObjectStorage: conn => meta(userId, role)
    protected $users;     // userId => conn (untuk routing)

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users   = [];
        $this->db      = new PDO(
            'mysql:host=localhost;dbname=jidoor_db;charset=utf8mb4',
            'root', 'password');   // samakan dgn application/config/database.php
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        // Pesan pertama = autentikasi
        if ($data['type'] === 'auth') {
            if (!ChatValidator::check($data['token'], $data['user_id'], $data['role'])) {
                $from->close();
                return;
            }
            $from->meta = ['user_id' => $data['user_id'], 'role' => $data['role']];
            $this->users[$data['user_id']] = $from;
            return;
        }

        // Pesan chat: simpan DB lalu route ke penerima jika online
        if ($data['type'] === 'message') {
            $convId = $this->saveMessage($from->meta, $data['text']);
            $targetId = $this->getRecipientId($from->meta, $convId);
            if (isset($this->users[$targetId])) {
                $this->users[$targetId]->send(json_encode([
                    'type'    => 'message',
                    'from'    => $from->meta['user_id'],
                    'role'    => $from->meta['role'],
                    'text'    => $data['text'],
                    'sent_at' => date('H:i'),
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        if (isset($conn->meta)) unset($this->users[$conn->meta['user_id']]);
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }

    private function saveMessage(array $meta, string $text): int { /* INSERT via PDO */ }
    private function getRecipientId(array $meta, int $convId): int  { /* user_id pemilik conv */ }
}

// Jalankan di port 8080, route /chat
$app = new Ratchet\App('localhost', 8080);
$app->route('/chat', new ChatServer, ['*']);
$app->run();
```

**Autentikasi WS (Chat_Token.php):** halaman yang dirender CI menyematkan token
`hash_hmac('sha256', $user_id . '|' . $role, CHAT_SECRET)` + timestamp. Daemon memvalidasi
HMAC dan usia token (< 24 jam) tanpa perlu akses session CI. Cukup aman untuk lingkup skripsi.

**Klien (v_chat_widget.php):**

```javascript
function connectChat() {
    ws = new WebSocket('ws://localhost:8080/chat');
    ws.onopen = () => ws.send(JSON.stringify({
        type: 'auth', token: CHAT_TOKEN,
        user_id: USER_ID, role: ROLE
    }));
    ws.onmessage = (e) => appendMessage(JSON.parse(e.data));
    ws.onclose = () => setTimeout(connectChat, 3000); // auto-reconnect
}
```

**Perilaku penting:**

1. Riwayat dimuat via HTTP (`chat/history`) saat panel dibuka — WebSocket hanya untuk pesan baru.
2. Jika daemon mati, widget menampilkan "Admin tidak sedang online — tinggalkan pesan"
   dan pesan tetap tersimpan via HTTP fallback (`chat/offline_message`).
3. Unread counter (`unread_admin` / `unread_user`) diperbarui daemon + saat load history.
4. Inbox admin diurutkan `last_message_at DESC` dengan badge jumlah belum dibaca.

**Menjalankan saat development/demo:**

```bash
cd /Applications/MAMP/htdocs/new-jidoor-ecommerce
php chat-server.php
# Server listening on ws://localhost:8080
```

---

## 4. Catatan Keamanan

| Area | Langkah |
|---|---|
| Password | Migrasi `md5` → `password_hash`/`password_verify` (strategi upgrade-on-login, lihat F2) |
| Reset token | DB menyimpan SHA-256 hash, bukan token mentah; kedaluwarsa 60 menit; sekali pakai |
| User enumeration | Respons "lupa password" identik untuk email terdaftar/tidak |
| Upload custom | Whitelist `jpg|jpeg|png`, maks 2 MB, `encrypt_name`, folder per order |
| Webhook Midtrans | Verifikasi ulang via `\Midtrans\Transaction::status()`; endpoint dikecualikan dari CSRF **hanya** `payment/notification` |
| WS auth | Token HMAC + batas usia; koneksi tanpa token valid ditutup |
| SQL injection | Semua query via Query Builder CI / prepared statement PDO (daemon) |
| XSS output | `htmlspecialchars()` pada semua output pesan chat & catatan |

---

## 5. Checklist Demo Sidang

**Persiapan malam sebelumnya:**

- [ ] Import `db_migration_revisi.sql` — pastikan tanpa error
- [ ] `composer install` (folder `vendor/` lengkap)
- [ ] Isi Server Key & Client Key sandbox Midtrans di `application/config/midtrans.php`
- [ ] Isi SMTP di `application/config/email.php` (atau siapkan mode fallback log)
- [ ] Isi data contoh: ≥ 3 produk dengan varian, 1 produk custom, 1 akun customer & admin
- [ ] Tes penuh alur pembelian sekali dari awal sampai akhir

**Urutan start hari-H:**

```bash
# 1. Nyalakan MAMP (Apache + MySQL)
# 2. Jalankan daemon chat di terminal terpisah:
cd /Applications/MAMP/htdocs/new-jidoor-ecommerce && php chat-server.php
# 3. Buka dua browser: http://localhost/new-jidoor-ecommerce (customer) + /admin
```

**Skenario demo yang disarankan (± 15 menit):**

1. Register/login customer → lupa password → reset via email/log
2. Detail produk: pilih warna + ukuran, lihat stok berubah, isi catatan, like ❤️
3. Produk custom: upload gambar + teks → checkout
4. Checkout: bayar via Midtrans Snap (kartu uji `4811 1111 1111 1114`, CVV `123`,
   exp bulan depan, OTP `112233`) **atau** QRIS simulator sandbox
5. Admin: verifikasi → processed → shipped + input resi
6. Customer: buka tracking timeline → klik "Pesanan Diterima"
7. Chat real-time dua arah customer ↔ admin (butuh 2 browser)
8. Tunjukkan count like & komentar bertambah realtime

**Cadangan jika internet mati:** gunakan transfer manual + fallback log token reset +
chat tetap jalan (WebSocket lokal). Tracking manual tidak butuh internet sama sekali.

---

## 6. Checklist Pengujian

### F1 Like & Komentar
- [x] Like bertambah/batal saat ditekan berulang; tamu diarahkan login *(teruji via curl)*
- [x] Count komentar hanya menghitung review berisi teks
- [x] Count tampil konsisten di home, katalog, dan detail

### F2 Lupa Password
- [x] Email terkirim (atau fallback log berisi tautan) *(fallback dev teruji)*
- [ ] Token kedaluwarsa (>60 mnt) ditolak; token terpakai tidak bisa dipakai lagi *(reuse token teruji: ditolak)*
- [x] Login dengan password baru sukses; akun lama (MD5) tetap bisa login & ter-upgrade otomatis

### F3 Varian — ✅ SELESAI & TERUJI (di-upgrade ke gaya Shopee)
- [x] Kombinasi warna×ukuran benar; ukuran habis nonaktif (stok 0 → tombol disabled)
- [x] Stok per varian berkurang tepat saat checkout *(deviasi dari dokumen: stok dikurangi di `process_checkout`, bukan saat verifikasi pembayaran — mengikuti perilaku existing aplikasi)*
- [x] Item keranjang sama produk beda varian = baris terpisah (unik per user+produk+varian)
- [x] Catatan ikut tersimpan & tampil di keranjang, checkout, detail pesanan customer & admin
- [x] Harga efektif = harga produk + delta varian (teruji: 1.000.000 + 100.000 × 2 = 2.200.000)
- [x] Produk tanpa varian nyata ("Standar" tunggal) tetap kompatibel — selector disembunyikan
- **Upgrade ala Shopee (2026-08):**
  - [x] Nama variasi fleksibel per produk (`products.variant_name1/2`, default Warna/Ukuran) — bisa "Motif", "Finishing", dll.
  - [x] Form admin ala Seller Centre: input nama variasi + pilihan berupa chip/tag → **matriks kombinasi otomatis** dengan isi massal harga/stok; kontrak backend `variant_color[]/size[]/stock[]/price_delta[]` tidak berubah
  - [x] Dukung produk 1 tingkat (variasi 2 kosong → size tersimpan 'Standar', pembeli melihat 1 grup chip saja — contoh: Topi, Lanyard)
  - [x] Halaman pembeli: label dinamis ("Pilih Motif"), chip pill ala Shopee (aktif = outline tebal), stok total sebelum memilih + ringkasan kombinasi terpilih
  - [x] Label badge hilir (keranjang/checkout/detail pesanan) ikut nama variasi produk
  - Catatan: pesanan lama menampilkan nama variasi *baru* bila admin me-rename setelah transaksi (nilai snapshot tetap akurat)
- **Seed data dikonsolidasikan**: 315 produk lama pola "{Tipe} {Model} {Warna}" digabung menjadi 9 produk (Kaos, Kaos Custom*, Polo, PDL, Rompi, Jaket, Lanyard†, Topi†, Setelan) dengan matriks Warna × Ukuran (S–XXL); † = 1 tingkat tanpa ukuran; * = is_custom aktif. Harga dasar = rata-rata tipe (dibulatkan 5.000), delta warna = selisih rata-rata warna vs dasar (bisa minus). Data transaksi demo lama (order/rating/wishlist) dibersihkan karena ID produk berubah.

### F4 Custom — ✅ SELESAI & TERUJI
- [x] Checkbox admin menyembunyikan/menampilkan form custom (switch `is_custom` di form tambah & edit)
- [x] Upload >2 MB / format salah ditolak dengan pesan jelas (validasi CI: jpg|jpeg|png, 2048 KB)
- [x] Gambar & teks tampil di detail pesanan (customer & admin)
- [x] Aturan "minimal teks ATAU gambar" teruji: tanpa keduanya → checkout ditolak, tanpa order yatim, stok utuh
- [x] File tersimpan di `uploads/custom/{order_id}/{encrypt}.png` saat checkout (bukan add-to-cart); folder masuk `.gitignore`
- Catatan implementasi: input file ditempatkan di halaman checkout (mengikuti aturan bisnis #2 dokumen), textarea teks custom di halaman detail produk.

### F5 Tracking — ✅ SELESAI & TERUJI
- [x] Setiap perubahan status membuat baris riwayat baru (teruji: pending→paid→processed→shipped→delivered = 5 baris)
- [x] Resi wajib saat status shipped; tampil di sisi customer (kartu resi + tombol salin; tanpa resi → ditolak)
- [x] Tombol "Pesanan Diterima" hanya muncul saat shipped (shipped → delivered teruji)
- [x] Timeline vertikal: ✓ hitam untuk status terlewati, titik berdenyut oranye untuk status aktif
- [x] Tracking awal "Pesanan dibuat" otomatis saat checkout
- [x] Panel admin "Update Status": dropdown dinamis per status + keterangan + field resi/kurir kondisional

### F6 Midtrans — ✅ SELESAI (kode teruji; pembayaran sandbox butuh key asli)
- [x] `composer require midtrans/midtrans-php` terpasang; autoload di `index.php`
- [x] Checkout dua tab: "Bayar Online" (default) & "Transfer Manual" — alur manual tetap utuh
- [x] Tombol "Bayar Sekarang" + popup snap.js muncul di detail pesanan pending
- [x] AJAX token gagal mulus saat key placeholder (JSON error 401 dari Midtrans, tanpa crash)
- [x] Webhook `/payment/notification` bebas CSRF, selalu verifikasi ulang via Get Status API
- [x] `midtrans-finish` verifikasi ulang via API → cocok untuk demo lokal tanpa ngrok
- [x] Idempotent: `mark_paid_if_pending` / `mark_cancelled_if_pending` mencegah status ganda
- [ ] Snap popup terbuka; pembayaran sandbox sukses → status `paid` otomatis *(isi key asli lalu uji dengan kartu 4811 1111 1111 1114)*
- Catatan: kolom baru `orders.midtrans_order_id` untuk mapping status API (token Snap tidak bisa dipakai query status).

### F7 Chat — ✅ SELESAI & TERUJI
- [x] Pesan muncul real-time di sisi penerima (teruji via klien WS: customer↔admin dua arah)
- [x] Riwayat tetap ada setelah refresh (dimuat via `chat/history` saat panel dibuka)
- [x] Daemon mati → widget menampilkan "Admin tidak sedang online", pesan tersimpan via HTTP fallback (`chat/offline-message` teruji)
- [x] Admin melihat badge unread di inbox; membuka thread mereset counter (teruji)
- [x] Token HMAC + usia 24 jam; token palsu ditolak server (koneksi ditutup)
- [x] Inbox admin terurut `last_message_at DESC`
- **Konteks produk ala Shopee (2026-08):**
  - [x] Tombol "Chat Penjual" di halaman detail → membuka widget dengan kartu mini produk di atas input + chip pertanyaan cepat ("Ready?", "Bisa custom?", "Estimasi kirim?")
  - [x] Konteks menempel ke **pesan** (`messages.product_id`) — tiap pertanyaan bisa beda produk; kartu produk dirender inline di bubble customer & admin, klik membuka `/produk/{slug}` tab baru
  - [x] product_id tidak valid otomatis disimpan NULL (pesan tetap terkirim); pesan tanpa konteks = perilaku lama utuh
  - [x] Teruji WS: dgn produk / tanpa produk / product palsu; HTTP fallback berkonteks; riwayat JSON membawa objek product
- **Sisi admin (2026-08):**
  - [x] **Kirim rekomendasi produk** — tombol "Produk" di thread membuka picker (thumbnail + nama + harga); kartu produk terlampir di balasan & muncul di sisi customer; preview produk bisa dilepas sebelum kirim
  - [x] **Chip balasan cepat** — Ready / Bisa custom / Estimasi kirim 2-3 hari / Terima kasih (mengisi input, bisa diedit dulu)
  - [x] **Preview inbox** — kolom Pesan Terakhir menampilkan cuplikan teks + ikon kotak bila pesan terakhir menyertakan produk (`M_chat::get_inbox` subselect `last_text` & `last_product_id`)
  - [x] Teruji: WS admin→customer dengan/tanpa produk; inbox render ikon hanya pada percakapan berkonteks
- Catatan: jalankan daemon sebelum demo — `php chat-server.php` (log: "Chat server siap"). PHP 8.4 deprecation notice dari Ratchet sudah didiamkan di dalam daemon.

---

*Dokumen ini menjadi acuan tunggal pengerjaan. Setiap tahap selesai = centang checklist
pengujian terkait. Perubahan lingkup hanya sah jika disepakati kedua belah pihak.*
