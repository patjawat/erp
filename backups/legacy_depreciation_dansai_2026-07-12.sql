-- MySQL dump 10.13  Distrib 8.0.45, for Linux (aarch64)
--
-- Host: localhost    Database: dansai
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `am_asset_depreciation_monthly`
--

DROP TABLE IF EXISTS `am_asset_depreciation_monthly`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `am_asset_depreciation_monthly` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL COMMENT 'FK asset.id',
  `fiscal_year` int NOT NULL COMMENT 'ปี (ค.ศ. e.g. 2024)',
  `month` tinyint NOT NULL COMMENT 'เดือน 1-12',
  `days_used` smallint NOT NULL DEFAULT '30' COMMENT 'จำนวนวันใช้ในเดือน',
  `beginning_value` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'มูลค่าต้นเดือน',
  `depreciation_amount` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'ค่าเสื่อมประจำเดือน',
  `accumulated_depreciation` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'ค่าเสื่อมสะสม',
  `remaining_value` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'มูลค่าปลายเดือน',
  `processed_at` datetime DEFAULT NULL COMMENT 'วันเวลาที่ประมวลผล',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_am_dep_monthly_asset_year_month` (`asset_id`,`fiscal_year`,`month`),
  KEY `idx_am_dep_monthly_asset_id` (`asset_id`),
  KEY `idx_am_dep_monthly_fiscal_month` (`fiscal_year`,`month`),
  CONSTRAINT `fk_am_dep_monthly_asset` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `am_asset_depreciation_monthly`
--

LOCK TABLES `am_asset_depreciation_monthly` WRITE;
/*!40000 ALTER TABLE `am_asset_depreciation_monthly` DISABLE KEYS */;
INSERT INTO `am_asset_depreciation_monthly` VALUES (7,1886,2026,4,14,160000.00,1244.46,1244.46,158755.54,'2026-04-26 21:43:38'),(8,1887,2026,4,14,50000.00,388.92,388.92,49611.08,'2026-04-26 21:43:38'),(9,1888,2026,4,14,50000.00,388.92,388.92,49611.08,'2026-04-26 21:43:38'),(10,1889,2026,4,30,6248.44,113.32,664.88,6135.12,'2026-04-26 21:43:38'),(11,1890,2026,4,15,16000.00,222.15,222.15,15777.85,'2026-04-26 21:43:38'),(12,1891,2026,4,29,4000.00,107.30,107.30,3892.70,'2026-04-26 21:43:38'),(13,35,2026,5,30,13320.79,416.66,87095.87,12904.13,'2026-05-27 08:59:58'),(14,274,2026,5,30,1413.36,128.38,6419.02,1284.98,'2026-05-27 08:59:58'),(15,281,2026,5,30,434.46,61.65,3327.19,372.81,'2026-05-27 08:59:58'),(16,420,2026,5,30,1484822.91,20824.99,1035002.08,1463997.92,'2026-05-27 08:59:58'),(17,1886,2026,5,30,158755.54,2666.65,3911.11,156088.89,'2026-05-27 08:59:58'),(18,1887,2026,5,30,49611.08,833.32,1222.24,48777.76,'2026-05-27 08:59:58'),(19,1888,2026,5,30,49611.08,833.32,1222.24,48777.76,'2026-05-27 08:59:58'),(20,1889,2026,5,30,6135.12,113.32,778.20,6021.80,'2026-05-27 08:59:58'),(21,1890,2026,5,30,15777.85,444.42,666.57,15333.43,'2026-05-27 08:59:58'),(22,1891,2026,5,30,3892.70,111.08,218.38,3781.62,'2026-05-27 08:59:58'),(23,1892,2026,5,30,10193.50,284.97,351.47,9908.53,'2026-05-27 08:59:58'),(24,1893,2026,5,26,5000.00,72.28,72.28,4927.72,'2026-05-27 08:59:58'),(25,1894,2026,5,30,24415.95,691.64,1175.69,23724.31,'2026-05-27 08:59:58'),(26,1895,2026,5,30,24415.95,691.64,1175.69,23724.31,'2026-05-27 08:59:58'),(27,1896,2026,5,10,499000.00,2772.20,2772.20,496227.80,'2026-05-27 08:59:58'),(28,1884,2026,5,30,23527.81,416.65,1888.84,23111.16,'2026-06-04 19:53:07'),(29,1897,2026,5,26,5000.00,72.28,72.28,4927.72,'2026-06-04 19:53:07'),(30,1898,2026,5,26,5000.00,72.28,72.28,4927.72,'2026-06-04 19:53:07'),(31,1899,2026,5,26,5000.00,72.28,72.28,4927.72,'2026-06-04 19:53:07'),(32,1900,2026,5,26,5000.00,72.28,72.28,4927.72,'2026-06-04 19:53:07'),(33,1902,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(34,1903,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(35,1904,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(36,1905,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(37,1906,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(38,1907,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(39,1908,2026,5,30,47361.14,833.32,3472.18,46527.82,'2026-06-04 19:53:07'),(40,1909,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(41,1910,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(42,1911,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(43,1912,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(44,1913,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(45,1914,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(46,1915,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(47,1916,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(48,1917,2026,5,30,142083.41,2499.98,10416.57,139583.43,'2026-06-04 19:53:07'),(49,1918,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(50,1919,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(51,1920,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(52,1921,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(53,1922,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(54,1923,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(55,1924,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(56,1925,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(57,1926,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(58,1927,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(59,1928,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(60,1929,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(61,1930,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(62,1931,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07'),(63,1932,2026,5,30,15803.78,472.19,1668.41,15331.59,'2026-06-04 19:53:07');
/*!40000 ALTER TABLE `am_asset_depreciation_monthly` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `am_asset_depreciations`
--

DROP TABLE IF EXISTS `am_asset_depreciations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `am_asset_depreciations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL COMMENT 'FK asset.id',
  `fiscal_year` int NOT NULL COMMENT 'ปีงบประมาณ (ค.ศ. e.g. 2024)',
  `opening_value` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'มูลค่าต้นปี',
  `depreciation_amount` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'ค่าเสื่อมประจำปี',
  `accumulated_depreciation` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'ค่าเสื่อมสะสม',
  `closing_value` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'มูลค่าปลายปี',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ล็อกเมื่อปิดปี',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_am_asset_depreciations_asset_year` (`asset_id`,`fiscal_year`),
  KEY `idx_am_asset_depreciations_asset_id` (`asset_id`),
  KEY `idx_am_asset_depreciations_fiscal_year` (`fiscal_year`),
  CONSTRAINT `fk_am_asset_depreciations_asset` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `am_asset_depreciations`
--

LOCK TABLES `am_asset_depreciations` WRITE;
/*!40000 ALTER TABLE `am_asset_depreciations` DISABLE KEYS */;
/*!40000 ALTER TABLE `am_asset_depreciations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `am_depreciation_closings`
--

DROP TABLE IF EXISTS `am_depreciation_closings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `am_depreciation_closings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fiscal_year` int NOT NULL COMMENT 'ปีงบประมาณ (ค.ศ.)',
  `closed_at` datetime NOT NULL COMMENT 'วันเวลาปิด',
  `closed_by` int DEFAULT NULL COMMENT 'ผู้ปิด',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'หมายเหตุ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_am_depreciation_closings_year` (`fiscal_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `am_depreciation_closings`
--

LOCK TABLES `am_depreciation_closings` WRITE;
/*!40000 ALTER TABLE `am_depreciation_closings` DISABLE KEYS */;
/*!40000 ALTER TABLE `am_depreciation_closings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-12 19:52:48
