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

INSERT INTO `categorise` (`code`, `name`,`title`) VALUES
('LT1', 'leave_type','ลาป่วย'),
('LT2', 'leave_type','ลาคลอดบุตร'),
('LT3', 'leave_type','ลากิจ'),
('LT4', 'leave_type','ลาพักผ่อน'),
('LT5', 'leave_type','ลาอุปสมบท'),
('LT6', 'leave_type','ลาช่วยภริยาคลอด'),
('LT7', 'leave_type','ลาเกณฑ์ทหาร'),
('LT8', 'leave_type','ลาศึกษา ฝึกอบรม'),
('LT9', 'leave_type','ลาทำงานต่างประเทศ'),
('LT10', 'leave_type','ลาติดตามคู่สมรส'),
('LT11', 'leave_type','ลาฟื้นฟูอาชีพ'),
('LT12', 'leave_type','ลาออก'),
('LT13', 'leave_type','ลาป่วยตามกฎหมายฯ');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
