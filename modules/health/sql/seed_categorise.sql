-- seed ข้อมูลสุขภาพลงตาราง categorise ที่มีอยู่แล้วในระบบ
-- name = ชื่อการบันทึก | code = รหัสโรค | title = ชื่อโรค | active = 1

-- โรคในครอบครัว (name = 'family_disease')
INSERT IGNORE INTO `categorise` (`name`, `code`, `title`, `active`) VALUES
('family_disease', 'diabetes',     'เบาหวาน',      1),
('family_disease', 'hypertension', 'ความดันสูง',    1),
('family_disease', 'gout',         'เก๊าท์',        1),
('family_disease', 'kidney',       'ไตวาย',        1),
('family_disease', 'heart',        'หัวใจ',        1),
('family_disease', 'stroke',       'อัมพาต',       1),
('family_disease', 'emphysema',    'ถุงลมโป่งพอง', 1),
('family_disease', 'unknown',      'ไม่ทราบ',      1);

-- โรคประจำตัว (name = 'chronic_disease')
INSERT IGNORE INTO `categorise` (`name`, `code`, `title`, `active`) VALUES
('chronic_disease', 'h_diabetes',     'เบาหวาน',              1),
('chronic_disease', 'h_hypertension', 'ความดันสูง',            1),
('chronic_disease', 'h_liver',        'โรคตับ',                1),
('chronic_disease', 'h_stroke',       'อัมพาต',                1),
('chronic_disease', 'h_heart',        'โรคหัวใจ',              1),
('chronic_disease', 'h_dyslipidemia', 'ไขมันเลือดผิดปกติ',     1),
('chronic_disease', 'h_gastric',      'แผลในกระเพาะ',          1),
('chronic_disease', 'h_birth',        'คลอดบุตร > 4kg',        1),
('chronic_disease', 'h_thirst',       'ดื่มน้ำบ่อย',           1),
('chronic_disease', 'h_nocturia',     'ปัสสาวะบ่อยกลางคืน',   1),
('chronic_disease', 'h_fatigue',      'อ่อนเพลีย',             1),
('chronic_disease', 'h_skin_itch',    'คันตามผิวหนัง',         1),
('chronic_disease', 'h_vision',       'ตาพร่ามัว',             1),
('chronic_disease', 'h_numbness',     'ชาปลายมือเท้า',         1),
('chronic_disease', 'h_constipation', 'ท้องผูกเรื้อรัง',       1),
('chronic_disease', 'h_urinary',      'ฉี่ขัด/ปนเลือด',        1);
