INSERT INTO
    `categorise` (
        `ref`, `category_id`, `code`, `emp_id`, `name`, `title`, `description`, `data_json`, `active`
    )
VALUES (NULL, NULL, '4', NULL, 'asset_group', 'วัสดุ', NULL, NULL, 1),
    (NULL, NULL, '3', NULL, 'asset_group', 'ครุภัณฑ์', NULL, NULL, 1),
    (NULL, NULL, '1', NULL, 'asset_group', 'ที่ดิน', NULL, NULL, 1),
    (NULL, NULL, '2', NULL, 'asset_group', 'สิ่งปลูกสร้าง', NULL, NULL, 1),
    (NULL, '2', '4', NULL, 'asset_type', 'สิ่งปลูกสร้าง', NULL, '{\"group_name\": \"สิ่งปลูกสร้าง\", \"depreciation\": \"0\", \"service_life\": 0}', 1),
    (NULL, '3', '5', NULL, 'asset_type', 'ครุภัณฑ์สำนักงาน', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"33.33\", \"service_life\": 3}', 1),
    (NULL, '3', '6', NULL, 'asset_type', 'ครุภัณฑ์ยานพาหนะและขนส่ง', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"20.00\", \"service_life\": 5}', 1),
    (NULL, '3', '7', NULL, 'asset_type', 'ครุภัณฑ์ไฟฟ้าและวิทยุ ', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"20.00\", \"service_life\": 5}', 1),
    (NULL, '4', '9', NULL, 'asset_type', 'ครุภัณฑ์โฆษณาและเผยแพร่', NULL, '{\"group_name\": \"วัสดุ\", \"depreciation\": \"20.00\", \"service_life\": 5}', 1),
    (NULL, '3', '10', NULL, 'asset_type', 'ครุภัณฑ์การเกษตร เครื่องมือและอุปกรณ์', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"50.00\", \"service_life\": 2}', 1),
    (NULL, '3', '11', NULL, 'asset_type', 'ครุภัณฑ์การเกษตร เครื่องจักรกล', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"20.00\", \"service_life\": 5}', 1),
    (NULL, '3', '12', NULL, 'asset_type', 'ครุภัณฑ์โรงงาน เครื่องมือและอุปกรณ์', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"50.00\", \"service_life\": 2}', 1),
    (NULL, '4', '17', NULL, 'asset_type', 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', NULL, '{\"group_name\": \"วัสดุ\", \"depreciation\": \"20.00\", \"service_life\": 5}', 1),
    (NULL, '3', '18', NULL, 'asset_type', 'ครุภัณฑ์คอมพิวเตอร์', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"33.33\", \"service_life\": 3}', 1),
    (NULL, '3', '20', NULL, 'asset_type', 'ครุภัณฑ์งานบ้านงานครัว', NULL, '{\"group_name\": \"ครุภัณฑ์\", \"depreciation\": \"33.33\", \"service_life\": 3}', 1),
    (NULL, NULL, '5', NULL, 'asset_group', 'จ้างเหมา', NULL, NULL, 1),
    (NULL, NULL, '6', NULL, 'asset_group', 'อาหารสด', NULL, NULL, 1),
    (NULL, NULL, '1', NULL, 'budget_type', 'งบประมาณ', NULL, NULL, 1),
    (NULL, NULL, '2', NULL, 'budget_type', 'งบค่าเสื่อม', NULL, NULL, 1),
    (NULL, NULL, '3', NULL, 'budget_type', 'เงินบริจาค', NULL, NULL, 1),
    (NULL, NULL, '4', NULL, 'budget_type', 'เงินบำรุง', NULL, NULL, 1),
    (NULL, NULL, '5', NULL, 'budget_type', 'เงิน อปท.', NULL, NULL, 1),
    (NULL, NULL, '6', NULL, 'budget_type', 'เงิน UC', NULL, NULL, 1),
    (NULL, NULL, '7', NULL, 'budget_type', 'เงินอื่นๆ', NULL, NULL, 1),
    (NULL, NULL, '8', NULL, 'budget_type', 'เงินค่าบริการทางการแพทย์ที่เบิกจ่ายในลักษณะงบลงทุน', NULL, NULL, 1),
    (NULL, NULL, '1', NULL, 'asset_status', 'ปกติ', NULL, NULL, 1),
    (NULL, NULL, '2', NULL, 'asset_status', 'จำหน่ายแล้ว', NULL, NULL, 1),
    (NULL, NULL, '3', NULL, 'asset_status', 'รอจำหน่าย', NULL, NULL, 1),
    (NULL, NULL, '4', NULL, 'asset_status', 'ถูกยืม', NULL, NULL, 1
    );