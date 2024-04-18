ALTER TABLE `asset` ADD `asset_status` VARCHAR(255) NULL COMMENT 'สถานะทรัพย?สิน' AFTER `budget_year`;
ALTER TABLE `asset` CHANGE `asset_status` `asset_status` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT 'สถานะทรัพย?สิน';
update `categorise` SET name="asset_status" WHERE `name` LIKE 'assetstatus';
INSERT INTO `categorise` (`id`, `ref`, `category_id`, `code`, `emp_id`, `name`, `title`, `description`, `data_json`, `active`) VALUES (NULL, NULL, NULL, '00', NULL, 'vendor', 'บริษัทตัวอย่างทดสอบ', NULL, NULL, '1');

-- INSERT INTO `categorise` (`code`, `name`, `title`, `active`) VALUES 
-- ('0','asset_status','จำหน่าย',1),
-- ('1','asset_status','ใช้งาน',1),
-- ('2','asset_status','ส่งซ่อม',1),
-- ('3','asset_status','ยืม',1);