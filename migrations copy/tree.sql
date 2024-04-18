-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysqlDB
-- Generation Time: Jan 30, 2024 at 07:06 PM
-- Server version: 5.7.44
-- PHP Version: 8.2.15

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
-- Dumping data for table `tree`
--

INSERT INTO `tree` (`id`, `root`, `lft`, `rgt`, `lvl`, `name`, `code`, `tb_name`, `leader`, `data_json`, `icon`, `icon_type`, `active`, `selected`, `disabled`, `readonly`, `visible`, `collapsed`, `movable_u`, `movable_d`, `movable_l`, `movable_r`, `removable`, `removable_all`, `child_allowed`, `visibleOrig`, `disabledOrig`) VALUES
(1, 1, 1, 64, 0, 'กลุ่มอำนวยการ', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"147\", \"leader2\": \"65\", \"leader1_fullname\": \"นายสันทัด บุญเรือง\", \"leader2_fullname\": \"นางทิพพาวดี สืบนุการณ์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0),
(2, 1, 2, 19, 1, 'กลุ่มงานบริหาร', NULL, 'diagram', NULL, '{\"phone\": \"123\", \"leader1\": \"8\", \"leader2\": \"62\", \"leader1_fullname\": \"นายเดชา สายบุญตั้ง\", \"leader2_fullname\": \"นายสุรชัย สิทธิศักดิ์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(3, 1, 20, 21, 1, 'กลุ่มงานเทคนิคการแพทย์', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"152\", \"leader2\": \"160\", \"leader1_fullname\": \"นายธนภัทร ต.ประดิษฐ์\", \"leader2_fullname\": \"น.ส.ศุกล หนูมณี\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(4, 1, 22, 23, 1, 'กลุ่มงานทันตกรรม', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"142\", \"leader2\": \"315\", \"leader1_fullname\": \"นายกฤษดาพันธ์ จันทนะ\", \"leader2_fullname\": \"นายศุภเกียรติ ศรีอินทร์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(5, 1, 24, 25, 1, 'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"69\", \"leader2\": \"70\", \"leader1_fullname\": \"น.ส.ดาริน จึงพัฒนาวดี\", \"leader2_fullname\": \"นางอัญชลี พิมพ์รัตน์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(6, 1, 26, 27, 1, 'กลุ่มงานการแพทย์', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"65\", \"leader2\": \"157\", \"leader1_fullname\": \"นางทิพพาวดี สืบนุการณ์\", \"leader2_fullname\": \"น.ส.สุขุมาล หุนทนทาน\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(7, 1, 28, 33, 1, 'กลุ่มงานประกันสุขภาพและสารสนเทศทางการแพทย์', NULL, 'diagram', NULL, '{\"phone\": \"555\", \"leader1\": \"26\", \"leader2\": \"290\", \"leader1_fullname\": \"ชายอภิชาติ ดีด่านค้อ\", \"leader2_fullname\": \"น.ส.พวรรณ์ตรี แสงนวล\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(8, 1, 34, 39, 1, 'กลุ่มงานดิจิทัลทางการแพทย์และสุขภาพ', NULL, 'diagram', NULL, '{\"phone\": \"444\", \"leader1\": \"26\", \"leader2\": \"88\", \"leader1_fullname\": \"ชายอภิชาติ ดีด่านค้อ\", \"leader2_fullname\": \"นายภาณุ ภักดีสาร\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(9, 1, 40, 41, 1, 'กลุ่มงานเวชกรรมฟื้นฟู', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"104\", \"leader2\": \"175\", \"leader1_fullname\": \"นางอรอุมา อุมารังษี\", \"leader2_fullname\": \"น.ส.นิศากร เกิดแสง\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(10, 1, 42, 43, 1, 'กลุ่มงานบริการด้านปฐมภูมิและองค์รวม', NULL, 'diagram', NULL, '{\"phone\": \"711\", \"leader1\": \"108\", \"leader2\": \"178\", \"leader1_fullname\": \"นางพุทธลักษ์ ดีสม\", \"leader2_fullname\": \"นางสายใจ ถึงนาค\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(11, 1, 44, 45, 1, 'กลุ่มงานการแพทย์แผนไทย', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"71\", \"leader2\": \"249\", \"leader1_fullname\": \"นายไพโรจน์ ทองคำ\", \"leader2_fullname\": \"น.ส.ภริตา ทองยา\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(12, 1, 46, 61, 1, 'กลุ่มการพยาบาล', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"118\", \"leader2\": \"67\", \"leader1_fullname\": \"นางวิมลมาศ พงษ์อำนวยกฤต\", \"leader2_fullname\": \"นางพรพิไล นิยมถิ่น\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(13, 1, 62, 63, 1, 'กลุ่มงานจิตเวชและยาเสพติด', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"117\", \"leader2\": \"208\", \"leader1_fullname\": \"นางสิริพร สิทธิศักดิ์\", \"leader2_fullname\": \"นางจำนงค์ ยิ่งยืน\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(14, 1, 3, 4, 2, 'งานพัสดุ', NULL, 'diagram', NULL, '{\"phone\": \"121\", \"leader1\": \"260\", \"leader2\": \"34\", \"leader1_fullname\": \"น.ส.อิษญาฎา คำแก้ว\", \"leader2_fullname\": \"นางศิรินันท์ เชื้อบุญมี\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(15, 1, 5, 6, 2, 'งานธุรการ', NULL, 'diagram', NULL, '{\"phone\": \"121\", \"leader1\": \"9\", \"leader2\": \"345\", \"leader1_fullname\": \"นางนภัสนันท์ จรัสโสภาสิทธิ์\", \"leader2_fullname\": \"น.ส.วรัญชฎา เชื้อบุญมี\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(16, 1, 7, 8, 2, 'งานการเงินและบัญชี', NULL, 'diagram', NULL, '{\"phone\": \"123\", \"leader1\": \"229\", \"leader2\": \"5\", \"leader1_fullname\": \"นางภัทรา ประดิษฐ์ศิลา\", \"leader2_fullname\": \"น.ส.กรรณิกา เหมบุรุษ\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(17, 1, 9, 10, 2, 'งานซ่อมบำรุง', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"312\", \"leader2\": \"284\", \"leader1_fullname\": \"นายฐิติกร สุดตะนา\", \"leader2_fullname\": \"นายวิทยา ทรงพุฒิ\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(18, 1, 11, 12, 2, 'งานยานพาหนะ', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"62\", \"leader2\": \"246\", \"leader1_fullname\": \"นายสุรชัย สิทธิศักดิ์\", \"leader2_fullname\": \"นายราเมศ ยะเสน\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(19, 1, 13, 14, 2, 'งานรักษาความปลอดภัย', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"62\", \"leader2\": \"133\", \"leader1_fullname\": \"นายสุรชัย สิทธิศักดิ์\", \"leader2_fullname\": \"นายพิษณุลักษณ์ สุทธิ\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(20, 1, 15, 16, 2, 'งานรักษาความสะอาด', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"260\", \"leader2\": \"145\", \"leader1_fullname\": \"น.ส.อิษญาฎา คำแก้ว\", \"leader2_fullname\": \"นางสมภาร สุทธิ\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(21, 1, 17, 18, 2, 'งานภูมิทัศน์', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"312\", \"leader2\": \"168\", \"leader1_fullname\": \"นายฐิติกร สุดตะนา\", \"leader2_fullname\": \"นายสายฝน ธรรมวงษ์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(22, 1, 29, 30, 2, 'ศูนย์ประกันสุขภาพ', NULL, 'diagram', NULL, '{\"phone\": \"555\", \"leader1\": \"290\", \"leader2\": \"151\", \"leader1_fullname\": \"น.ส.พวรรณ์ตรี แสงนวล\", \"leader2_fullname\": \"น.ส.วรรณภรณ์ นนทะโคตร\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(23, 1, 31, 32, 2, 'งานเวชระเบียนและสถิติ', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"196\", \"leader2\": \"111\", \"leader1_fullname\": \"นายเกียรติพงษ์ ฤทธิ์ศักดิ์\", \"leader2_fullname\": \"นายทองดี อุ่นแก้ว\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(24, 1, 35, 36, 2, 'งานศูนย์คอมพิวเตอร์', NULL, 'diagram', NULL, '{\"phone\": \"444\", \"leader1\": \"88\", \"leader2\": \"121\", \"leader1_fullname\": \"นายภาณุ ภักดีสาร\", \"leader2_fullname\": \"นายสมคิด พรหมรักษา\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(25, 1, 37, 38, 2, 'งานยุทธศาสตร์และสารสนเทศ', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"26\", \"leader2\": \"316\", \"leader1_fullname\": \"ชายอภิชาติ ดีด่านค้อ\", \"leader2_fullname\": \"น.ส.บัวบูชา มหารักษิต\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(26, 1, 47, 48, 2, 'งานการพยาบาลผู้ป่วยนอก', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"76\", \"leader2\": \"55\", \"leader1_fullname\": \"นางคุณารักษ์ มณีนุษย์\", \"leader2_fullname\": \"นางเมตตาจิตร เจริญทรัพย์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(27, 1, 49, 50, 2, 'งานการพยาบาลผู้ป่วยใน 1', NULL, 'diagram', NULL, '{\"phone\": \"100\", \"leader1\": \"116\", \"leader2\": \"161\", \"leader1_fullname\": \"น.ส.เยาวฤทธิ์ สุวรรณสิงห์\", \"leader2_fullname\": \"นางบุญลักษณ์ บุญมี\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(28, 1, 51, 52, 2, 'งานการพยาบาลผู้ป่วยใน 2', NULL, 'diagram', NULL, '{\"phone\": \"200\", \"leader1\": \"66\", \"leader2\": \"115\", \"leader1_fullname\": \"นางณัฐิยา ดีด่านค้อ\", \"leader2_fullname\": \"น.ส.พงศ์วรินทร์ นันทกร\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(29, 1, 53, 54, 2, 'งานการพยาบาลผู้ป่วยอุบัติเหตุฉุกเฉินและนิติเวช', NULL, 'diagram', NULL, '{\"phone\": \"511\", \"leader1\": \"154\", \"leader2\": \"200\", \"leader1_fullname\": \"นางรัศมีแข จันธุ\", \"leader2_fullname\": \"นางวรรณฑณี สุวรรณกูฎ\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(30, 1, 55, 56, 2, 'งานการพยาบาลผู้คลอด', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"56\", \"leader2\": \"150\", \"leader1_fullname\": \"นางพรพิมล เห็มสุทธิ์\", \"leader2_fullname\": \"นางวรพร ต.ประดิษฐ์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, NULL, NULL),
(31, 1, 57, 58, 2, 'งานการพยาบาลผู้ป่วยผ่าตัดและวิสัญญีพยาบาล', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"68\", \"leader2\": \"172\", \"leader1_fullname\": \"นางศิวาพร ดอกนาค\", \"leader2_fullname\": \"นางเอื้อมพร เกตุพิบูลย์\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0),
(32, 1, 59, 60, 2, 'งานการพยาบาลหน่วยควบคุมการติดเชื้อ/งานจ่ายกลาง', NULL, 'diagram', NULL, '{\"phone\": \"\", \"leader1\": \"59\", \"leader2\": \"\", \"leader1_fullname\": \"น.ส.สุวรรณี วังคีรี\", \"leader2_fullname\": \"\"}', NULL, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
