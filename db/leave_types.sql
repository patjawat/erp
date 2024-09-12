-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysqlDB
-- Generation Time: Sep 12, 2024 at 08:24 AM
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
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`) VALUES
(1, 'ลาป่วย'),
(2, 'ลาคลอดบุตร'),
(3, 'ลากิจ'),
(4, 'ลาพักผ่อน'),
(5, 'ลาอุปสมบท'),
(6, 'ลาช่วยภริยาคลอด'),
(7, 'ลาเกณฑ์ทหาร'),
(8, 'ลาศึกษา ฝึกอบรม'),
(9, 'ลาทำงานต่างประเทศ'),
(10, 'ลาติดตามคู่สมรส'),
(11, 'ลาฟื้นฟูอาชีพ'),
(12, 'ลาออก'),
(13, 'ลาป่วยตามกฎหมายฯ');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
