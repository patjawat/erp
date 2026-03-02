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
('chronic_disease', 'DM',     'DM (เบาหวาน)',         1),
('chronic_disease', 'HT',     'HT (ความดันโลหิตสูง)', 1),
('chronic_disease', 'DLP',    'DLP (ไขมันในเลือด)',   1),
('chronic_disease', 'Heart',  'โรคหัวใจ',             1),
('chronic_disease', 'Kidney', 'โรคไต',                1),
('chronic_disease', 'other',  'อื่นๆ',                1);
