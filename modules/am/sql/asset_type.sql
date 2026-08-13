-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysqlDB
-- Generation Time: Aug 13, 2026 at 04:02 PM
-- Server version: 8.0.45
-- PHP Version: 8.3.33

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
-- Dumping data for table `categorise`
--

INSERT INTO `categorise` (`group_id`, `code`, `title`, `category_id`, `name`, `data_json`) VALUES
('BLDG', '1', 'อาคารถาวร', '2', 'asset_type', '{\"depreciation\": \"4\", \"service_life\": \"25\"}'),
(NULL, '2.1', 'สิ่งปลูกสร้าง อาคารชั่วคราว/โรงเรือน', '2', 'asset_type', '{\"depreciation\": \"10\", \"service_life\": \"10\"}'),
(NULL, '2.2', 'สิ่งปลูกสร้าง ใช้คอนกรีตเสริมเหล็กหรือโครงเหล็กเป็นส่วนประกอบหลัก', '2', 'asset_type', '{\"depreciation\": \"6.66\", \"service_life\": \"15\"}'),
(NULL, '2.3', 'สิ่งปลูกสร้าง ใช้ไม้หรือวัสดุอื่นๆ เป็นส่วนประกอบหลัก', '2', 'asset_type', '{\"depreciation\": \"20\", \"service_life\": \"5\"}'),
(NULL, '19', 'ครุภัณฑ์อื่นๆ', '', 'asset_type', '{\"depreciation\": \"20\", \"service_life\": \"5\"}'),
('INTAN', '20', 'สินทรัพย์ไม่มีตัวตัน', '', 'asset_type', '{\"depreciation\": \"33.3\", \"service_life\": \"3\"}'),
('MATER', 'M1', 'วัสดุสำนักงาน', '4', 'asset_type', NULL),
('MATER', 'M2', 'วัสดุไฟฟ้าและวิทยุ', '4', 'asset_type', NULL),
('MATER', 'M3', 'วัสดุงานบ้านงานครัว', '4', 'asset_type', NULL),
('MATER', 'M4', 'วัสดุก่อสร้างและประปา', '4', 'asset_type', NULL),
('MATER', 'M5', 'วัสดุยานพาหนะและขนส่ง', '4', 'asset_type', NULL),
('MATER', 'M6', 'วัสดุเชื้อเพลิงและหล่อลื่น', '4', 'asset_type', NULL),
('MATER', 'M7', 'วัสดุวิทยาศาสตร์หรือการแพทย์', '4', 'asset_type', NULL),
('MATER', 'M8', 'วัสดุการเกษตร', '4', 'asset_type', NULL),
('MATER', 'M9', 'วัสดุโฆษณาและเผยแพร่', '4', 'asset_type', NULL),
('MATER', 'M10', 'วัสดุเครื่องแต่งกาย', '4', 'asset_type', NULL),
('MATER', 'M11', 'วัสดุกีฬา', '4', 'asset_type', NULL),
('MATER', 'M12', 'วัสดุคอมพิวเตอร์', '4', 'asset_type', NULL),
('MATER', 'M13', 'วัสดุสนาม', '4', 'asset_type', NULL),
('MATER', 'M14', 'วัสดุการศึกษา', '4', 'asset_type', NULL),
('MATER', 'M15', 'วัสดุสำรวจ', '4', 'asset_type', NULL),
('MATER', 'M16', 'วัสดุอื่นๆ', '4', 'asset_type', NULL),
('MATER', 'M17', 'วัสดุแบบพิมพ์', '4', 'asset_type', NULL),
('MATER', 'M18', 'วัสดุบริโภค', '4', 'asset_type', NULL),
('MATER', 'M19', 'วัสดุทันตกรรม', '4', 'asset_type', NULL),
('MATER', 'M20', 'วัสดุวิทยาศาสตร์', '4', 'asset_type', NULL),
('MATER', 'M21', 'วัสดุรังสี', '4', 'asset_type', NULL),
('MATER', 'M22', 'วัสดุการแพทย์ทั่วไป', '4', 'asset_type', NULL),
('MATER', 'M23', 'ยา|เวชภัณฑ์', '4', 'asset_type', NULL),
('MATER', 'M24', 'วัสดุเภสัชกรรม', '4', 'asset_type', NULL),
('MATER', 'M25', 'จ้างเหมาอื่นๆ', '4', 'asset_type', NULL),
('MATER', 'M26', 'วัสดุการแพทย์ ออกซิเจน', '4', 'asset_type', NULL),
('EQUIP', 'MED', 'ครุภัณฑ์การแพทย์', NULL, 'asset_type', '{\"title_en\": \"Medical Equipment\", \"description\": \"อุปกรณ์ทางการแพทย์และเครื่องมือรักษาพยาบาล\"}'),
('EQUIP', 'ELE', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', NULL, 'asset_type', '{\"title_en\": \"Electrical and Radio Equipment\", \"description\": \"อุปกรณ์ไฟฟ้าและเครื่องมือวิทยุกสารสนเทศ\"}'),
('EQUIP', 'IND', 'ครุภัณฑ์โรงงาน', NULL, 'asset_type', '{\"title_en\": \"Industrial Equipment\", \"description\": \"เครื่องจักรและอุปกรณ์ในงานโรงงาน การผลิต\"}'),
('EQUIP', 'AGR', 'ครุภัณฑ์การเกษตร', NULL, 'asset_type', '{\"title_en\": \"Agricultural Equipment\", \"description\": \"เครื่องมือและอุปกรณ์ทางการเกษตร\"}'),
('EQUIP', 'EDU', 'ครุภัณฑ์การศึกษา', NULL, 'asset_type', '{\"title_en\": \"Educational Equipment\", \"description\": \"อุปกรณ์การเรียนการสอนและวัสดุการศึกษา\"}'),
('EQUIP', 'COM', 'ครุภัณฑ์คอมพิวเตอร์', NULL, 'asset_type', '{\"title_en\": \"Computer Equipment\", \"description\": \"เครื่องคอมพิวเตอร์และอุปกรณ์เทคโนโลยีสารสนเทศ\"}'),
('EQUIP', 'ADV', 'ครุภัณฑ์โฆษณาและเผยแพร่', NULL, 'asset_type', '{\"title_en\": \"Advertising and Publishing Equipment\", \"description\": \"อุปกรณ์โฆษณา ประชาสัมพันธ์ และเผยแพร่ข้อมูล\"}'),
('EQUIP', 'HOM', 'ครุภัณฑ์งานบ้านงานครัว', NULL, 'asset_type', '{\"title_en\": \"Household and Kitchen Equipment\", \"description\": \"อุปกรณ์ใช้ในบ้านและครัว สำหรับงานทั่วไป\"}'),
('EQUIP', 'VEH', 'ครุภัณฑ์ยานพาหนะ', NULL, 'asset_type', '{\"title_en\": \"Vehicle Equipment\", \"description\": \"ยานพาหนะและอุปกรณ์การขนส่ง\"}'),
('EQUIP', 'SCI', 'ครุภัณฑ์วิทยาศาสตร์และการแพททย์', NULL, 'asset_type', '{\"title_en\": \"Scientific Equipment\", \"description\": \"เครื่องมือและอุปกรณ์ทางวิทยาศาสตร์และการวิจัย\"}'),
('EQUIP', 'OFF', 'ครุภัณฑ์สำนักงาน', NULL, 'asset_type', '{\"title_en\": \"Office Equipment\", \"description\": \"อุปกรณ์สำนักงานและเครื่องใช้ในการบริหารงาน\"}'),
('EQUIP', 'MED', 'ครุภัณฑ์การแพทย์', NULL, 'asset_type', '{\"title_en\": \"Medical Equipment\", \"description\": \"อุปกรณ์ทางการแพทย์และเครื่องมือรักษาพยาบาล\"}'),
('EQUIP', 'ELE', 'ครุภัณฑ์ไฟฟ้าและวิทยุ', NULL, 'asset_type', '{\"title_en\": \"Electrical and Radio Equipment\", \"description\": \"อุปกรณ์ไฟฟ้าและเครื่องมือวิทยุกสารสนเทศ\"}'),
('EQUIP', 'IND', 'ครุภัณฑ์โรงงาน', NULL, 'asset_type', '{\"title_en\": \"Industrial Equipment\", \"description\": \"เครื่องจักรและอุปกรณ์ในงานโรงงาน การผลิต\"}'),
('EQUIP', 'AGR', 'ครุภัณฑ์การเกษตร', NULL, 'asset_type', '{\"title_en\": \"Agricultural Equipment\", \"description\": \"เครื่องมือและอุปกรณ์ทางการเกษตร\"}'),
('EQUIP', 'EDU', 'ครุภัณฑ์การศึกษา', NULL, 'asset_type', '{\"title_en\": \"Educational Equipment\", \"description\": \"อุปกรณ์การเรียนการสอนและวัสดุการศึกษา\"}'),
('EQUIP', 'COM', 'ครุภัณฑ์คอมพิวเตอร์', NULL, 'asset_type', '{\"title_en\": \"Computer Equipment\", \"description\": \"เครื่องคอมพิวเตอร์และอุปกรณ์เทคโนโลยีสารสนเทศ\"}'),
('EQUIP', 'ADV', 'ครุภัณฑ์โฆษณาและเผยแพร่', NULL, 'asset_type', '{\"title_en\": \"Advertising and Publishing Equipment\", \"description\": \"อุปกรณ์โฆษณา ประชาสัมพันธ์ และเผยแพร่ข้อมูล\"}'),
('EQUIP', 'HOM', 'ครุภัณฑ์งานบ้านงานครัว', NULL, 'asset_type', '{\"title_en\": \"Household and Kitchen Equipment\", \"description\": \"อุปกรณ์ใช้ในบ้านและครัว สำหรับงานทั่วไป\"}'),
('EQUIP', 'VEH', 'ครุภัณฑ์ยานพาหนะ', NULL, 'asset_type', '{\"title_en\": \"Vehicle Equipment\", \"description\": \"ยานพาหนะและอุปกรณ์การขนส่ง\"}'),
('EQUIP', 'SCI', 'ครุภัณฑ์วิทยาศาสตร์', NULL, 'asset_type', '{\"title_en\": \"Scientific Equipment\", \"description\": \"เครื่องมือและอุปกรณ์ทางวิทยาศาสตร์และการวิจัย\"}'),
('EQUIP', 'OFF', 'ครุภัณฑ์สำนักงาน', NULL, 'asset_type', '{\"title_en\": \"Office Equipment\", \"description\": \"อุปกรณ์สำนักงานและเครื่องใช้ในการบริหารงาน\"}'),
('STRUCT', 'STR_GRP_TEMP', 'อาคารชั่วคราว/โรงเรือน', NULL, 'asset_type', '{\"useful_life\": 10, \"depreciation_rate\": 10}'),
('STRUCT', 'STR_GRP_CONCRETE', 'สิ่งก่อสร้าง — คอนกรีต/เหล็ก', NULL, 'asset_type', '{\"useful_life\": 15, \"depreciation_rate\": 6.66}'),
('STRUCT', 'STR_GRP_WOOD', 'สิ่งก่อสร้าง — ไม้/วัสดุอื่น', NULL, 'asset_type', '{\"useful_life\": 5, \"depreciation_rate\": 20}');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
