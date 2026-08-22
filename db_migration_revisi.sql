-- ============================================================
-- MIGRASI REVISI JIDOOR — jalankan sekali
-- Tanggal: 2026-08-21
-- Aman terhadap data lama: tidak ada kolom yang dihapus,
-- enum status baru tetap memuat semua nilai lama.
-- ============================================================

-- ------------------------------------------------------------
-- 1. VARIAN PRODUK (warna & ukuran per produk)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
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
CREATE TABLE IF NOT EXISTS likes (
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
CREATE TABLE IF NOT EXISTS password_resets (
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
CREATE TABLE IF NOT EXISTS conversations (
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
CREATE TABLE IF NOT EXISTS messages (
  id              INT NOT NULL AUTO_INCREMENT,
  conversation_id INT NOT NULL,
  sender_id       INT NOT NULL,
  sender_role     ENUM('user','admin') NOT NULL,
  message         TEXT NOT NULL,
  product_id      INT NULL, -- konteks produk ala Shopee (pesan tanya barang spesifik)
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_msg_conversation (conversation_id),
  KEY idx_messages_product (product_id),
  CONSTRAINT fk_msg_conversation FOREIGN KEY (conversation_id)
    REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ------------------------------------------------------------
-- 6. RIWAYAT TRACKING PESANAN
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_tracking (
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

-- Nama variasi fleksibel ala Shopee (mis. 'Motif' / 'Tinggi')
ALTER TABLE products
  ADD COLUMN variant_name1 VARCHAR(50) NOT NULL DEFAULT 'Warna' AFTER is_custom,
  ADD COLUMN variant_name2 VARCHAR(50) NOT NULL DEFAULT 'Ukuran' AFTER variant_name1;

-- Keranjang: varian + catatan + teks custom
ALTER TABLE cart
  ADD COLUMN variant_id INT NULL DEFAULT NULL AFTER product_id,
  ADD COLUMN note TEXT NULL AFTER qty,
  ADD COLUMN custom_text TEXT NULL AFTER note;

-- Item pesanan: snapshot varian + catatan + data custom
ALTER TABLE order_items
  ADD COLUMN variant_id   INT NULL AFTER product_id,
  ADD COLUMN color        VARCHAR(50) NULL AFTER variant_id,
  ADD COLUMN size         VARCHAR(50) NULL AFTER color,
  ADD COLUMN note         TEXT NULL AFTER size,
  ADD COLUMN custom_image VARCHAR(255) NULL AFTER note,
  ADD COLUMN custom_text  TEXT NULL AFTER custom_image;

-- Pesanan: resi, kurir, token Midtrans, perluasan status
ALTER TABLE orders
  ADD COLUMN resi       VARCHAR(100) NULL AFTER status,
  ADD COLUMN courier    VARCHAR(50)  NULL AFTER resi,
  ADD COLUMN snap_token VARCHAR(255) NULL AFTER courier,
  ADD COLUMN midtrans_order_id VARCHAR(100) NULL AFTER snap_token,
  MODIFY COLUMN status ENUM(
    'pending','paid','processed','shipped','delivered','rejected','cancelled'
  ) NOT NULL DEFAULT 'pending';

-- FK keranjang -> varian
ALTER TABLE cart
  ADD CONSTRAINT fk_cart_variant FOREIGN KEY (variant_id)
    REFERENCES product_variants(id) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 8. DATA AWAL: satu varian default utk produk lama agar tidak error
-- ------------------------------------------------------------
INSERT INTO product_variants (product_id, color, size, stock, price_delta)
SELECT id, 'Standar', 'Standar', stock, 0 FROM products
WHERE id NOT IN (SELECT COALESCE(product_id, 0) FROM product_variants);
