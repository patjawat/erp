
--
-- Dumping data for table `supplies_type`
-- ประเภทของวัสดุ
--
DELETE FROM `categorise` WHERE `category_id` LIKE '4' AND `name` LIKE 'asset_type';
INSERT INTO `categorise` (`code`, `title`, `active`,`category_id`,`name`) VALUES
('M1', 'วัสดุสำนักงาน','1', 4,'asset_type'),
('M2', 'วัสดุไฟฟ้าและวิทยุ','1', 4,'asset_type'),
('M3', 'วัสดุงานบ้านงานครัว','1', 4,'asset_type'),
('M4', 'วัสดุก่อสร้างและประปา','1', 4,'asset_type'),
('M5', 'วัสดุยานพาหนะและขนส่ง','1', 4,'asset_type'),
('M6', 'วัสดุเชื้อเพลิงและหล่อลื่น','1', 4,'asset_type'),
('M7', 'วัสดุวิทยาศาสตร์หรือการแพทย์','1', 4,'asset_type'),
('M8', 'วัสดุการเกษตร','1', 4,'asset_type'),
('M9', 'วัสดุโฆษณาและเผยแพร่','1', 4,'asset_type'),
('M10', 'วัสดุเครื่องแต่งกาย','1', 4,'asset_type'),
('M11', 'วัสดุกีฬา','1', 4,'asset_type'),
('M12', 'วัสดุคอมพิวเตอร์','1', 4,'asset_type'),
('M13', 'วัสดุสนาม','1', 4,'asset_type'),
('M14', 'วัสดุการศึกษา','1', 4,'asset_type'),
('M15', 'วัสดุสำรวจ','1', 4,'asset_type'),
('M16', 'วัสดุอื่นๆ','1', 4,'asset_type'),
('M17', 'วัสดุแบบพิมพ์','1', 4,'asset_type'),
('M18', 'วัสดุบริโภค','1', 4,'asset_type'),
('M19', 'วัสดุทันตกรรม','1', 4,'asset_type'),
('M20', 'วัสดุวิทยาศาสตร์','1', 4,'asset_type'),
('M21', 'วัสดุรังสี','1', 4,'asset_type'),
('M22', 'วัสดุการแพทย์ทั่วไป','1', 4,'asset_type'),
('M23', 'ยา|เวชภัณฑ์','1', 4,'asset_type'),
('M24', 'วัสดุเภสัชกรรม','1', 4,'asset_type'),
('M25', 'จ้างเหมาอื่นๆ','1', 4,'asset_type');



ALTER TABLE `categorise` ADD `group_id` VARCHAR(255) NULL COMMENT 'กลุ่ม' AFTER `ref`;


UPDATE `categorise`SET name = 'asset_type' WHERE `name` LIKE 'product_type';

UPDATE categorise a 
LEFT JOIN categorise t ON t.code  = a.category_id AND t.name = 'asset_type'
SET a.group_id = t.category_id
WHERE a.name = 'asset_item'

