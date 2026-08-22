-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysqlDB
-- Generation Time: Aug 13, 2026 at 04:01 PM
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

INSERT INTO `categorise` (`code`, `title`, `category_id`, `name`, `data_json`) VALUES
('DF', 'กระตุกไฟฟ้าหัวใจ', 'MED', 'asset_category', '{\"title_en\": \"Defibrillation\"}'),
('MC', 'กล้องจุลทรรศน์ในการผ่าตัด', 'MED', 'asset_category', '{\"title_en\": \"Microscopy\"}'),
('ES', 'กล้องส่องตรวจวินิจฉัยและรักษา', 'MED', 'asset_category', '{\"title_en\": \"Endoscopic examination\"}'),
('PT', 'กายภาพบำบัด', 'MED', 'asset_category', '{\"title_en\": \"Physical Therapy\"}'),
('BB', 'คลังเลือด', 'MED', 'asset_category', '{\"title_en\": \"Blood Bank\"}'),
('IP', 'ควบคุมการให้สารน้ำ', 'MED', 'asset_category', '{\"title_en\": \"Infusion pump\"}'),
('LO', 'โคมไฟผ่าตัด', 'MED', 'asset_category', '{\"title_en\": \"Lamp operation\"}'),
('EM', 'จักษุ', 'MED', 'asset_category', '{\"title_en\": \"Eye medical\"}'),
('CSSD', 'จ่ายกลาง', 'MED', 'asset_category', '{\"title_en\": \"Central Sterile Supply Department\"}'),
('CE', 'จี้ห้ามเลือดและตัดเนื้อเยื่อ', 'MED', 'asset_category', '{\"title_en\": \"Cauterization equipment\"}'),
('RS', 'ช่วยหายใจ', 'MED', 'asset_category', '{\"title_en\": \"Respiration\"}'),
('LAB', 'ชันสูตร', 'MED', 'asset_category', '{\"title_en\": \"Laboratory\"}'),
('FT', 'ตรวจทารกในครรภ์', 'MED', 'asset_category', '{\"title_en\": \"Fetus\"}'),
('HL', 'ตรวจรักษาหัวใจและปอด', 'MED', 'asset_category', '{\"title_en\": \"Heart Lung\"}'),
('NE', 'ตรวจวินิจฉัยและรักษาสมอง', 'MED', 'asset_category', '{\"title_en\": \"Neuro equipment\"}'),
('ME', 'ติดตามการทำงานของหัวใจและสัญญาณชีพ', 'MED', 'asset_category', '{\"title_en\": \"Monitor equipment\"}'),
('OB', 'เตียงผ่าตัด-คลอด', 'MED', 'asset_category', '{\"title_en\": \"Operation Bed\"}'),
('BP', 'เตียงผู้ป่วย', 'MED', 'asset_category', '{\"title_en\": \"Bed patient\"}'),
('CKD', 'ไตเทียม', 'MED', 'asset_category', '{\"title_en\": \"Chronic Kidney Disease\"}'),
('DE', 'ทันตกรรม', 'MED', 'asset_category', '{\"title_en\": \"Dental equipment\"}'),
('NB', 'ทารกแรกคลอด', 'MED', 'asset_category', '{\"title_en\": \"New born\"}'),
('OE', 'ผ่าตัด', 'MED', 'asset_category', '{\"title_en\": \"Operation equipment\"}'),
('PHR', 'เภสัชกรรม', 'MED', 'asset_category', '{\"title_en\": \"Pharmacy\"}'),
('RT', 'รังสีรักษา', 'MED', 'asset_category', '{\"title_en\": \"Radio therapy\"}'),
('AE', 'วิสัญญี', 'MED', 'asset_category', '{\"title_en\": \"Anesthesia equipment\"}'),
('ORT', 'ศัลยกรรมออร์โธปิดิกส์', 'MED', 'asset_category', '{\"title_en\": \"Orthopedic\"}'),
('URO', 'ศัลยศาสตร์ทางเดินปัสสาวะ', 'MED', 'asset_category', '{\"title_en\": \"Urology\"}'),
('MP', 'สนับสนุนการแพทย์', 'MED', 'asset_category', '{\"title_en\": \"Medical Support\"}'),
('ENT', 'หู คอ จมูก', 'MED', 'asset_category', '{\"title_en\": \"Ear Nose Throat\"}'),
('US', 'อัลตราซาวด์', 'MED', 'asset_category', '{\"title_en\": \"Ultrasound\"}'),
('6525-005-0005', 'เอกซเรย์', 'MED', 'asset_category', '{\"title_en\": \"Xray\"}'),
('EE', 'ไฟฟ้าและวิทยุ', 'ELE', 'asset_category', '{\"title_en\": \"Electric equipment\"}'),
('HT', 'ช่างซ่อมบำรุง', 'IND', 'asset_category', '{\"title_en\": \"Hand Tool\"}'),
('AC', 'การเกษตร', 'AGR', 'asset_category', '{\"title_en\": \"Agriculture\"}'),
('ED', 'การศึกษา', 'EDU', 'asset_category', '{\"title_en\": \"Education\"}'),
('CCTV', 'กล้องโทรทัศน์วงจรปิด', 'COM', 'asset_category', '{\"title_en\": \"Close Circuit Television\"}'),
('AP', 'โฆษณาและเผยแพร่', 'ADV', 'asset_category', '{\"title_en\": \"Advertise and publish\"}'),
('HK', 'งานบ้านงานครัว', 'HOM', 'asset_category', '{\"title_en\": \"Housework kitchen work\"}'),
('VM', 'ยานพาหนะบริการทางการแพทย์', 'VEH', 'asset_category', '{\"title_en\": \"Vehicle medical\"}'),
('VT', 'ยานพาหนะและขนส่ง', 'VEH', 'asset_category', '{\"title_en\": \"Vehicle transport\"}'),
('SC', 'วิทยาศาสตร์', 'SCI', 'asset_category', '{\"title_en\": \"Science\"}'),
('OFF', 'สำนักงาน', 'OFF', 'asset_category', '{\"title_en\": \"Office\"}'),
('AIR', 'เครื่องปรับอากาศและฟอกอากาศ', 'OFF', 'asset_category', '{\"title_en\": \"Air condition\"}'),
('DF', 'กระตุกไฟฟ้าหัวใจ', 'MED', 'asset_category', '{\"title_en\": \"Defibrillation\"}'),
('MC', 'กล้องจุลทรรศน์ในการผ่าตัด', 'MED', 'asset_category', '{\"title_en\": \"Microscopy\"}'),
('ES', 'กล้องส่องตรวจวินิจฉัยและรักษา', 'MED', 'asset_category', '{\"title_en\": \"Endoscopic examination\"}'),
('PT', 'กายภาพบำบัด', 'MED', 'asset_category', '{\"title_en\": \"Physical Therapy\"}'),
('BB', 'คลังเลือด', 'MED', 'asset_category', '{\"title_en\": \"Blood Bank\"}'),
('IP', 'ควบคุมการให้สารน้ำ', 'MED', 'asset_category', '{\"title_en\": \"Infusion pump\"}'),
('LO', 'โคมไฟผ่าตัด', 'MED', 'asset_category', '{\"title_en\": \"Lamp operation\"}'),
('EM', 'จักษุ', 'MED', 'asset_category', '{\"title_en\": \"Eye medical\"}'),
('CSSD', 'จ่ายกลาง', 'MED', 'asset_category', '{\"title_en\": \"Central Sterile Supply Department\"}'),
('CE', 'จี้ห้ามเลือดและตัดเนื้อเยื่อ', 'MED', 'asset_category', '{\"title_en\": \"Cauterization equipment\"}'),
('RS', 'ช่วยหายใจ', 'MED', 'asset_category', '{\"title_en\": \"Respiration\"}'),
('LAB', 'ชันสูตร', 'MED', 'asset_category', '{\"title_en\": \"Laboratory\"}'),
('FT', 'ตรวจทารกในครรภ์', 'MED', 'asset_category', '{\"title_en\": \"Fetus\"}'),
('HL', 'ตรวจรักษาหัวใจและปอด', 'MED', 'asset_category', '{\"title_en\": \"Heart Lung\"}'),
('NE', 'ตรวจวินิจฉัยและรักษาสมอง', 'MED', 'asset_category', '{\"title_en\": \"Neuro equipment\"}'),
('ME', 'ติดตามการทำงานของหัวใจและสัญญาณชีพ', 'MED', 'asset_category', '{\"title_en\": \"Monitor equipment\"}'),
('OB', 'เตียงผ่าตัด-คลอด', 'MED', 'asset_category', '{\"title_en\": \"Operation Bed\"}'),
('BP', 'เตียงผู้ป่วย', 'MED', 'asset_category', '{\"title_en\": \"Bed patient\"}'),
('CKD', 'ไตเทียม', 'MED', 'asset_category', '{\"title_en\": \"Chronic Kidney Disease\"}'),
('DE', 'ทันตกรรม', 'MED', 'asset_category', '{\"title_en\": \"Dental equipment\"}'),
('NB', 'ทารกแรกคลอด', 'MED', 'asset_category', '{\"title_en\": \"New born\"}'),
('OE', 'ผ่าตัด', 'MED', 'asset_category', '{\"title_en\": \"Operation equipment\"}'),
('PHR', 'เภสัชกรรม', 'MED', 'asset_category', '{\"title_en\": \"Pharmacy\"}'),
('RT', 'รังสีรักษา', 'MED', 'asset_category', '{\"title_en\": \"Radio therapy\"}'),
('AE', 'วิสัญญี', 'MED', 'asset_category', '{\"title_en\": \"Anesthesia equipment\"}'),
('ORT', 'ศัลยกรรมออร์โธปิดิกส์', 'MED', 'asset_category', '{\"title_en\": \"Orthopedic\"}'),
('URO', 'ศัลยศาสตร์ทางเดินปัสสาวะ', 'MED', 'asset_category', '{\"title_en\": \"Urology\"}'),
('MP', 'สนับสนุนการแพทย์', 'MED', 'asset_category', '{\"title_en\": \"Medical Support\"}'),
('ENT', 'หู คอ จมูก', 'MED', 'asset_category', '{\"title_en\": \"Ear Nose Throat\"}'),
('US', 'อัลตราซาวด์', 'MED', 'asset_category', '{\"title_en\": \"Ultrasound\"}'),
('XR', 'เอกซเรย์', 'MED', 'asset_category', '{\"title_en\": \"Xray\"}'),
('EE', 'ไฟฟ้าและวิทยุ', 'ELE', 'asset_category', '{\"title_en\": \"Electric equipment\"}'),
('HT', 'ช่างซ่อมบำรุง', 'IND', 'asset_category', '{\"title_en\": \"Hand Tool\"}'),
('AC', 'การเกษตร', 'AGR', 'asset_category', '{\"title_en\": \"Agriculture\"}'),
('ED', 'การศึกษา', 'EDU', 'asset_category', '{\"title_en\": \"Education\"}'),
('CCTV', 'กล้องโทรทัศน์วงจรปิด', 'COM', 'asset_category', '{\"title_en\": \"Close Circuit Television\"}'),
('AP', 'โฆษณาและเผยแพร่', 'ADV', 'asset_category', '{\"title_en\": \"Advertise and publish\"}'),
('HK', 'งานบ้านงานครัว', 'HOM', 'asset_category', '{\"title_en\": \"Housework kitchen work\"}'),
('SC', 'วิทยาศาสตร์', 'SCI', 'asset_category', '{\"title_en\": \"Science\"}'),
('OFF', 'สำนักงาน', 'OFF', 'asset_category', '{\"title_en\": \"Office\"}'),
('AIR', 'เครื่องปรับอากาศและฟอกอากาศ', 'OFF', 'asset_category', '{\"title_en\": \"Air condition\"}'),
('7440-001', 'โปรแกรมสำเร็จรูป', 'COM', 'asset_category', NULL),
('STR_GARAGE', 'โรงเก็บรถ', 'STR_GRP_TEMP', 'asset_category', '{}'),
('STR_SHELTER', 'ที่พักชั่วคราว', 'STR_GRP_TEMP', 'asset_category', '{}'),
('STR_NURSERY', 'เรือนเพาะชำ', 'STR_GRP_TEMP', 'asset_category', '{}'),
('STR_TEMP_OTHER', 'อื่น ๆ (รื้อถอนได้)', 'STR_GRP_TEMP', 'asset_category', '{\"allow_other_note\": true}'),
('STR_FENCE_CONCRETE', 'รั้วคอนกรีต / ผนังอิฐ', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_POOL', 'สระว่ายน้ำ', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_YARD_CONCRETE', 'ลานคอนกรีต', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_ROAD_CONCRETE', 'ถนนคอนกรีต', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_ROAD_ASPHALT', 'ถนนลาดยาง', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_BRIDGE_CONCRETE', 'สะพานคอนกรีตเสริมเหล็ก', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_DAM_EARTH', 'เขื่อนดิน', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_DAM_CONCRETE', 'เขื่อนปูน', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_RESERVOIR', 'อ่างเก็บน้ำ', 'STR_GRP_CONCRETE', 'asset_category', '{}'),
('STR_FENCE_BARBED', 'รั้วลวดหนาม', 'STR_GRP_WOOD', 'asset_category', '{}'),
('STR_FENCE_ZINC', 'รั้วสังกะสี', 'STR_GRP_WOOD', 'asset_category', '{}'),
('STR_FENCE_MESH', 'รั้วตาข่าย', 'STR_GRP_WOOD', 'asset_category', '{}'),
('STR_FENCE_WOOD', 'รั้วไม้', 'STR_GRP_WOOD', 'asset_category', '{}'),
('7440-026', 'เครื่องสแกนเนอร์', 'COM', 'asset_category', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
