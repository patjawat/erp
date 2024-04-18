ALTER TABLE `employees` CHANGE `position_group` `position_group` VARCHAR(100) NULL DEFAULT NULL COMMENT 'ประเภท/กลุ่มงาน';
ALTER TABLE `employees` CHANGE `position_name` `position_name` VARCHAR(100) NULL DEFAULT NULL COMMENT 'ตำแหน่ง';
ALTER TABLE `employees` CHANGE `position_type` `position_type` VARCHAR(100) NULL DEFAULT NULL COMMENT 'ตำแหน่ง';
ALTER TABLE `employees` CHANGE `position_level` `position_level` VARCHAR(100) NULL DEFAULT NULL COMMENT 'ตำแหน่ง';
ALTER TABLE `employees` CHANGE `position_number` `position_number` VARCHAR(100) NULL DEFAULT NULL COMMENT 'ตำแหน่ง';
