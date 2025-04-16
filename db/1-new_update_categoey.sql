
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


เปลี่ยน datatype วันลา
ALTER TABLE `leave_entitlements` CHANGE `days` `days` FLOAT NOT NULL COMMENT 'วันที่ลาได้';

แก้ไขให้มีจุดทศนิยมได้
ALTER TABLE `leave_entitlements` CHANGE `balance` `balance` DOUBLE NOT NULL DEFAULT '0' COMMENT 'วันที่ลาพักผ่อนสะสม';
ALTER TABLE `leave_entitlements` 
MODIFY leave_on_year INT NOT NULL DEFAULT 0;


ีupdate order_status

UPDATE categorise SET code = CAST(code AS UNSIGNED) + 1 WHERE name = 'order_status' AND CAST(code AS UNSIGNED) >= 2;
INSERT INTO `categorise` (`id`, `ref`, `group_id`, `category_id`, `code`, `emp_id`, `name`, `title`, `qty`, `description`, `data_json`, `ma_items`, `active`)
VALUES (NULL, NULL, NULL, '', 2, NULL, 'order_status', 'ผอ.อนุมัติ', NULL, NULL, '{}', NULL, 1);
ALTER TABLE `helpdesk` ADD `category_id` INT NOT NULL DEFAULT '0' AFTER `id`;
ALTER TABLE `helpdesk` ADD `emp_id` INT NULL AFTER `category_id`;

-- แก้ approve label
UPDATE approve
SET data_json = JSON_SET(
    JSON_REMOVE(data_json, '$.topic'),
    '$.label', JSON_UNQUOTE(json_extract(data_json, '$.topic'))
) WHERE name = 'leave'

UPDATE approve
SET data_json = JSON_SET(data_json, '$.label', 'ตรวจสอบ')
WHERE name = 'leave' AND level = 3

UPDATE approve
SET data_json = JSON_SET(data_json, '$.label', 'เห็นชอบ')
WHERE name = 'leave' AND level = 2


## แก้ไข asset
ALTER TABLE `asset` ADD `deleted_at` DATETIME NULL DEFAULT NULL COMMENT 'วันที่ลบ' AFTER `updated_by`, ADD `deleted_by` INT NULL DEFAULT NULL COMMENT 'ผู้ลบ' AFTER `deleted_at`;

-- update qty stock 
ALTER TABLE `orders` CHANGE `qty` `qty` FLOAT NULL DEFAULT NULL COMMENT 'จำนวน';
ALTER TABLE `stock` CHANGE `qty` `qty` FLOAT NULL DEFAULT NULL COMMENT 'จำนวนสินค้าที่เคลื่อนย้าย';
ALTER TABLE `stock_events` CHANGE `qty` `qty` FLOAT NULL DEFAULT NULL COMMENT 'จำนวนสินค้าที่เคลื่อนย้าย';

SELECT title,code,MAX(CAST(SUBSTRING_INDEX(code, '-', -1) AS UNSIGNED)) AS max_value  FROM `categorise` WHERE `group_id` = 4 AND `name` LIKE 'asset_item'
GROUP BY category_id
ORDER BY `categorise`.`id` DESC;

-- update ตารางงานซ่อมบำรุง
UPDATE helpdesk h
LEFT JOIN employees e ON e.user_id = h.created_by
SET h.emp_id = e.id
WHERE h.emp_id IS NULL;


UPDATE `categorise` set name='committee' WHERE `name` LIKE 'board'
