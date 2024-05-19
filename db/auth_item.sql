-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysqlDB
-- Generation Time: May 19, 2024 at 08:44 AM
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

--
-- Dumping data for table `auth_item`
--

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('computer', 1, 'ศูนย์คอมพิวเตอร์', NULL, NULL, 1715695352, 1716107976),
('computer_ma', 1, 'หัวหน้าศูนย์คอมพิวเตอร์', NULL, NULL, 1715695469, 1716107962),
('hr', 1, 'เจ้าหน้าที่ HR', NULL, NULL, 1716108146, 1716108146),
('medical', 1, 'ศูนย์เครื่องมือแพทย์', NULL, NULL, 1715695412, 1716108063),
('medical_ma', 1, 'หัวหน้าช่างศูนย์เครื่องมือแพทย์', NULL, NULL, 1715695546, 1716108083),
('technician', 1, 'งานซ่อมบำรุง', NULL, NULL, 1715695322, 1716108015),
('technician_ma', 1, 'หัวหน้างานซ่อมบำรุง', NULL, NULL, 1715695507, 1716108034);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
