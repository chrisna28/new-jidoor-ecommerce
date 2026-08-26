-- MySQL dump 10.13  Distrib 8.0.44, for macos12.7 (arm64)
--
-- Host: 127.0.0.1    Database: ecommerce_db
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `ecommerce_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ecommerce_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `ecommerce_db`;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `variant_id` int DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `note` text,
  `custom_text` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cart_user` (`user_id`),
  KEY `fk_cart_product` (`product_id`),
  KEY `fk_cart_variant` (`variant_id`),
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (35,200,324,NULL,3,'','','2026-08-22 11:00:00'),(36,200,326,NULL,2,'','','2026-08-23 11:00:00'),(37,201,329,NULL,2,'','','2026-08-23 11:00:00'),(38,201,326,NULL,1,'','','2026-08-24 11:00:00'),(39,202,322,NULL,3,'','','2026-08-22 11:00:00'),(40,202,328,NULL,1,'','','2026-08-24 11:00:00'),(41,203,323,NULL,2,'','','2026-08-23 11:00:00'),(42,203,324,NULL,1,'','','2026-08-24 11:00:00'),(43,204,327,NULL,1,'','','2026-08-24 11:00:00'),(44,204,330,NULL,2,'','','2026-08-23 11:00:00'),(45,205,325,NULL,1,'','','2026-08-24 11:00:00'),(46,205,330,NULL,2,'','','2026-08-23 11:00:00'),(47,206,325,NULL,2,'','','2026-08-23 11:00:00'),(48,206,327,NULL,1,'','','2026-08-24 11:00:00'),(49,207,325,NULL,1,'','','2026-08-24 11:00:00'),(50,207,330,NULL,1,'','','2026-08-24 11:00:00'),(51,208,326,NULL,1,'','','2026-08-24 11:00:00'),(52,208,330,NULL,1,'','','2026-08-24 11:00:00'),(53,209,322,NULL,2,'','','2026-08-23 11:00:00'),(54,209,329,NULL,1,'','','2026-08-24 11:00:00');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Kaos','kaos'),(2,'Kaos Custom','kaos-custom'),(3,'Polo','polo'),(4,'PDL','pdl'),(5,'Rompi','rompi'),(6,'Jaket','jaket'),(7,'Lanyard','lanyard'),(8,'Topi','topi'),(9,'Setelan','setelan');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `last_message_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unread_admin` int NOT NULL DEFAULT '0' COMMENT 'Pesan belum dibaca admin',
  `unread_user` int NOT NULL DEFAULT '0' COMMENT 'Pesan belum dibaca customer',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_conversation` (`user_id`),
  CONSTRAINT `fk_conv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (3,119,'2026-08-23 16:55:54',0,0,'2026-08-21 21:53:52');
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`user_id`,`product_id`),
  KEY `fk_like_user` (`user_id`),
  KEY `fk_like_product` (`product_id`),
  CONSTRAINT `fk_like_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (8,119,328,'2026-08-23 13:54:54'),(9,119,330,'2026-08-23 13:55:07'),(10,119,329,'2026-08-23 13:55:08'),(32,200,322,'2026-08-05 14:00:00'),(33,200,324,'2026-08-10 14:00:00'),(34,200,329,'2026-08-13 14:00:00'),(35,201,322,'2026-08-06 14:00:00'),(36,201,323,'2026-08-09 14:00:00'),(37,202,328,'2026-08-04 14:00:00'),(38,202,329,'2026-08-15 14:00:00'),(39,203,326,'2026-08-08 14:00:00'),(40,203,328,'2026-08-17 14:00:00'),(41,204,325,'2026-08-07 14:00:00'),(42,204,330,'2026-08-10 14:00:00'),(43,205,327,'2026-08-05 14:00:00'),(44,205,330,'2026-08-11 14:00:00'),(45,206,325,'2026-08-09 14:00:00'),(46,206,330,'2026-08-13 14:00:00'),(47,207,325,'2026-08-08 14:00:00'),(48,207,327,'2026-08-15 14:00:00'),(49,208,322,'2026-08-06 14:00:00'),(50,208,325,'2026-08-10 14:00:00'),(51,209,323,'2026-08-07 14:00:00');
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `sender_role` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `product_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_msg_conversation` (`conversation_id`),
  KEY `idx_messages_product` (`product_id`),
  CONSTRAINT `fk_msg_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (10,3,119,'user','Ready stok?',322,'2026-08-21 21:53:55'),(15,3,1,'admin','Ready',NULL,'2026-08-22 01:35:48'),(16,3,119,'user','halo kak saya ingin pesen kaos dengan desain custom apakah bisa?',NULL,'2026-08-23 16:51:05'),(17,3,1,'admin','bisa kak, nanti pesan yang saya rekomendasikan ya kak',NULL,'2026-08-23 16:53:11'),(18,3,1,'admin','ini ya kak',323,'2026-08-23 16:53:22'),(19,3,119,'user','halo kak, ini ready?',330,'2026-08-23 16:55:54');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `variant_id` int DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `note` text,
  `custom_image` varchar(255) DEFAULT NULL,
  `custom_text` text,
  `qty` int NOT NULL DEFAULT '1',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `fk_item_order` (`order_id`),
  KEY `fk_item_product` (`product_id`),
  CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (2,2,330,887,'Abu-abu','M',NULL,NULL,NULL,1,175000.00),(3,3,323,589,'Abu-abu','M',NULL,'uploads/custom/3/e7b168532f9481a88650b407e92c7269.jpeg','kjabkqjwe',1,60000.00),(9,8,323,609,'Hijau Army','M',NULL,'uploads/custom/8/98a8f407199cf2970c6182c7e3156105.jpg','Sesuaikan dengan gambar yang saya kirimkan',1,72000.00),(10,9,330,1152,'Navy','M',NULL,NULL,NULL,1,150000.00),(11,9,330,1098,'Abu-abu','M',NULL,NULL,NULL,1,150000.00),(12,10,330,1104,'Biru Muda','M',NULL,NULL,NULL,2,150000.00),(43,20,322,NULL,'Hitam','L',NULL,NULL,NULL,1,80000.00),(44,20,324,NULL,'Navy','M',NULL,NULL,NULL,1,75000.00),(45,21,323,NULL,'Biru Muda','L',NULL,NULL,NULL,1,75000.00),(46,21,329,NULL,'Hitam','Standar',NULL,NULL,NULL,1,60000.00),(47,22,322,NULL,'Putih','M',NULL,NULL,NULL,1,80000.00),(48,22,329,NULL,'Navy','Standar',NULL,NULL,NULL,1,70000.00),(49,23,323,NULL,'Cream','L',NULL,NULL,NULL,1,75000.00),(50,24,323,NULL,'Abu-abu','M',NULL,NULL,NULL,1,75000.00),(51,25,328,NULL,'Hitam','Standar',NULL,NULL,NULL,1,45000.00),(52,25,329,NULL,'Cream','Standar',NULL,NULL,NULL,1,75000.00),(53,26,322,NULL,'Maroon','S',NULL,NULL,NULL,1,80000.00),(54,26,326,NULL,'Navy','M',NULL,NULL,NULL,1,60000.00),(55,27,325,NULL,'Hitam','L',NULL,NULL,NULL,1,180000.00),(56,27,330,NULL,'Hitam','L',NULL,NULL,NULL,1,150000.00),(57,28,327,NULL,'Navy','XL',NULL,NULL,NULL,1,180000.00),(58,29,325,NULL,'Navy','M',NULL,NULL,NULL,1,180000.00),(59,29,330,NULL,'Cream','M',NULL,NULL,NULL,1,180000.00),(60,30,330,NULL,'Hitam','M',NULL,NULL,NULL,1,150000.00),(61,31,325,NULL,'Hijau Army','L',NULL,NULL,NULL,1,180000.00),(62,31,330,NULL,'Putih','L',NULL,NULL,NULL,1,150000.00),(63,32,327,NULL,'Hitam','L',NULL,NULL,NULL,1,180000.00),(64,33,325,NULL,'Hitam','XL',NULL,NULL,NULL,1,180000.00),(65,33,330,NULL,'Navy','XL',NULL,NULL,NULL,1,150000.00),(66,33,327,NULL,'Cream','XL',NULL,NULL,NULL,1,180000.00),(67,34,325,NULL,'Hitam','M',NULL,NULL,NULL,1,180000.00),(68,34,327,NULL,'Navy','M',NULL,NULL,NULL,1,180000.00),(69,35,322,NULL,'Kuning','L',NULL,NULL,NULL,1,80000.00),(70,36,326,NULL,'Hitam','M',NULL,NULL,NULL,1,60000.00),(71,37,323,NULL,'Merah','L',NULL,NULL,NULL,1,75000.00),(72,37,328,NULL,'Navy','Standar',NULL,NULL,NULL,1,45000.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_tracking`
--

DROP TABLE IF EXISTS `order_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_tracking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL COMMENT 'Keterangan bebas dari admin',
  `resi` varchar(100) DEFAULT NULL,
  `courier` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_track_order` (`order_id`),
  CONSTRAINT `fk_track_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_tracking`
--

LOCK TABLES `order_tracking` WRITE;
/*!40000 ALTER TABLE `order_tracking` DISABLE KEYS */;
INSERT INTO `order_tracking` VALUES (2,2,'pending','Pesanan dibuat, menunggu pembayaran',NULL,NULL,'2026-08-22 12:32:49'),(3,3,'pending','Pesanan dibuat, menunggu pembayaran',NULL,NULL,'2026-08-22 12:54:02'),(6,5,'processed','Pesanan sedang diproses','','','2026-08-23 03:19:42'),(7,5,'shipped','basdfef','12313123123123','JNT','2026-08-23 03:42:55'),(8,3,'paid','Pembayaran diverifikasi','','','2026-08-23 03:43:40'),(12,8,'pending','Pesanan dibuat, menunggu pembayaran',NULL,NULL,'2026-08-23 12:36:22'),(13,9,'pending','Pesanan dibuat, menunggu pembayaran',NULL,NULL,'2026-08-23 16:57:32'),(14,10,'pending','Pesanan dibuat, menunggu pembayaran',NULL,NULL,'2026-08-25 19:04:33'),(15,10,'paid','Pembayaran online diverifikasi via Midtrans',NULL,NULL,'2026-08-25 19:10:11'),(18,10,'processed','Pesanan sedang diproses','','','2026-08-25 20:06:02'),(19,10,'shipped','WIM CO','12313123123123','JNT','2026-08-25 20:06:22');
/*!40000 ALTER TABLE `order_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `total_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','paid','processed','shipped','delivered','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `resi` varchar(100) DEFAULT NULL,
  `courier` varchar(50) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `midtrans_order_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `note` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_user` (`user_id`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (2,119,'Mukraf Studio Magelang','123123123123',175000.00,'pending',NULL,NULL,'986a9b12-9f7c-4a04-9e19-8e569fa0db7d','JIDOOR-2-1787377784',NULL,'123213123','sdfweree','123qseqw','','2026-08-22 12:32:49'),(3,119,'Mukraf Studio Magelang','123123123123',60000.00,'paid',NULL,NULL,NULL,NULL,NULL,'mjhgdhngdc','kwjhrqwer','kafkljwhg','','2026-08-22 12:54:02'),(5,119,'UJI-RESTORE','0',1000.00,'shipped','12313123123123','JNT',NULL,NULL,NULL,'-','-','-',NULL,'2026-08-23 03:03:09'),(8,119,'Chrisna Mahendra','01234567890',72000.00,'pending',NULL,NULL,NULL,NULL,NULL,'Perum Jambewangi 1\r\nPerum Jambewangi 1','Magelang','Jawa Tengah — Central Java','','2026-08-23 12:36:22'),(9,119,'Chrisna Mahendra','01234567890',300000.00,'pending',NULL,NULL,'4c456d79-ed2f-4d67-aed1-4e6f8723ba30','JIDOOR-9-1787659163',NULL,'Perum Jambewangi 1\r\nPerum Jambewangi 1','Magelang','Jawa Tengah — Central Java','','2026-08-23 16:57:32'),(10,119,'Chrisna Mahendra','01234567890',300000.00,'shipped','12313123123123','JNT','aa5ebdc8-c0f9-47eb-ad3f-a357d1fd0911','JIDOOR-10-1787659768',NULL,'Perum Jambewangi 1\r\nPerum Jambewangi 1','Magelang','Jawa Tengah — Central Java','','2026-08-25 19:04:33'),(20,200,'User200','0812300002XX',155000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 200',NULL,NULL,NULL,'2026-08-06 09:00:00'),(21,200,'User200','0812300002XX',135000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 200',NULL,NULL,NULL,'2026-08-13 09:00:00'),(22,201,'User201','0812300002XX',150000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 201',NULL,NULL,NULL,'2026-08-07 09:00:00'),(23,201,'User201','0812300002XX',75000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 201',NULL,NULL,NULL,'2026-08-19 09:00:00'),(24,202,'User202','0812300002XX',75000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 202',NULL,NULL,NULL,'2026-08-10 09:00:00'),(25,202,'User202','0812300002XX',120000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 202',NULL,NULL,NULL,'2026-08-16 09:00:00'),(26,203,'User203','0812300002XX',140000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 203',NULL,NULL,NULL,'2026-08-14 09:00:00'),(27,204,'User204','0812300002XX',330000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 204',NULL,NULL,NULL,'2026-08-08 09:00:00'),(28,204,'User204','0812300002XX',180000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 204',NULL,NULL,NULL,'2026-08-17 09:00:00'),(29,205,'User205','0812300002XX',360000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 205',NULL,NULL,NULL,'2026-08-09 09:00:00'),(30,205,'User205','0812300002XX',150000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 205',NULL,NULL,NULL,'2026-08-18 09:00:00'),(31,206,'User206','0812300002XX',330000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 206',NULL,NULL,NULL,'2026-08-11 09:00:00'),(32,206,'User206','0812300002XX',180000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 206',NULL,NULL,NULL,'2026-08-20 09:00:00'),(33,207,'User207','0812300002XX',510000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 207',NULL,NULL,NULL,'2026-08-12 09:00:00'),(34,208,'User208','0812300002XX',360000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 208',NULL,NULL,NULL,'2026-08-13 09:00:00'),(35,208,'User208','0812300002XX',80000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 208',NULL,NULL,NULL,'2026-08-21 09:00:00'),(36,209,'User209','0812300002XX',60000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 209',NULL,NULL,NULL,'2026-08-15 09:00:00'),(37,209,'User209','0812300002XX',120000.00,'delivered',NULL,NULL,NULL,NULL,NULL,'Jl. Dummy 209',NULL,NULL,NULL,'2026-08-22 09:00:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token_hash` char(64) NOT NULL COMMENT 'SHA-256 dari token, bukan token mentah',
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  KEY `idx_pr_token` (`token_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `color` varchar(50) DEFAULT NULL COMMENT 'Nama warna, mis. Hitam',
  `size` varchar(50) DEFAULT NULL COMMENT 'Ukuran, mis. XL / 42',
  `stock` int NOT NULL DEFAULT '0',
  `price_delta` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Selisih harga dari harga dasar',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_variant_product` (`product_id`),
  CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1606 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1193,330,'Abu-abu','S',3,0.00,'2026-08-26 00:03:58'),(1194,330,'Abu-abu','M',6,0.00,'2026-08-26 00:03:58'),(1195,330,'Abu-abu','L',13,0.00,'2026-08-26 00:03:58'),(1196,330,'Abu-abu','XL',18,0.00,'2026-08-26 00:03:58'),(1197,330,'Abu-abu','XXL',23,0.00,'2026-08-26 00:03:58'),(1198,330,'Biru Muda','S',10,0.00,'2026-08-26 00:03:58'),(1199,330,'Biru Muda','M',13,0.00,'2026-08-26 00:03:58'),(1200,330,'Biru Muda','L',20,0.00,'2026-08-26 00:03:58'),(1201,330,'Biru Muda','XL',3,0.00,'2026-08-26 00:03:58'),(1202,330,'Biru Muda','XXL',8,0.00,'2026-08-26 00:03:58'),(1203,330,'Cokelat','S',17,0.00,'2026-08-26 00:03:58'),(1204,330,'Cokelat','M',22,0.00,'2026-08-26 00:03:58'),(1205,330,'Cokelat','L',5,0.00,'2026-08-26 00:03:58'),(1206,330,'Cokelat','XL',10,0.00,'2026-08-26 00:03:58'),(1207,330,'Cokelat','XXL',15,0.00,'2026-08-26 00:03:58'),(1208,330,'Cream','S',24,0.00,'2026-08-26 00:03:58'),(1209,330,'Cream','M',24,0.00,'2026-08-26 00:03:58'),(1210,330,'Cream','L',24,0.00,'2026-08-26 00:03:58'),(1211,330,'Cream','XL',24,0.00,'2026-08-26 00:03:58'),(1212,330,'Cream','XXL',24,0.00,'2026-08-26 00:03:58'),(1213,330,'Hijau Army','S',9,0.00,'2026-08-26 00:03:58'),(1214,330,'Hijau Army','M',9,0.00,'2026-08-26 00:03:58'),(1215,330,'Hijau Army','L',9,0.00,'2026-08-26 00:03:58'),(1216,330,'Hijau Army','XL',9,0.00,'2026-08-26 00:03:58'),(1217,330,'Hijau Army','XXL',9,0.00,'2026-08-26 00:03:58'),(1218,330,'Hitam','S',16,0.00,'2026-08-26 00:03:58'),(1219,330,'Hitam','M',16,0.00,'2026-08-26 00:03:58'),(1220,330,'Hitam','L',16,0.00,'2026-08-26 00:03:58'),(1221,330,'Hitam','XL',16,0.00,'2026-08-26 00:03:58'),(1222,330,'Hitam','XXL',16,0.00,'2026-08-26 00:03:58'),(1223,330,'Kuning','S',23,0.00,'2026-08-26 00:03:58'),(1224,330,'Kuning','M',23,0.00,'2026-08-26 00:03:58'),(1225,330,'Kuning','L',23,0.00,'2026-08-26 00:03:58'),(1226,330,'Kuning','XL',23,0.00,'2026-08-26 00:03:58'),(1227,330,'Kuning','XXL',23,0.00,'2026-08-26 00:03:58'),(1228,330,'Maroon','S',8,0.00,'2026-08-26 00:03:58'),(1229,330,'Maroon','M',8,0.00,'2026-08-26 00:03:58'),(1230,330,'Maroon','L',8,0.00,'2026-08-26 00:03:58'),(1231,330,'Maroon','XL',8,0.00,'2026-08-26 00:03:58'),(1232,330,'Maroon','XXL',8,0.00,'2026-08-26 00:03:58'),(1233,330,'Merah','S',15,0.00,'2026-08-26 00:03:58'),(1234,330,'Merah','M',15,0.00,'2026-08-26 00:03:58'),(1235,330,'Merah','L',15,0.00,'2026-08-26 00:03:58'),(1236,330,'Merah','XL',15,0.00,'2026-08-26 00:03:58'),(1237,330,'Merah','XXL',15,0.00,'2026-08-26 00:03:58'),(1238,330,'Navy','S',22,0.00,'2026-08-26 00:03:58'),(1239,330,'Navy','M',21,0.00,'2026-08-26 00:03:58'),(1240,330,'Navy','L',22,0.00,'2026-08-26 00:03:58'),(1241,330,'Navy','XL',22,0.00,'2026-08-26 00:03:58'),(1242,330,'Navy','XXL',22,0.00,'2026-08-26 00:03:58'),(1243,330,'Pink','S',7,0.00,'2026-08-26 00:03:58'),(1244,330,'Pink','M',7,0.00,'2026-08-26 00:03:58'),(1245,330,'Pink','L',7,0.00,'2026-08-26 00:03:58'),(1246,330,'Pink','XL',7,0.00,'2026-08-26 00:03:58'),(1247,330,'Pink','XXL',7,0.00,'2026-08-26 00:03:58'),(1248,330,'Putih','S',14,0.00,'2026-08-26 00:03:58'),(1249,330,'Putih','M',14,0.00,'2026-08-26 00:03:58'),(1250,330,'Putih','L',14,0.00,'2026-08-26 00:03:58'),(1251,330,'Putih','XL',14,0.00,'2026-08-26 00:03:58'),(1252,330,'Putih','XXL',14,0.00,'2026-08-26 00:03:58'),(1253,329,'Abu-abu','Standar',3,-15000.00,'2026-08-26 00:04:34'),(1254,329,'Biru Muda','Standar',10,-15000.00,'2026-08-26 00:04:34'),(1255,329,'Cokelat','Standar',17,-3000.00,'2026-08-26 00:04:34'),(1256,329,'Cream','Standar',24,0.00,'2026-08-26 00:04:34'),(1257,329,'Hijau Army','Standar',9,3000.00,'2026-08-26 00:04:34'),(1258,329,'Hitam','Standar',16,20000.00,'2026-08-26 00:04:34'),(1259,329,'Kuning','Standar',23,-7000.00,'2026-08-26 00:04:34'),(1260,329,'Maroon','Standar',8,-1000.00,'2026-08-26 00:04:34'),(1261,329,'Merah','Standar',15,4000.00,'2026-08-26 00:04:34'),(1262,329,'Navy','Standar',22,15000.00,'2026-08-26 00:04:34'),(1263,329,'Pink','Standar',7,-7000.00,'2026-08-26 00:04:34'),(1264,329,'Putih','Standar',14,-2000.00,'2026-08-26 00:04:34'),(1265,328,'Abu-abu','Standar',3,-13000.00,'2026-08-26 00:05:12'),(1266,328,'Biru Muda','Standar',10,1000.00,'2026-08-26 00:05:12'),(1267,328,'Cokelat','Standar',17,7000.00,'2026-08-26 00:05:12'),(1268,328,'Cream','Standar',24,18000.00,'2026-08-26 00:05:12'),(1269,328,'Hijau Army','Standar',9,1000.00,'2026-08-26 00:05:12'),(1270,328,'Hitam','Standar',16,-25000.00,'2026-08-26 00:05:12'),(1271,328,'Kuning','Standar',23,-23000.00,'2026-08-26 00:05:12'),(1272,328,'Maroon','Standar',8,5000.00,'2026-08-26 00:05:12'),(1273,328,'Navy','Standar',15,-5000.00,'2026-08-26 00:05:12'),(1274,328,'Pink','Standar',22,-11000.00,'2026-08-26 00:05:12'),(1275,328,'Putih','Standar',7,-9000.00,'2026-08-26 00:05:12'),(1276,327,'Abu-abu','S',2,-13000.00,'2026-08-26 00:05:55'),(1277,327,'Abu-abu','M',6,-13000.00,'2026-08-26 00:05:55'),(1278,327,'Abu-abu','L',13,-13000.00,'2026-08-26 00:05:55'),(1279,327,'Abu-abu','XL',18,-13000.00,'2026-08-26 00:05:55'),(1280,327,'Abu-abu','XXL',23,-13000.00,'2026-08-26 00:05:55'),(1281,327,'Biru Muda','S',10,0.00,'2026-08-26 00:05:55'),(1282,327,'Biru Muda','M',15,0.00,'2026-08-26 00:05:55'),(1283,327,'Biru Muda','L',20,0.00,'2026-08-26 00:05:55'),(1284,327,'Biru Muda','XL',3,0.00,'2026-08-26 00:05:55'),(1285,327,'Biru Muda','XXL',8,0.00,'2026-08-26 00:05:55'),(1286,327,'Cokelat','S',17,13000.00,'2026-08-26 00:05:55'),(1287,327,'Cokelat','M',22,13000.00,'2026-08-26 00:05:55'),(1288,327,'Cokelat','L',5,13000.00,'2026-08-26 00:05:55'),(1289,327,'Cokelat','XL',10,13000.00,'2026-08-26 00:05:55'),(1290,327,'Cokelat','XXL',15,13000.00,'2026-08-26 00:05:55'),(1291,327,'Cream','S',24,-1000.00,'2026-08-26 00:05:55'),(1292,327,'Cream','M',7,-1000.00,'2026-08-26 00:05:55'),(1293,327,'Cream','L',12,-1000.00,'2026-08-26 00:05:55'),(1294,327,'Cream','XL',17,-1000.00,'2026-08-26 00:05:55'),(1295,327,'Cream','XXL',22,-1000.00,'2026-08-26 00:05:55'),(1296,327,'Hijau Army','S',9,-6000.00,'2026-08-26 00:05:55'),(1297,327,'Hijau Army','M',14,-6000.00,'2026-08-26 00:05:55'),(1298,327,'Hijau Army','L',19,-6000.00,'2026-08-26 00:05:55'),(1299,327,'Hijau Army','XL',24,-6000.00,'2026-08-26 00:05:55'),(1300,327,'Hijau Army','XXL',7,-6000.00,'2026-08-26 00:05:55'),(1301,327,'Hitam','S',16,12000.00,'2026-08-26 00:05:55'),(1302,327,'Hitam','M',21,12000.00,'2026-08-26 00:05:55'),(1303,327,'Hitam','L',4,12000.00,'2026-08-26 00:05:55'),(1304,327,'Hitam','XL',9,12000.00,'2026-08-26 00:05:55'),(1305,327,'Hitam','XXL',14,12000.00,'2026-08-26 00:05:55'),(1306,327,'Maroon','S',23,5000.00,'2026-08-26 00:05:55'),(1307,327,'Maroon','M',6,5000.00,'2026-08-26 00:05:55'),(1308,327,'Maroon','L',11,5000.00,'2026-08-26 00:05:55'),(1309,327,'Maroon','XL',16,5000.00,'2026-08-26 00:05:55'),(1310,327,'Maroon','XXL',21,5000.00,'2026-08-26 00:05:55'),(1311,327,'Merah','S',8,13000.00,'2026-08-26 00:05:55'),(1312,327,'Merah','M',13,13000.00,'2026-08-26 00:05:55'),(1313,327,'Merah','L',18,13000.00,'2026-08-26 00:05:55'),(1314,327,'Merah','XL',23,13000.00,'2026-08-26 00:05:55'),(1315,327,'Merah','XXL',6,13000.00,'2026-08-26 00:05:55'),(1316,327,'Navy','S',15,-5000.00,'2026-08-26 00:05:55'),(1317,327,'Navy','M',20,-5000.00,'2026-08-26 00:05:55'),(1318,327,'Navy','L',3,-5000.00,'2026-08-26 00:05:55'),(1319,327,'Navy','XL',8,-5000.00,'2026-08-26 00:05:55'),(1320,327,'Navy','XXL',13,-5000.00,'2026-08-26 00:05:55'),(1321,327,'Pink','S',22,-7000.00,'2026-08-26 00:05:55'),(1322,327,'Pink','M',5,-7000.00,'2026-08-26 00:05:55'),(1323,327,'Pink','L',10,-7000.00,'2026-08-26 00:05:55'),(1324,327,'Pink','XL',15,-7000.00,'2026-08-26 00:05:55'),(1325,327,'Pink','XXL',20,-7000.00,'2026-08-26 00:05:55'),(1326,327,'Putih','S',7,5000.00,'2026-08-26 00:05:55'),(1327,327,'Putih','M',12,5000.00,'2026-08-26 00:05:55'),(1328,327,'Putih','L',17,5000.00,'2026-08-26 00:05:55'),(1329,327,'Putih','XL',22,5000.00,'2026-08-26 00:05:55'),(1330,327,'Putih','XXL',5,5000.00,'2026-08-26 00:05:55'),(1331,326,'Abu-abu','S',3,-10000.00,'2026-08-26 00:07:07'),(1332,326,'Abu-abu','M',8,-10000.00,'2026-08-26 00:07:07'),(1333,326,'Abu-abu','L',13,-10000.00,'2026-08-26 00:07:07'),(1334,326,'Abu-abu','XL',18,-10000.00,'2026-08-26 00:07:07'),(1335,326,'Abu-abu','XXL',23,-10000.00,'2026-08-26 00:07:07'),(1336,326,'Biru Muda','S',10,-5000.00,'2026-08-26 00:07:07'),(1337,326,'Biru Muda','M',15,-5000.00,'2026-08-26 00:07:07'),(1338,326,'Biru Muda','L',20,-5000.00,'2026-08-26 00:07:07'),(1339,326,'Biru Muda','XL',3,-5000.00,'2026-08-26 00:07:07'),(1340,326,'Biru Muda','XXL',8,-5000.00,'2026-08-26 00:07:07'),(1341,326,'Cokelat','S',17,-8000.00,'2026-08-26 00:07:07'),(1342,326,'Cokelat','M',22,-8000.00,'2026-08-26 00:07:07'),(1343,326,'Cokelat','L',5,-8000.00,'2026-08-26 00:07:07'),(1344,326,'Cokelat','XL',10,-8000.00,'2026-08-26 00:07:07'),(1345,326,'Cokelat','XXL',15,-8000.00,'2026-08-26 00:07:07'),(1346,326,'Cream','S',24,-3000.00,'2026-08-26 00:07:07'),(1347,326,'Cream','M',7,-3000.00,'2026-08-26 00:07:07'),(1348,326,'Cream','L',12,-3000.00,'2026-08-26 00:07:07'),(1349,326,'Cream','XL',17,-3000.00,'2026-08-26 00:07:07'),(1350,326,'Cream','XXL',22,-3000.00,'2026-08-26 00:07:07'),(1351,326,'Hijau Army','S',9,3000.00,'2026-08-26 00:07:07'),(1352,326,'Hijau Army','M',14,3000.00,'2026-08-26 00:07:07'),(1353,326,'Hijau Army','L',19,3000.00,'2026-08-26 00:07:07'),(1354,326,'Hijau Army','XL',24,3000.00,'2026-08-26 00:07:07'),(1355,326,'Hijau Army','XXL',7,3000.00,'2026-08-26 00:07:07'),(1356,326,'Hitam','S',16,7000.00,'2026-08-26 00:07:07'),(1357,326,'Hitam','M',21,7000.00,'2026-08-26 00:07:07'),(1358,326,'Hitam','L',4,7000.00,'2026-08-26 00:07:07'),(1359,326,'Hitam','XL',9,7000.00,'2026-08-26 00:07:07'),(1360,326,'Hitam','XXL',14,7000.00,'2026-08-26 00:07:07'),(1361,326,'Kuning','S',23,-10000.00,'2026-08-26 00:07:07'),(1362,326,'Kuning','M',6,-10000.00,'2026-08-26 00:07:07'),(1363,326,'Kuning','L',11,-10000.00,'2026-08-26 00:07:07'),(1364,326,'Kuning','XL',16,-10000.00,'2026-08-26 00:07:07'),(1365,326,'Kuning','XXL',21,-10000.00,'2026-08-26 00:07:07'),(1366,326,'Maroon','S',8,-2000.00,'2026-08-26 00:07:07'),(1367,326,'Maroon','M',13,-2000.00,'2026-08-26 00:07:07'),(1368,326,'Maroon','L',18,-2000.00,'2026-08-26 00:07:07'),(1369,326,'Maroon','XL',23,-2000.00,'2026-08-26 00:07:07'),(1370,326,'Maroon','XXL',6,-2000.00,'2026-08-26 00:07:07'),(1371,326,'Merah','S',15,4000.00,'2026-08-26 00:07:07'),(1372,326,'Merah','M',20,4000.00,'2026-08-26 00:07:07'),(1373,326,'Merah','L',3,4000.00,'2026-08-26 00:07:07'),(1374,326,'Merah','XL',8,4000.00,'2026-08-26 00:07:07'),(1375,326,'Merah','XXL',13,4000.00,'2026-08-26 00:07:07'),(1376,326,'Navy','S',22,13000.00,'2026-08-26 00:07:07'),(1377,326,'Navy','M',5,13000.00,'2026-08-26 00:07:07'),(1378,326,'Navy','L',10,13000.00,'2026-08-26 00:07:07'),(1379,326,'Navy','XL',15,13000.00,'2026-08-26 00:07:07'),(1380,326,'Navy','XXL',20,13000.00,'2026-08-26 00:07:07'),(1381,326,'Pink','S',7,5000.00,'2026-08-26 00:07:07'),(1382,326,'Pink','M',12,5000.00,'2026-08-26 00:07:07'),(1383,326,'Pink','L',17,5000.00,'2026-08-26 00:07:07'),(1384,326,'Pink','XL',22,5000.00,'2026-08-26 00:07:07'),(1385,326,'Pink','XXL',5,5000.00,'2026-08-26 00:07:07'),(1386,326,'Putih','S',14,-10000.00,'2026-08-26 00:07:07'),(1387,326,'Putih','M',19,-10000.00,'2026-08-26 00:07:07'),(1388,326,'Putih','L',24,-10000.00,'2026-08-26 00:07:07'),(1389,326,'Putih','XL',7,-10000.00,'2026-08-26 00:07:07'),(1390,326,'Putih','XXL',12,-10000.00,'2026-08-26 00:07:07'),(1391,325,'Abu-abu','S',3,12000.00,'2026-08-26 00:08:01'),(1392,325,'Abu-abu','M',8,12000.00,'2026-08-26 00:08:01'),(1393,325,'Abu-abu','L',13,12000.00,'2026-08-26 00:08:01'),(1394,325,'Abu-abu','XL',18,12000.00,'2026-08-26 00:08:01'),(1395,325,'Abu-abu','XXL',23,12000.00,'2026-08-26 00:08:01'),(1396,325,'Biru Muda','S',10,-13000.00,'2026-08-26 00:08:01'),(1397,325,'Biru Muda','M',15,-13000.00,'2026-08-26 00:08:01'),(1398,325,'Biru Muda','L',20,-13000.00,'2026-08-26 00:08:01'),(1399,325,'Biru Muda','XL',3,-13000.00,'2026-08-26 00:08:01'),(1400,325,'Biru Muda','XXL',8,-13000.00,'2026-08-26 00:08:01'),(1401,325,'Cream','S',17,-5000.00,'2026-08-26 00:08:01'),(1402,325,'Cream','M',22,-5000.00,'2026-08-26 00:08:01'),(1403,325,'Cream','L',5,-5000.00,'2026-08-26 00:08:01'),(1404,325,'Cream','XL',10,-5000.00,'2026-08-26 00:08:01'),(1405,325,'Cream','XXL',15,-5000.00,'2026-08-26 00:08:01'),(1406,325,'Hijau Army','S',24,14000.00,'2026-08-26 00:08:01'),(1407,325,'Hijau Army','M',7,14000.00,'2026-08-26 00:08:01'),(1408,325,'Hijau Army','L',12,14000.00,'2026-08-26 00:08:01'),(1409,325,'Hijau Army','XL',17,14000.00,'2026-08-26 00:08:01'),(1410,325,'Hijau Army','XXL',22,14000.00,'2026-08-26 00:08:01'),(1411,325,'Hitam','S',9,-20000.00,'2026-08-26 00:08:01'),(1412,325,'Hitam','M',14,-20000.00,'2026-08-26 00:08:01'),(1413,325,'Hitam','L',19,-20000.00,'2026-08-26 00:08:01'),(1414,325,'Hitam','XL',24,-20000.00,'2026-08-26 00:08:01'),(1415,325,'Hitam','XXL',7,-20000.00,'2026-08-26 00:08:01'),(1416,325,'Kuning','S',16,10000.00,'2026-08-26 00:08:01'),(1417,325,'Kuning','M',21,10000.00,'2026-08-26 00:08:01'),(1418,325,'Kuning','L',4,10000.00,'2026-08-26 00:08:01'),(1419,325,'Kuning','XL',9,10000.00,'2026-08-26 00:08:01'),(1420,325,'Kuning','XXL',14,10000.00,'2026-08-26 00:08:01'),(1421,325,'Maroon','S',23,-13000.00,'2026-08-26 00:08:01'),(1422,325,'Maroon','M',6,-13000.00,'2026-08-26 00:08:01'),(1423,325,'Maroon','L',11,-13000.00,'2026-08-26 00:08:01'),(1424,325,'Maroon','XL',16,-13000.00,'2026-08-26 00:08:01'),(1425,325,'Maroon','XXL',21,-13000.00,'2026-08-26 00:08:01'),(1426,325,'Merah','S',8,1000.00,'2026-08-26 00:08:01'),(1427,325,'Merah','M',13,1000.00,'2026-08-26 00:08:01'),(1428,325,'Merah','L',18,1000.00,'2026-08-26 00:08:01'),(1429,325,'Merah','XL',23,1000.00,'2026-08-26 00:08:01'),(1430,325,'Merah','XXL',6,1000.00,'2026-08-26 00:08:01'),(1431,325,'Pink','S',15,3000.00,'2026-08-26 00:08:01'),(1432,325,'Pink','M',20,3000.00,'2026-08-26 00:08:01'),(1433,325,'Pink','L',3,3000.00,'2026-08-26 00:08:01'),(1434,325,'Pink','XL',8,3000.00,'2026-08-26 00:08:01'),(1435,325,'Pink','XXL',13,3000.00,'2026-08-26 00:08:01'),(1436,325,'Putih','S',22,-10000.00,'2026-08-26 00:08:01'),(1437,325,'Putih','M',5,-10000.00,'2026-08-26 00:08:01'),(1438,325,'Putih','L',10,-10000.00,'2026-08-26 00:08:01'),(1439,325,'Putih','XL',15,-10000.00,'2026-08-26 00:08:01'),(1440,325,'Putih','XXL',20,-10000.00,'2026-08-26 00:08:01'),(1441,324,'Abu-abu','S',3,4000.00,'2026-08-26 00:08:40'),(1442,324,'Abu-abu','M',8,4000.00,'2026-08-26 00:08:40'),(1443,324,'Abu-abu','L',13,4000.00,'2026-08-26 00:08:40'),(1444,324,'Abu-abu','XL',18,4000.00,'2026-08-26 00:08:40'),(1445,324,'Abu-abu','XXL',23,4000.00,'2026-08-26 00:08:40'),(1446,324,'Biru Muda','S',10,3000.00,'2026-08-26 00:08:40'),(1447,324,'Biru Muda','M',15,3000.00,'2026-08-26 00:08:40'),(1448,324,'Biru Muda','L',20,3000.00,'2026-08-26 00:08:40'),(1449,324,'Biru Muda','XL',3,3000.00,'2026-08-26 00:08:40'),(1450,324,'Biru Muda','XXL',8,3000.00,'2026-08-26 00:08:40'),(1451,324,'Cokelat','S',17,12000.00,'2026-08-26 00:08:40'),(1452,324,'Cokelat','M',22,12000.00,'2026-08-26 00:08:40'),(1453,324,'Cokelat','L',5,12000.00,'2026-08-26 00:08:40'),(1454,324,'Cokelat','XL',10,12000.00,'2026-08-26 00:08:40'),(1455,324,'Cokelat','XXL',15,12000.00,'2026-08-26 00:08:40'),(1456,324,'Cream','S',24,15000.00,'2026-08-26 00:08:40'),(1457,324,'Cream','M',7,15000.00,'2026-08-26 00:08:40'),(1458,324,'Cream','L',12,15000.00,'2026-08-26 00:08:40'),(1459,324,'Cream','XL',17,15000.00,'2026-08-26 00:08:40'),(1460,324,'Cream','XXL',22,15000.00,'2026-08-26 00:08:40'),(1461,324,'Hijau Army','S',9,2000.00,'2026-08-26 00:08:40'),(1462,324,'Hijau Army','M',14,2000.00,'2026-08-26 00:08:40'),(1463,324,'Hijau Army','L',19,2000.00,'2026-08-26 00:08:40'),(1464,324,'Hijau Army','XL',24,2000.00,'2026-08-26 00:08:40'),(1465,324,'Hijau Army','XXL',7,2000.00,'2026-08-26 00:08:40'),(1466,324,'Hitam','S',16,-8000.00,'2026-08-26 00:08:40'),(1467,324,'Hitam','M',21,-8000.00,'2026-08-26 00:08:40'),(1468,324,'Hitam','L',4,-8000.00,'2026-08-26 00:08:40'),(1469,324,'Hitam','XL',9,-8000.00,'2026-08-26 00:08:40'),(1470,324,'Hitam','XXL',14,-8000.00,'2026-08-26 00:08:40'),(1471,324,'Kuning','S',23,-1000.00,'2026-08-26 00:08:40'),(1472,324,'Kuning','M',6,-1000.00,'2026-08-26 00:08:40'),(1473,324,'Kuning','L',11,-1000.00,'2026-08-26 00:08:40'),(1474,324,'Kuning','XL',16,-1000.00,'2026-08-26 00:08:40'),(1475,324,'Kuning','XXL',21,-1000.00,'2026-08-26 00:08:40'),(1476,324,'Maroon','S',8,13000.00,'2026-08-26 00:08:40'),(1477,324,'Maroon','M',13,13000.00,'2026-08-26 00:08:40'),(1478,324,'Maroon','L',18,13000.00,'2026-08-26 00:08:40'),(1479,324,'Maroon','XL',23,13000.00,'2026-08-26 00:08:40'),(1480,324,'Maroon','XXL',6,13000.00,'2026-08-26 00:08:40'),(1481,324,'Merah','S',15,-12000.00,'2026-08-26 00:08:40'),(1482,324,'Merah','M',20,-12000.00,'2026-08-26 00:08:40'),(1483,324,'Merah','L',3,-12000.00,'2026-08-26 00:08:40'),(1484,324,'Merah','XL',8,-12000.00,'2026-08-26 00:08:40'),(1485,324,'Merah','XXL',13,-12000.00,'2026-08-26 00:08:40'),(1486,324,'Pink','S',22,-3000.00,'2026-08-26 00:08:40'),(1487,324,'Pink','M',5,-3000.00,'2026-08-26 00:08:40'),(1488,324,'Pink','L',10,-3000.00,'2026-08-26 00:08:40'),(1489,324,'Pink','XL',15,-3000.00,'2026-08-26 00:08:40'),(1490,324,'Pink','XXL',20,-3000.00,'2026-08-26 00:08:40'),(1491,324,'Putih','S',7,5000.00,'2026-08-26 00:08:40'),(1492,324,'Putih','M',12,5000.00,'2026-08-26 00:08:40'),(1493,324,'Putih','L',17,5000.00,'2026-08-26 00:08:40'),(1494,324,'Putih','XL',22,5000.00,'2026-08-26 00:08:40'),(1495,324,'Putih','XXL',5,5000.00,'2026-08-26 00:08:40'),(1496,323,'Abu-abu','S',3,-15000.00,'2026-08-26 00:09:08'),(1497,323,'Abu-abu','M',7,-15000.00,'2026-08-26 00:09:08'),(1498,323,'Abu-abu','L',13,-15000.00,'2026-08-26 00:09:08'),(1499,323,'Abu-abu','XL',18,-15000.00,'2026-08-26 00:09:08'),(1500,323,'Abu-abu','XXL',23,-15000.00,'2026-08-26 00:09:08'),(1501,323,'Biru Muda','S',10,-20000.00,'2026-08-26 00:09:08'),(1502,323,'Biru Muda','M',15,-20000.00,'2026-08-26 00:09:08'),(1503,323,'Biru Muda','L',20,-20000.00,'2026-08-26 00:09:08'),(1504,323,'Biru Muda','XL',3,-20000.00,'2026-08-26 00:09:08'),(1505,323,'Biru Muda','XXL',8,-20000.00,'2026-08-26 00:09:08'),(1506,323,'Cokelat','S',17,-5000.00,'2026-08-26 00:09:08'),(1507,323,'Cokelat','M',22,-5000.00,'2026-08-26 00:09:08'),(1508,323,'Cokelat','L',5,-5000.00,'2026-08-26 00:09:08'),(1509,323,'Cokelat','XL',10,-5000.00,'2026-08-26 00:09:08'),(1510,323,'Cokelat','XXL',15,-5000.00,'2026-08-26 00:09:08'),(1511,323,'Cream','S',24,3000.00,'2026-08-26 00:09:08'),(1512,323,'Cream','M',7,3000.00,'2026-08-26 00:09:08'),(1513,323,'Cream','L',12,3000.00,'2026-08-26 00:09:08'),(1514,323,'Cream','XL',17,3000.00,'2026-08-26 00:09:08'),(1515,323,'Cream','XXL',22,3000.00,'2026-08-26 00:09:08'),(1516,323,'Hijau Army','S',9,-3000.00,'2026-08-26 00:09:08'),(1517,323,'Hijau Army','M',13,-3000.00,'2026-08-26 00:09:08'),(1518,323,'Hijau Army','L',19,-3000.00,'2026-08-26 00:09:08'),(1519,323,'Hijau Army','XL',24,-3000.00,'2026-08-26 00:09:08'),(1520,323,'Hijau Army','XXL',7,-3000.00,'2026-08-26 00:09:08'),(1521,323,'Hitam','S',16,-10000.00,'2026-08-26 00:09:08'),(1522,323,'Hitam','M',21,-10000.00,'2026-08-26 00:09:08'),(1523,323,'Hitam','L',4,-10000.00,'2026-08-26 00:09:08'),(1524,323,'Hitam','XL',9,-10000.00,'2026-08-26 00:09:08'),(1525,323,'Hitam','XXL',14,-10000.00,'2026-08-26 00:09:08'),(1526,323,'Kuning','S',23,1000.00,'2026-08-26 00:09:08'),(1527,323,'Kuning','M',6,1000.00,'2026-08-26 00:09:08'),(1528,323,'Kuning','L',11,1000.00,'2026-08-26 00:09:08'),(1529,323,'Kuning','XL',16,1000.00,'2026-08-26 00:09:08'),(1530,323,'Kuning','XXL',21,1000.00,'2026-08-26 00:09:08'),(1531,323,'Maroon','S',8,14000.00,'2026-08-26 00:09:08'),(1532,323,'Maroon','M',13,14000.00,'2026-08-26 00:09:08'),(1533,323,'Maroon','L',18,14000.00,'2026-08-26 00:09:08'),(1534,323,'Maroon','XL',23,14000.00,'2026-08-26 00:09:08'),(1535,323,'Maroon','XXL',6,14000.00,'2026-08-26 00:09:08'),(1536,323,'Merah','S',15,10000.00,'2026-08-26 00:09:08'),(1537,323,'Merah','M',20,10000.00,'2026-08-26 00:09:08'),(1538,323,'Merah','L',3,10000.00,'2026-08-26 00:09:08'),(1539,323,'Merah','XL',8,10000.00,'2026-08-26 00:09:08'),(1540,323,'Merah','XXL',13,10000.00,'2026-08-26 00:09:08'),(1541,323,'Pink','S',22,11000.00,'2026-08-26 00:09:08'),(1542,323,'Pink','M',5,11000.00,'2026-08-26 00:09:08'),(1543,323,'Pink','L',10,11000.00,'2026-08-26 00:09:08'),(1544,323,'Pink','XL',15,11000.00,'2026-08-26 00:09:08'),(1545,323,'Pink','XXL',20,11000.00,'2026-08-26 00:09:08'),(1546,323,'Putih','S',7,8000.00,'2026-08-26 00:09:08'),(1547,323,'Putih','M',12,8000.00,'2026-08-26 00:09:08'),(1548,323,'Putih','L',17,8000.00,'2026-08-26 00:09:08'),(1549,323,'Putih','XL',22,8000.00,'2026-08-26 00:09:08'),(1550,323,'Putih','XXL',5,8000.00,'2026-08-26 00:09:08'),(1551,322,'Biru Muda','S',3,12000.00,'2026-08-26 00:09:33'),(1552,322,'Biru Muda','M',8,12000.00,'2026-08-26 00:09:33'),(1553,322,'Biru Muda','L',13,12000.00,'2026-08-26 00:09:33'),(1554,322,'Biru Muda','XL',18,12000.00,'2026-08-26 00:09:33'),(1555,322,'Biru Muda','XXL',23,12000.00,'2026-08-26 00:09:33'),(1556,322,'Cokelat','S',10,-8000.00,'2026-08-26 00:09:33'),(1557,322,'Cokelat','M',15,-8000.00,'2026-08-26 00:09:33'),(1558,322,'Cokelat','L',20,-8000.00,'2026-08-26 00:09:33'),(1559,322,'Cokelat','XL',3,-8000.00,'2026-08-26 00:09:33'),(1560,322,'Cokelat','XXL',8,-8000.00,'2026-08-26 00:09:33'),(1561,322,'Cream','S',17,8000.00,'2026-08-26 00:09:33'),(1562,322,'Cream','M',22,8000.00,'2026-08-26 00:09:33'),(1563,322,'Cream','L',5,8000.00,'2026-08-26 00:09:33'),(1564,322,'Cream','XL',10,8000.00,'2026-08-26 00:09:33'),(1565,322,'Cream','XXL',15,8000.00,'2026-08-26 00:09:33'),(1566,322,'Hijau Army','S',24,-14000.00,'2026-08-26 00:09:33'),(1567,322,'Hijau Army','M',7,-14000.00,'2026-08-26 00:09:33'),(1568,322,'Hijau Army','L',12,-14000.00,'2026-08-26 00:09:33'),(1569,322,'Hijau Army','XL',17,-14000.00,'2026-08-26 00:09:33'),(1570,322,'Hijau Army','XXL',22,-14000.00,'2026-08-26 00:09:33'),(1571,322,'Hitam','S',9,20000.00,'2026-08-26 00:09:33'),(1572,322,'Hitam','M',14,20000.00,'2026-08-26 00:09:33'),(1573,322,'Hitam','L',19,20000.00,'2026-08-26 00:09:33'),(1574,322,'Hitam','XL',24,20000.00,'2026-08-26 00:09:33'),(1575,322,'Hitam','XXL',7,20000.00,'2026-08-26 00:09:33'),(1576,322,'Kuning','S',16,0.00,'2026-08-26 00:09:33'),(1577,322,'Kuning','M',21,0.00,'2026-08-26 00:09:33'),(1578,322,'Kuning','L',4,0.00,'2026-08-26 00:09:33'),(1579,322,'Kuning','XL',9,0.00,'2026-08-26 00:09:33'),(1580,322,'Kuning','XXL',14,0.00,'2026-08-26 00:09:33'),(1581,322,'Maroon','S',23,0.00,'2026-08-26 00:09:33'),(1582,322,'Maroon','M',6,0.00,'2026-08-26 00:09:33'),(1583,322,'Maroon','L',11,0.00,'2026-08-26 00:09:33'),(1584,322,'Maroon','XL',16,0.00,'2026-08-26 00:09:33'),(1585,322,'Maroon','XXL',21,0.00,'2026-08-26 00:09:33'),(1586,322,'Merah','S',8,-30000.00,'2026-08-26 00:09:33'),(1587,322,'Merah','M',13,-30000.00,'2026-08-26 00:09:33'),(1588,322,'Merah','L',18,-30000.00,'2026-08-26 00:09:33'),(1589,322,'Merah','XL',23,-30000.00,'2026-08-26 00:09:33'),(1590,322,'Merah','XXL',6,-30000.00,'2026-08-26 00:09:33'),(1591,322,'Navy','S',15,-3000.00,'2026-08-26 00:09:33'),(1592,322,'Navy','M',20,-3000.00,'2026-08-26 00:09:33'),(1593,322,'Navy','L',3,-3000.00,'2026-08-26 00:09:33'),(1594,322,'Navy','XL',8,-3000.00,'2026-08-26 00:09:33'),(1595,322,'Navy','XXL',13,-3000.00,'2026-08-26 00:09:33'),(1596,322,'Pink','S',22,-13000.00,'2026-08-26 00:09:33'),(1597,322,'Pink','M',5,-13000.00,'2026-08-26 00:09:33'),(1598,322,'Pink','L',10,-13000.00,'2026-08-26 00:09:33'),(1599,322,'Pink','XL',15,-13000.00,'2026-08-26 00:09:33'),(1600,322,'Pink','XXL',20,-13000.00,'2026-08-26 00:09:33'),(1601,322,'Putih','S',7,13000.00,'2026-08-26 00:09:33'),(1602,322,'Putih','M',12,13000.00,'2026-08-26 00:09:33'),(1603,322,'Putih','L',17,13000.00,'2026-08-26 00:09:33'),(1604,322,'Putih','XL',22,13000.00,'2026-08-26 00:09:33'),(1605,322,'Putih','XXL',5,13000.00,'2026-08-26 00:09:33');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_views`
--

DROP TABLE IF EXISTS `product_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_views` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `view_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_views`
--

LOCK TABLES `product_views` WRITE;
/*!40000 ALTER TABLE `product_views` DISABLE KEYS */;
INSERT INTO `product_views` VALUES (1,119,323,NULL,'2026-08-23 12:35:03','2026-08-23 12:35:03','p8dhig5t5155079oepbnv38o7e58h3ga'),(2,119,323,NULL,'2026-08-23 12:58:25','2026-08-23 12:58:25','nouct2kv7krrasuh3p9lkd0clq4ohtv5'),(3,119,330,NULL,'2026-08-23 13:04:22','2026-08-23 13:04:22','8vfo99f0k1pu1lbim25mjtoc7rq9ifkr'),(4,119,330,NULL,'2026-08-23 13:04:30','2026-08-23 13:04:30','8vfo99f0k1pu1lbim25mjtoc7rq9ifkr'),(5,119,330,NULL,'2026-08-23 13:04:58','2026-08-23 13:04:58','8vfo99f0k1pu1lbim25mjtoc7rq9ifkr'),(6,119,330,NULL,'2026-08-23 13:54:35','2026-08-23 13:54:35','g1p1ie8u64fm6v2ilksvpaeegkth2l1j'),(7,0,323,NULL,'2026-08-23 14:29:54','2026-08-23 14:29:54','sn3upb3bdr9tqtenjpf2k95bitqta40q'),(8,0,323,NULL,'2026-08-23 14:30:00','2026-08-23 14:30:00','sn3upb3bdr9tqtenjpf2k95bitqta40q'),(9,119,330,NULL,'2026-08-23 14:32:06','2026-08-23 14:32:06','sn3upb3bdr9tqtenjpf2k95bitqta40q'),(10,0,330,NULL,'2026-08-23 16:40:17','2026-08-23 16:40:17','4e028t06mltfno6ck0shqc18rprk9kov'),(11,0,326,NULL,'2026-08-23 16:40:34','2026-08-23 16:40:34','bgmpg5s2ac664ke2i11al3l50hkm2r2i'),(12,119,330,NULL,'2026-08-23 16:48:24','2026-08-23 16:48:24','l97h1k66ogtomg0rrij329h1gb939mrb'),(13,119,330,NULL,'2026-08-23 16:51:28','2026-08-23 16:51:28','l97h1k66ogtomg0rrij329h1gb939mrb'),(14,119,323,NULL,'2026-08-23 16:52:32','2026-08-23 16:52:32','1hd5p7e60a5ngpq8ubgtgcif932suqeg'),(15,119,330,NULL,'2026-08-23 16:54:38','2026-08-23 16:54:38','novsd0d5eivh4n720m5k7svp5p82iaih'),(16,119,330,NULL,'2026-08-23 16:55:45','2026-08-23 16:55:45','novsd0d5eivh4n720m5k7svp5p82iaih'),(17,119,330,NULL,'2026-08-23 17:09:51','2026-08-23 17:09:51','7cs8bacieigjelsndthuj2uhhp7bt78s'),(18,119,330,NULL,'2026-08-23 17:13:57','2026-08-23 17:13:57','7cs8bacieigjelsndthuj2uhhp7bt78s'),(19,119,329,NULL,'2026-08-25 20:01:35','2026-08-25 20:01:35','j4p0f3ck8vnpvmbpkj8tmbneie942scd'),(20,119,330,NULL,'2026-08-25 20:02:06','2026-08-25 20:02:06','j4p0f3ck8vnpvmbpkj8tmbneie942scd'),(21,119,330,NULL,'2026-08-25 20:04:05','2026-08-25 20:04:05','j4p0f3ck8vnpvmbpkj8tmbneie942scd'),(84,200,322,30,'2026-08-05 00:00:00','2026-08-05 10:00:00','s200-1'),(85,200,322,30,'2026-08-06 00:00:00','2026-08-06 10:00:00','s200-2'),(86,200,322,30,'2026-08-07 00:00:00','2026-08-07 10:00:00','s200-3'),(87,200,324,30,'2026-08-08 00:00:00','2026-08-08 10:00:00','s200-4'),(88,200,324,30,'2026-08-09 00:00:00','2026-08-09 10:00:00','s200-5'),(89,200,329,30,'2026-08-10 00:00:00','2026-08-10 10:00:00','s200-6'),(90,200,326,30,'2026-08-11 00:00:00','2026-08-11 10:00:00','s200-7'),(91,200,328,30,'2026-08-12 00:00:00','2026-08-12 10:00:00','s200-8'),(92,201,322,30,'2026-08-06 00:00:00','2026-08-06 10:00:00','s201-1'),(93,201,324,30,'2026-08-09 00:00:00','2026-08-09 10:00:00','s201-2'),(94,201,324,30,'2026-08-10 00:00:00','2026-08-10 10:00:00','s201-3'),(95,201,329,30,'2026-08-12 00:00:00','2026-08-12 10:00:00','s201-4'),(96,201,329,30,'2026-08-13 00:00:00','2026-08-13 10:00:00','s201-5'),(97,201,326,30,'2026-08-15 00:00:00','2026-08-15 10:00:00','s201-6'),(98,201,323,30,'2026-08-16 00:00:00','2026-08-16 10:00:00','s201-7'),(99,202,323,30,'2026-08-04 00:00:00','2026-08-04 10:00:00','s202-1'),(100,202,323,30,'2026-08-05 00:00:00','2026-08-05 10:00:00','s202-2'),(101,202,328,30,'2026-08-11 00:00:00','2026-08-11 10:00:00','s202-3'),(102,202,328,30,'2026-08-12 00:00:00','2026-08-12 10:00:00','s202-4'),(103,202,329,30,'2026-08-15 00:00:00','2026-08-15 10:00:00','s202-5'),(104,202,322,30,'2026-08-17 00:00:00','2026-08-17 10:00:00','s202-6'),(105,203,322,30,'2026-08-08 00:00:00','2026-08-08 10:00:00','s203-1'),(106,203,326,30,'2026-08-10 00:00:00','2026-08-10 10:00:00','s203-2'),(107,203,328,30,'2026-08-15 00:00:00','2026-08-15 10:00:00','s203-3'),(108,203,328,30,'2026-08-16 00:00:00','2026-08-16 10:00:00','s203-4'),(109,203,329,30,'2026-08-18 00:00:00','2026-08-18 10:00:00','s203-5'),(110,203,324,30,'2026-08-20 00:00:00','2026-08-20 10:00:00','s203-6'),(111,204,325,30,'2026-08-07 00:00:00','2026-08-07 10:00:00','s204-1'),(112,204,325,30,'2026-08-08 00:00:00','2026-08-08 10:00:00','s204-2'),(113,204,330,30,'2026-08-10 00:00:00','2026-08-10 10:00:00','s204-3'),(114,204,327,30,'2026-08-15 00:00:00','2026-08-15 10:00:00','s204-4'),(115,204,327,30,'2026-08-16 00:00:00','2026-08-16 10:00:00','s204-5'),(116,204,330,30,'2026-08-18 00:00:00','2026-08-18 10:00:00','s204-6'),(117,205,325,30,'2026-08-05 00:00:00','2026-08-05 10:00:00','s205-1'),(118,205,325,30,'2026-08-06 00:00:00','2026-08-06 10:00:00','s205-2'),(119,205,330,30,'2026-08-11 00:00:00','2026-08-11 10:00:00','s205-3'),(120,205,330,30,'2026-08-12 00:00:00','2026-08-12 10:00:00','s205-4'),(121,205,327,30,'2026-08-15 00:00:00','2026-08-15 10:00:00','s205-5'),(122,205,327,30,'2026-08-17 00:00:00','2026-08-17 10:00:00','s205-6'),(123,206,325,30,'2026-08-09 00:00:00','2026-08-09 10:00:00','s206-1'),(124,206,325,30,'2026-08-10 00:00:00','2026-08-10 10:00:00','s206-2'),(125,206,330,30,'2026-08-13 00:00:00','2026-08-13 10:00:00','s206-3'),(126,206,327,30,'2026-08-19 00:00:00','2026-08-19 10:00:00','s206-4'),(127,206,327,30,'2026-08-20 00:00:00','2026-08-20 10:00:00','s206-5'),(128,207,325,30,'2026-08-08 00:00:00','2026-08-08 10:00:00','s207-1'),(129,207,325,30,'2026-08-09 00:00:00','2026-08-09 10:00:00','s207-2'),(130,207,330,30,'2026-08-14 00:00:00','2026-08-14 10:00:00','s207-3'),(131,207,327,30,'2026-08-18 00:00:00','2026-08-18 10:00:00','s207-4'),(132,207,327,30,'2026-08-19 00:00:00','2026-08-19 10:00:00','s207-5'),(133,208,322,30,'2026-08-06 00:00:00','2026-08-06 10:00:00','s208-1'),(134,208,325,30,'2026-08-10 00:00:00','2026-08-10 10:00:00','s208-2'),(135,208,327,30,'2026-08-13 00:00:00','2026-08-13 10:00:00','s208-3'),(136,208,330,30,'2026-08-16 00:00:00','2026-08-16 10:00:00','s208-4'),(137,208,326,30,'2026-08-19 00:00:00','2026-08-19 10:00:00','s208-5'),(138,208,323,30,'2026-08-22 00:00:00','2026-08-22 10:00:00','s208-6'),(139,209,323,30,'2026-08-07 00:00:00','2026-08-07 10:00:00','s209-1'),(140,209,328,30,'2026-08-12 00:00:00','2026-08-12 10:00:00','s209-2'),(141,209,328,30,'2026-08-13 00:00:00','2026-08-13 10:00:00','s209-3'),(142,209,329,30,'2026-08-16 00:00:00','2026-08-16 10:00:00','s209-4'),(143,209,326,30,'2026-08-20 00:00:00','2026-08-20 10:00:00','s209-5'),(144,209,322,30,'2026-08-22 00:00:00','2026-08-22 10:00:00','s209-6'),(145,209,324,30,'2026-08-23 00:00:00','2026-08-23 10:00:00','s209-7'),(146,1,323,NULL,'2026-08-26 00:09:56','2026-08-26 00:09:56','tud718rel2vj9e07qpeir85hn9qq0ql6');
/*!40000 ALTER TABLE `product_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `is_custom` tinyint(1) NOT NULL DEFAULT '0',
  `variant_name1` varchar(50) NOT NULL DEFAULT 'Warna',
  `variant_name2` varchar(50) NOT NULL DEFAULT 'Ukuran',
  `image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_product_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=333 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (322,1,'Kaos','kaos','Kaos berkualitas dengan pilihan warna: biru muda, cokelat, cream, hijau army, hitam, kuning, maroon, merah, navy, pink, putih, serta ukuran S–M–L–XL–XXL. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',80000.00,748,0,'Warna','Ukuran','produk_322_1787677773.jpeg','2026-08-21 19:50:15'),(323,2,'Kaos Custom','kaos-custom','Kaos Custom berkualitas dengan pilihan warna: abu-abu, biru muda, cokelat, cream, hijau army, hitam, kuning, maroon, merah, pink, putih, serta ukuran S–M–L–XL–XXL. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',75000.00,748,1,'Warna','Ukuran','produk_323_1787677748.jpeg','2026-08-21 19:50:15'),(324,3,'Polo','polo','Polo berkualitas dengan pilihan warna: abu-abu, biru muda, cokelat, cream, hijau army, hitam, kuning, maroon, merah, pink, putih, serta ukuran S–M–L–XL–XXL. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',75000.00,748,0,'Warna','Ukuran','produk_324_1787677720.jpeg','2026-08-21 19:50:15'),(325,4,'PDL','pdl','PDL berkualitas dengan pilihan warna: abu-abu, biru muda, cream, hijau army, hitam, kuning, maroon, merah, pink, putih, serta ukuran S–M–L–XL–XXL. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',180000.00,685,0,'Warna','Ukuran','produk_325_1787677681.jpeg','2026-08-21 19:50:15'),(326,5,'Rompi','rompi','Rompi berkualitas dengan pilihan warna: abu-abu, biru muda, cokelat, cream, hijau army, hitam, kuning, maroon, merah, navy, pink, putih, serta ukuran S–M–L–XL–XXL. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',60000.00,824,0,'Warna','Ukuran','produk_326_1787677627.jpeg','2026-08-21 19:50:15'),(327,6,'Jaket','jaket','Jaket berkualitas dengan pilihan warna: abu-abu, biru muda, cokelat, cream, hijau army, hitam, maroon, merah, navy, pink, putih, serta ukuran S–M–L–XL–XXL. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',180000.00,748,0,'Warna','Ukuran','produk_327_1787677555.jpeg','2026-08-21 19:50:15'),(328,7,'Lanyard','lanyard','Lanyard berkualitas dengan pilihan warna: abu-abu, biru muda, cokelat, cream, hijau army, hitam, kuning, maroon, navy, pink, putih. Bahan nyaman, jahitan rapi, cocok untuk aktivitas harian.',45000.00,154,0,'Warna','','produk_328_1787677512.jpeg','2026-08-21 19:50:15'),(329,1,'Topi','topi','-',75000.00,50,0,'Warna','','produk_329_1787677474.jpeg','2026-08-21 19:50:15'),(330,1,'Setelan','setelan','-',150000.00,100,0,'Warna','Ukuran','produk_330_1787677438.jpeg','2026-08-21 19:50:15');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT '5' COMMENT '1-5 bintang',
  `review` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`user_id`,`product_id`),
  KEY `fk_rating_user` (`user_id`),
  KEY `fk_rating_product` (`product_id`),
  CONSTRAINT `fk_rating_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rating_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
INSERT INTO `ratings` VALUES (1,119,329,5,'adsasd','2026-08-22 12:50:31','2026-08-22 12:50:31'),(2,119,330,5,'kljlhloh','2026-08-23 13:04:30','2026-08-23 13:04:30'),(72,200,322,4,'dummy','2026-08-05 10:00:00','2026-08-05 10:00:00'),(73,200,323,5,'dummy','2026-08-07 10:00:00','2026-08-07 10:00:00'),(74,200,324,4,'dummy','2026-08-10 10:00:00','2026-08-10 10:00:00'),(75,200,326,3,'dummy','2026-08-13 10:00:00','2026-08-13 10:00:00'),(76,201,322,5,'dummy','2026-08-06 10:00:00','2026-08-06 10:00:00'),(77,201,324,4,'dummy','2026-08-09 10:00:00','2026-08-09 10:00:00'),(78,201,329,5,'dummy','2026-08-12 10:00:00','2026-08-12 10:00:00'),(79,202,323,5,'dummy','2026-08-04 10:00:00','2026-08-04 10:00:00'),(80,202,328,4,'dummy','2026-08-11 10:00:00','2026-08-11 10:00:00'),(81,202,329,3,'dummy','2026-08-15 10:00:00','2026-08-15 10:00:00'),(82,203,322,4,'dummy','2026-08-08 10:00:00','2026-08-08 10:00:00'),(83,203,326,4,'dummy','2026-08-14 10:00:00','2026-08-14 10:00:00'),(84,203,328,5,'dummy','2026-08-17 10:00:00','2026-08-17 10:00:00'),(85,204,325,5,'dummy','2026-08-07 10:00:00','2026-08-07 10:00:00'),(86,204,330,5,'dummy','2026-08-10 10:00:00','2026-08-10 10:00:00'),(87,204,327,4,'dummy','2026-08-16 10:00:00','2026-08-16 10:00:00'),(88,205,325,5,'dummy','2026-08-05 10:00:00','2026-08-05 10:00:00'),(89,205,330,4,'dummy','2026-08-11 10:00:00','2026-08-11 10:00:00'),(90,205,327,4,'dummy','2026-08-18 10:00:00','2026-08-18 10:00:00'),(91,206,325,4,'dummy','2026-08-09 10:00:00','2026-08-09 10:00:00'),(92,206,330,5,'dummy','2026-08-13 10:00:00','2026-08-13 10:00:00'),(93,206,327,3,'dummy','2026-08-19 10:00:00','2026-08-19 10:00:00'),(94,207,325,5,'dummy','2026-08-08 10:00:00','2026-08-08 10:00:00'),(95,207,330,4,'dummy','2026-08-15 10:00:00','2026-08-15 10:00:00'),(96,207,327,5,'dummy','2026-08-21 10:00:00','2026-08-21 10:00:00'),(97,208,322,3,'dummy','2026-08-06 10:00:00','2026-08-06 10:00:00'),(98,208,325,4,'dummy','2026-08-10 10:00:00','2026-08-10 10:00:00'),(99,208,327,4,'dummy','2026-08-17 10:00:00','2026-08-17 10:00:00'),(100,208,330,3,'dummy','2026-08-22 10:00:00','2026-08-22 10:00:00'),(101,209,323,4,'dummy','2026-08-07 10:00:00','2026-08-07 10:00:00'),(102,209,328,5,'dummy','2026-08-12 10:00:00','2026-08-12 10:00:00'),(103,209,329,4,'dummy','2026-08-16 10:00:00','2026-08-16 10:00:00'),(104,209,326,3,'dummy','2026-08-23 10:00:00','2026-08-23 10:00:00'),(105,119,322,4,'dummy','2026-08-19 10:00:00','2026-08-19 10:00:00'),(106,119,328,5,'dummy','2026-08-20 10:00:00','2026-08-20 10:00:00');
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@ecommerce.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','admin','08123456789','Jl. Admin No. 1, Jakarta','2026-04-28 16:50:57'),(119,'asep','asep@mail.com','$2y$12$x5y8mAWhCj5CjPc/ImC4KOTd6XvTosZmTr1884meBWMEf5U74QIKy','user','1234567890',NULL,'2026-04-29 08:50:10'),(120,'sapri','sapri@mail.com','229e487e9a1668ecc424646c288cfcab','user','01234567890',NULL,'2026-04-29 08:54:14'),(152,'agus','agus@mail.com','01c3c766ce47082b1b130daedd347ffd','user','123',NULL,'2026-04-30 19:17:18'),(153,'test_user_ai','test@ai.com','$2y$10$8y.S2i/YyvI9vI9vI9vI9u','user',NULL,NULL,'2026-04-30 22:04:03'),(200,'budi','budi@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300002','Jl. Merdeka 1, Bandung','2026-07-16 00:00:00'),(201,'deni','deni@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300003','Jl. Asia Afrika 5, Bandung','2026-07-18 00:00:00'),(202,'rina','rina@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300004','Jl. Buah Batu 12, Bandung','2026-07-20 00:00:00'),(203,'wati','wati@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300005','Jl. Dago 8, Bandung','2026-07-22 00:00:00'),(204,'eko','eko@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300006','Jl. Gatot Subroto 20, Jakarta','2026-07-24 00:00:00'),(205,'maya','maya@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300007','Jl. Sudirman 33, Jakarta','2026-07-26 00:00:00'),(206,'lia','lia@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300008','Jl. Thamrin 15, Jakarta','2026-07-28 00:00:00'),(207,'tommy','tommy@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300009','Jl. Rasuna Said 7, Jakarta','2026-07-30 00:00:00'),(208,'sari','sari@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300010','Jl. Pemuda 25, Surabaya','2026-08-01 00:00:00'),(209,'yanto','yanto@example.com','$2y$10$4LrCBCjVAj7Ua1VMSsMNB.LRaJh/BD5KzH4hadXvva8I/2EXybhT.','user','812300011','Jl. Malioboro 4, Yogyakarta','2026-08-03 00:00:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists_bak`
--

DROP TABLE IF EXISTS `wishlists_bak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlists_bak` (
  `id` int NOT NULL DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists_bak`
--

LOCK TABLES `wishlists_bak` WRITE;
/*!40000 ALTER TABLE `wishlists_bak` DISABLE KEYS */;
INSERT INTO `wishlists_bak` VALUES (1,119,329,'2026-08-22 12:50:52');
/*!40000 ALTER TABLE `wishlists_bak` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ecommerce_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-26  0:18:24
