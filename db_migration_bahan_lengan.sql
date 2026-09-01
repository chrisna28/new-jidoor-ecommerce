-- =========================================================
-- Revisi: Pilihan Lengan & Bahan untuk Produk Custom
--  1. Lengan Pendek / Panjang dengan selisih harga
--  2. Bahan (material) kustom
--  3. Harga sablon berbeda per bahan
-- =========================================================

CREATE TABLE IF NOT EXISTS `custom_sleeves` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `price_delta` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `custom_materials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `fabric_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sablon_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `custom_sleeves` (`name`,`price_delta`,`sort_order`) VALUES
  ('Lengan Pendek', 0.00, 1),
  ('Lengan Panjang', 15000.00, 2);

INSERT INTO `custom_materials` (`name`,`fabric_price`,`sablon_price`,`sort_order`) VALUES
  ('Cotton Combed 20s', 0.00, 6000.00, 1),
  ('Cotton Combed 24s', 3000.00, 7000.00, 2),
  ('Cotton Combed 30s', 8000.00, 9000.00, 3),
  ('Dry Fit', 12000.00, 11000.00, 4);

ALTER TABLE `cart`
  ADD COLUMN `sleeve_id` int DEFAULT NULL AFTER `variant_id`,
  ADD COLUMN `material_id` int DEFAULT NULL AFTER `sleeve_id`,
  ADD KEY `fk_cart_sleeve` (`sleeve_id`),
  ADD KEY `fk_cart_material` (`material_id`),
  ADD CONSTRAINT `fk_cart_sleeve` FOREIGN KEY (`sleeve_id`) REFERENCES `custom_sleeves` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cart_material` FOREIGN KEY (`material_id`) REFERENCES `custom_materials` (`id`) ON DELETE SET NULL;

ALTER TABLE `order_items`
  ADD COLUMN `sleeve` varchar(50) DEFAULT NULL AFTER `size`,
  ADD COLUMN `material` varchar(100) DEFAULT NULL AFTER `sleeve`;
