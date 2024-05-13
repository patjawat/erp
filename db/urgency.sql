-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysqlDB
-- Generation Time: May 13, 2024 at 10:04 AM
-- Server version: 5.7.44
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dansai`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorise`
--

CREATE TABLE `categorise` (
  `id` int(11) NOT NULL,
  `ref` varchar(255) DEFAULT NULL,
  `category_id` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL COMMENT 'รหัส',
  `emp_id` varchar(255) DEFAULT NULL COMMENT 'พนักงาน',
  `name` varchar(255) NOT NULL COMMENT 'ชนิดข้อมูล',
  `title` varchar(255) DEFAULT NULL COMMENT 'ชื่อ',
  `description` varchar(255) DEFAULT NULL COMMENT 'รายละเอียดเพิ่มเติม',
  `data_json` json DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categorise`
--

INSERT INTO `categorise` (`id`, `ref`, `category_id`, `code`, `emp_id`, `name`, `title`, `description`, `data_json`, `active`) VALUES
(2805, NULL, NULL, '1', NULL, 'urgency', 'ปกติ', NULL, NULL, 1),
(2806, NULL, NULL, '2', NULL, 'urgency', 'ด่วน', NULL, NULL, 1),
(2807, NULL, NULL, '3', NULL, 'urgency', 'ด่วนมาก', NULL, NULL, 1),
(2808, NULL, NULL, '4', NULL, 'urgency', 'ด่วนที่สุด', NULL, NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorise`
--
ALTER TABLE `categorise`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorise`
--
ALTER TABLE `categorise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2809;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
