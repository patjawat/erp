-- แก้ไขการเพิ่ม group_id ใน categorise
ALTER TABLE `categorise` ADD `group_id` VARCHAR(255) NULL COMMENT 'กลุ่ม' AFTER `ref`;


UPDATE `categorise`SET name = 'asset_type' WHERE `name` LIKE 'product_type'

UPDATE categorise a 
LEFT JOIN categorise t ON t.code  = a.category_id AND t.name = 'asset_type'
SET a.group_id = t.category_id
WHERE a.name = 'asset_item'

