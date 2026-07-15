-- MySQL dump 10.13  Distrib 8.0.45, for Linux (aarch64)
--
-- Host: localhost    Database: ubonrat
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `am_asset_depreciation_monthly`
--

LOCK TABLES `am_asset_depreciation_monthly` WRITE;
/*!40000 ALTER TABLE `am_asset_depreciation_monthly` DISABLE KEYS */;
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
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'หมายเหตุ',
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

-- Dump completed on 2026-07-12 19:52:49
