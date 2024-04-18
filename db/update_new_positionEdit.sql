CREATE TABLE categorise_17_04_2024_backup AS SELECT * FROM categorise;
DELETE FROM `categorise` WHERE `name` = 'position_name';
INSERT INTO `categorise` (`category_id`, `code`,`emp_id`,`name`,`title`,`description`,`data_json`,`active`) VALUES
-- INSERT INTO
--     `categorise`
-- VALUES 
        (
                'PT1PG1', 'PT1PG1PN1', NULL, 'position_name', 'นักบริหาร', NULL, '{\"code\": \"1-1-2001\", \"level\": \"ต้น - สูง\", \"title_name\": \"บริหาร\"}', 1
        ),
        (
                'PT1PG1', 'PT1PG1PN2', NULL, 'position_name', 'ผู้ตรวจราชการกระทรวง', NULL, '{\"code\": \"1-1-2004\", \"level\": \"สูง\", \"title_name\": \"ตรวจราชการกระทรวง\"}', 1
        ),
        (
                'PT1PG2', 'PT1PG2PN1', NULL, 'position_name', 'ผู้อำนวยการ', NULL, '{\"code\": \"2-1-2001\", \"level\": \"ต้น - สูง\", \"title_name\": \"อำนวยการ\"}', 1
        ),
        (
                'PT1PG2', 'PT1PG2PN2', NULL, 'position_name', 'ผู้อำนวยการเฉพาะด้าน (ระบุชื่อสายงาน)', NULL, '{\"code\": \"2-1-2002\", \"level\": \"ต้น - สูง\", \"title_name\": \"อำนวยการเฉพาะด้าน\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN1', NULL, 'position_name', 'นักจัดการงานทั่วไป', NULL, '{\"code\": \"3-1-2004\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"จัดการงานทั่วไป\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN2', NULL, 'position_name', 'นักทรัพยากรบุคคล', NULL, '{\"code\": \"3-1-2006\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"ทรัพยากรบุคคล\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN3', NULL, 'position_name', 'นิติกร', NULL, '{\"code\": \"3-1-2008\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"นิติการ\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN4', NULL, 'position_name', 'นักวิเคราะห์นโยบายและแผน', NULL, '{\"code\": \"3-1-2012\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"นักวิเคราะห์นโยบายและแผน\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN5', NULL, 'position_name', 'นักวิชาการคอมพิวเตอร์', NULL, '{\"code\": \"3-1-2013\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"วิชาการคอมพิวเตอร์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN6', NULL, 'position_name', 'นักเทคโนโลยีสารสนเทศ', NULL, '{\"code\": \"3-1-2015\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"วิชาการเทคโนโลยีสารสนเทศ\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN7', NULL, 'position_name', 'นักวิชาการพัสดุ', NULL, '{\"code\": \"3-1-2016\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"วิชาการพัสดุ\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN8', NULL, 'position_name', 'นักวิชาการสถิติ', NULL, '{\"code\": \"3-1-2019\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"วิชาการสถิติ\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN9', NULL, 'position_name', 'นักวิเทศสัมพันธ์', NULL, '{\"code\": \"3-1-2021\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"วิเทศสัมพันธ์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN10', NULL, 'position_name', 'นักวิชาการเงินและบัญชี', NULL, '{\"code\": \"3-2-2006\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"วิชาการเงินและบัญชี\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN11', NULL, 'position_name', 'นักวิชาการตรวจสอบภายใน', NULL, '{\"code\": \"3-2-2009\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"วิชาการตรวจสอบภายใน\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN12', NULL, 'position_name', 'นักประชาสัมพันธ์', NULL, '{\"code\": \"3-3-2005\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"ประชาสัมพันธ์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN13', NULL, 'position_name', 'นักวิชาการเผยแพร่', NULL, '{\"code\": \"3-3-2007\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"วิชาการเผยแพร่\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN14', NULL, 'position_name', 'นักวิชาการโสตทัศนศึกษา', NULL, '{\"code\": \"3-3-2008\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"วิชาการโสตทัศนศึกษา\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN15', NULL, 'position_name', 'นักกายภาพบำบัด', NULL, '{\"code\": \"3-6-2001\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"กายภาพบำบัด\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN16', NULL, 'position_name', 'นักกิจกรรมบำบัด', NULL, '{\"code\": \"3-6-2002\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"กิจกรรมบำบัด\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN17', NULL, 'position_name', 'นักจิตวิทยา', NULL, '{\"code\": \"3-6-2003\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"จิตวิทยา\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN18', NULL, 'position_name', 'นักจิตวิทยาคลินิก', NULL, '{\"code\": \"3-6-2004\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"จิตวิทยาคลีนิก\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN19', NULL, 'position_name', 'ทันตแพทย์', NULL, '{\"code\": \"3-6-2005\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"ทันตแพทย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN20', NULL, 'position_name', 'นักเทคนิคการแพทย์', NULL, '{\"code\": \"3-6-2006\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"เทคนิคการแพทย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN21', NULL, 'position_name', 'พยาบาลวิชาชีพ', NULL, '{\"code\": \"3-6-2008\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"พยาบาลวิชาชีพ\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN22', NULL, 'position_name', 'นายแพทย์', NULL, '{\"code\": \"3-6-2009\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"แพทย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN23', NULL, 'position_name', 'แพทย์แผนไทย', NULL, '{\"code\": \"3-6-2010\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"แพทย์แผนไทย\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN24', NULL, 'position_name', 'เภสัชกร', NULL, '{\"code\": \"3-6-2011\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"เภสัชกรรม\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN25', NULL, 'position_name', 'นักโภชนาการ', NULL, '{\"code\": \"3-6-2012\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"โภชนาการ\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN26', NULL, 'position_name', 'นักรังสีการแพทย์', NULL, '{\"code\": \"3-6-2013\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"รังสีการแพทย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN27', NULL, 'position_name', 'นักวิชาการพยาบาล', NULL, '{\"code\": \"3-6-2014\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"วิชาการพยาบาล\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN28', NULL, 'position_name', 'นักวิชาการสาธารณสุข', NULL, '{\"code\": \"3-6-2015\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"วิชาการสาธารณสุข\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN29', NULL, 'position_name', 'นักวิชาการอาหารและยา', NULL, '{\"code\": \"3-6-2017\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"วิชาการอาหารและยา\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN30', NULL, 'position_name', 'นักวิทยาศาสตร์การแพทย์', NULL, '{\"code\": \"3-6-2018\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"วิทยาศาสตร์การแพทย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN31', NULL, 'position_name', 'นักเวชศาสตร์การสื่อความหมาย', NULL, '{\"code\": \"3-6-2019\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"เวชศาสตร์การสื่อความหมาย\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN32', NULL, 'position_name', 'นักเทคโนโลยีหัวใจและทรวงอก', NULL, '{\"code\": \"3-6-2020\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"เทคโนโลยีหัวใจและทรวงอก\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN33', NULL, 'position_name', 'นักสาธารณสุข', NULL, '{\"code\": \"3-6-2022\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"สาธารณสุข\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN34', NULL, 'position_name', 'นักกายอุปกรณ์', NULL, '{\"code\": \"3-7-2001\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"กายอุปกรณ์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN35', NULL, 'position_name', 'ช่างภาพการแพทย์', NULL, '{\"code\": \"3-7-2004\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"ช่างภาพการแพทย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN36', NULL, 'position_name', 'บรรณารักษ์', NULL, '{\"code\": \"3-8-2003\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"บรรณารักษ์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN37', NULL, 'position_name', 'นักวิชาการศึกษา', NULL, '{\"code\": \"3-8-2021\", \"level\": \"ปฏิบัติการ - ทรงคุณวุฒิ\", \"title_name\": \"วิชาการศึกษา\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN38', NULL, 'position_name', 'วิทยาจารย์', NULL, '{\"code\": \"3-8-2025\", \"level\": \"ปฏิบัติการ - ชำนาญการพิเศษ\", \"title_name\": \"วิทยาจารย์\"}', 1
        ),
        (
                'PT1PG3', 'PT1PG3PN39', NULL, 'position_name', 'นักสังคมสงเคราะห์', NULL, '{\"code\": \"3-8-2026\", \"level\": \"ปฏิบัติการ - เชี่ยวชาญ\", \"title_name\": \"สังคมสงเคราะห์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN1', NULL, 'position_name', 'เจ้าพนักงานธุรการ', NULL, '{\"code\": \"4-1-2001\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานธุรการ\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN2', NULL, 'position_name', 'เจ้าพนักงานพัสดุ', NULL, '{\"code\": \"4-1-2002\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานพัสดุ\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN3', NULL, 'position_name', 'เจ้าพนักงานเวชสถิติ', NULL, '{\"code\": \"4-1-2004\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"เจ้าพนักงานเวชสถิติ\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN4', NULL, 'position_name', 'เจ้าพนักงานสถิติ', NULL, '{\"code\": \"4-1-2005\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานสถิติ\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN5', NULL, 'position_name', 'เจ้าพนักงานการเงินและบัญชี', NULL, '{\"code\": \"4-2-2002\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานการเงินและบัญชี\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN6', NULL, 'position_name', 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', NULL, '{\"code\": \"4-3-2004\", \"level\": \"ปฏิบัติงาน - ชำนาญงาน\", \"title_name\": \"ปฏิบัติงานเผยแพร่ประชาสัมพันธ์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN7', NULL, 'position_name', 'เจ้าพนักงานโสตทัศนศึกษา', NULL, '{\"code\": \"4-3-2007\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานโสตทัศนศึกษา\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN8', NULL, 'position_name', 'เจ้าพนักงานทันตสาธารณสุข', NULL, '{\"code\": \"4-6-2001\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานทันตสาธารณสุข\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN9', NULL, 'position_name', 'เจ้าพนักงานเภสัชกรรม', NULL, '{\"code\": \"4-6-2002\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานเภสัชกรรม\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN10', NULL, 'position_name', 'โภชนากร', NULL, '{\"code\": \"4-6-2003\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"โภชนาการ\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN11', NULL, 'position_name', 'เจ้าพนักงานรังสีการแพทย์', NULL, '{\"code\": \"4-6-2004\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานรังสีการแพทย์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN12', NULL, 'position_name', 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', NULL, '{\"code\": \"4-6-2005\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานวิทยาศาสตร์การแพทย์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN13', NULL, 'position_name', 'เจ้าพนักงานเวชกรรมฟื้นฟู', NULL, '{\"code\": \"4-6-2006\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานเวชกรรมฟื้นฟู\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN14', NULL, 'position_name', 'เจ้าพนักงานสาธารณสุข', NULL, '{\"code\": \"4-6-2007\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานสาธารณสุข\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN15', NULL, 'position_name', 'เจ้าพนักงานอาชีวบำบัด', NULL, '{\"code\": \"4-6-2008\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานอาชีวบำบัด\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN16', NULL, 'position_name', 'พยาบาลเทคนิค', NULL, '{\"code\": \"4-6-2009\", \"level\": \"ปฏิบัติงาน - ชำนาญงาน\", \"title_name\": \"พยาบาลเทคนิค\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN17', NULL, 'position_name', 'นายช่างศิลป์', NULL, '{\"code\": \"4-7-2003\", \"level\": \"ปฏิบัติงาน - ชำนาญงาน\", \"title_name\": \"ปฏิบัติงานช่างศิลป์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN18', NULL, 'position_name', 'ช่างกายอุปกรณ์', NULL, '{\"code\": \"4-7-2006\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานกายอุปกรณ์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN19', NULL, 'position_name', 'เจ้าพนักงานเครื่องคอมพิวเตอร์', NULL, '{\"code\": \"4-7-2007\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานเครื่องคอมพิวเตอร์\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN20', NULL, 'position_name', 'ช่างทันตกรรม', NULL, '{\"code\": \"4-7-2012\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานช่างทันตกรรม\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN21', NULL, 'position_name', 'นายช่างเทคนิค', NULL, '{\"code\": \"4-7-2013\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานช่างเทคนิค\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN22', NULL, 'position_name', 'นายช่างไฟฟ้า', NULL, '{\"code\": \"4-7-2014\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานช่างไฟฟ้า\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN23', NULL, 'position_name', 'นายช่างโยธา', NULL, '{\"code\": \"4-7-2016\", \"level\": \"ปฏิบัติงาน - อาวุโส\", \"title_name\": \"ปฏิบัติงานโยธา\"}', 1
        ),
        (
                'PT1PG4', 'PT1PG4PN24', NULL, 'position_name', 'เจ้าพนักงานห้องสมุด', NULL, '{\"code\": \"4-8-2015\", \"level\": \"ปฏิบัติงาน - ชำนาญงาน\", \"title_name\": \"ปฏิบัติงานห้องสมุด\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN1', NULL, 'position_name', 'พนักงานช่วยเหลือคนไข้', NULL, '{\"title_name\": \"ปฏิบัติงานช่วยเหลือคนไข้\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN2', NULL, 'position_name', 'ผู้ช่วยพยาบาล', NULL, '{\"title_name\": \"ผู้ช่วยพยาบาล\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN3', NULL, 'position_name', 'ผู้ช่วยทันตแพทย์', NULL, '{\"title_name\": \"ผู้ช่วยทันตแพทย์\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN4', NULL, 'position_name', 'เจ้าพนักงานธุรการ', NULL, '{\"title_name\": \"ปฏิบัติงานธุรการ\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN5', NULL, 'position_name', 'เจ้าพนักงานพัสดุ', NULL, '{\"title_name\": \"ปฏิบัติงานพัสดุ\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN6', NULL, 'position_name', 'เจ้าพนักงานสถิติ', NULL, '{\"title_name\": \"ปฏิบัติงานสถิติ\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN7', NULL, 'position_name', 'เจ้าพนักงานการเงินและบัญชี', NULL, '{\"title_name\": \"ปฏิบัติงานการเงินและบัญชี\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN8', NULL, 'position_name', 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', NULL, '{\"title_name\": \"ปฏิบัติงานเผยแพร่ประชาสัมพันธ์\"}', 1
        ),
        (
                'PT2PG1', 'PT2PG1PN9', NULL, 'position_name', 'เจ้าพนักงานห้องสมุด', NULL, '{\"title_name\": \"ปฏิบัติงานห้องสมุด\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN1', NULL, 'position_name', 'เจ้าพนักงานโสตทัศนศึกษา', NULL, '{\"title_name\": \"ปฏิบัติงานโสตทัศนศึกษา\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN2', NULL, 'position_name', 'นายช่างเทคนิค', NULL, '{\"title_name\": \"ปฏิบัติงานช่างเทคนิค\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN3', NULL, 'position_name', 'เจ้าพนักงานเครื่องคอมพิวเตอร์', NULL, '{\"title_name\": \"ปฏิบัติงานเครื่องคอมพิวเตอร์\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN4', NULL, 'position_name', 'นายช่างไฟฟ้า', NULL, '{\"title_name\": \"ปฏิบัติงานช่างไฟฟ้า\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN5', NULL, 'position_name', 'นายช่างโยธา', NULL, '{\"title_name\": \"ปฏิบัติงานช่างโยธา\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN6', NULL, 'position_name', 'เจ้าพนักงานอาชีวบำบัด', NULL, '{\"title_name\": \"ปฏิบัติงานอาชีวบำบัด\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN7', NULL, 'position_name', 'เจ้าพนักงานเวชสถิติ', NULL, '{\"title_name\": \"ปฏิบัติงานเวชสถิติ\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN8', NULL, 'position_name', 'เจ้าพนักงานทันตสาธารณสุข', NULL, '{\"title_name\": \"ปฏิบัติงานทันตสาธารณสุข\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN9', NULL, 'position_name', 'เจ้าพนักงานเภสัชกรรม', NULL, '{\"title_name\": \"ปฏิบัติงานเภสัชกรรม\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN10', NULL, 'position_name', 'โภชนากร', NULL, '{\"title_name\": \"ปฏิบัติงานโภชนากร\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN11', NULL, 'position_name', 'เจ้าพนักงานรังสีการแพทย์', NULL, '{\"title_name\": \"ปฏิบัติงานรังสีการแพทย์\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN12', NULL, 'position_name', 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', NULL, '{\"title_name\": \"ปฏิบัติงานวิทยาศาสตร์การแพทย์\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN13', NULL, 'position_name', 'เจ้าพนักงานเวชกรรมฟื้นฟู', NULL, '{\"title_name\": \"ปฏิบัติงานเวชกรรมฟื้นฟู\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN14', NULL, 'position_name', 'เจ้าพนักงานสาธารณสุข', NULL, '{\"title_name\": \"ปฏิบัติงานสาธารณสุข\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN15', NULL, 'position_name', 'นายช่างศิลป์', NULL, '{\"title_name\": \"ปฏิบัติงานช่างศิลป์\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN16', NULL, 'position_name', 'ช่างกายอุปกรณ์', NULL, '{\"title_name\": \"ปฏิบัติงานช่างกายอุปกรณ์\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN17', NULL, 'position_name', 'ช่างทันตกรรม', NULL, '{\"title_name\": \"ปฏิบัติงานช่างทันตกรรม\"}', 1
        ),
        (
                'PT2PG2', 'PT2PG2PN18', NULL, 'position_name', 'พยาบาลเทคนิค', NULL, '{\"title_name\": \"ปฏิบัติงานพยาบาลเทคนิค\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN1', NULL, 'position_name', 'นักจัดการงานทั่วไป', NULL, '{\"title_name\": \"จัดการงานทั่วไป\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN2', NULL, 'position_name', 'นักทรัพยากรบุคคล', NULL, '{\"title_name\": \"ทรัพยากรบุคคล\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN3', NULL, 'position_name', 'นักประชาสัมพันธ์', NULL, '{\"title_name\": \"ประชาสัมพันธ์\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN4', NULL, 'position_name', 'นักวิชาการเผยแพร่', NULL, '{\"title_name\": \"วิชาการเผยแพร่\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN5', NULL, 'position_name', 'นักวิเคราะห์นโยบายและแผน', NULL, '{\"title_name\": \"วิเคราะห์นโยบายและแผน\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN6', NULL, 'position_name', 'นักวิชาการพัสดุ', NULL, '{\"title_name\": \"วิชาการพัสดุ\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN7', NULL, 'position_name', 'นักวิชาการศึกษา', NULL, '{\"title_name\": \"วิชาการศึกษา\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN8', NULL, 'position_name', 'นักวิชาการตรวจสอบภายใน', NULL, '{\"title_name\": \"วิชาการตรวจสอบภายใน\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN9', NULL, 'position_name', 'นักวิทยาศาสตร์การแพทย์', NULL, '{\"title_name\": \"วิทยาศาสตร์การแพทย์\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN10', NULL, 'position_name', 'นักสังคมสงเคราะห์', NULL, '{\"title_name\": \"สังคมสงเคราะห์\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN11', NULL, 'position_name', 'นักอาชีวบำบัด', NULL, '{\"title_name\": \"อาชีวบำบัด\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN12', NULL, 'position_name', 'นักวิชาการสาธารณสุข', NULL, '{\"title_name\": \"วิชาการสาธารณสุข\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN13', NULL, 'position_name', 'นักโภชนาการ', NULL, '{\"title_name\": \"โภชนาการ\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN14', NULL, 'position_name', 'นักวิชาการเงินและบัญชี', NULL, '{\"title_name\": \"วิชาการเงินและบัญชี\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN15', NULL, 'position_name', 'นักวิชาการโสตทัศนศึกษา', NULL, '{\"title_name\": \"วิชาการโสตทัศนศึกษา\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN16', NULL, 'position_name', 'ช่างภาพการแพทย์', NULL, '{\"title_name\": \"ช่างภาพการแพทย์\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN17', NULL, 'position_name', 'บรรณารักษ์', NULL, '{\"title_name\": \"บรรณารักษ์\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN18', NULL, 'position_name', 'นิติกร', NULL, '{\"title_name\": \"นิติการ\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN19', NULL, 'position_name', 'วิชาการสถิติ', NULL, '{\"title_name\": \"วิชาการสถิติ\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN20', NULL, 'position_name', 'นักจิตวิทยา', NULL, '{\"title_name\": \"จิตวิทยา\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN21', NULL, 'position_name', 'เศรษฐกร', NULL, '{\"title_name\": \"เศรษฐกร\"}', 1
        ),
        (
                'PT2PG3', 'PT2PG3PN22', NULL, 'position_name', 'นักวิเทศสัมพันธ์', NULL, '{\"title_name\": \"วิเทศสัมพันธ์\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN1', NULL, 'position_name', 'นักกายอุปกรณ์', NULL, '{\"title_name\": \"กายอุปกรณ์\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN2', NULL, 'position_name', 'นักกายภาพบำบัด', NULL, '{\"title_name\": \"กายภาพบำบัด\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN3', NULL, 'position_name', 'นักกิจกรรมบำบัด', NULL, '{\"title_name\": \"กิจกรรมบำบัด\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN4', NULL, 'position_name', 'แพทย์แผนไทย', NULL, '{\"title_name\": \"การแพทย์แผนไทย\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN5', NULL, 'position_name', 'นักจิตวิทยาคลินิก', NULL, '{\"title_name\": \"จิตวิทยาคลินิก\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN6', NULL, 'position_name', 'นักเทคนิคการแพทย์', NULL, '{\"title_name\": \"เทคนิคการแพทย์\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN7', NULL, 'position_name', 'นักรังสีการแพทย์', NULL, '{\"title_name\": \"รังสีการแพทย์\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN8', NULL, 'position_name', 'นักวิชาการคอมพิวเตอร์', NULL, '{\"title_name\": \"วิชาการคอมพิวเตอร์\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN9', NULL, 'position_name', 'นักเวชศาสตร์การสื่อความหมาย', NULL, '{\"title_name\": \"เวชศาสตร์การสื่อความหมาย\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN10', NULL, 'position_name', 'พยาบาลวิชาชีพ', NULL, '{\"title_name\": \"พยาบาลวิชาชีพ\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN11', NULL, 'position_name', 'เภสัชกร', NULL, '{\"title_name\": \"เภสัชกรรม\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN12', NULL, 'position_name', 'นักเทคโนโลยีหัวใจและทรวงอก', NULL, '{\"title_name\": \"เทคโนโลยีหัวใจและทรวงอก\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN13', NULL, 'position_name', 'วิศวกรไฟฟ้า', NULL, '{\"title_name\": \"วิศวกรไฟฟ้า\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN14', NULL, 'position_name', 'วิศวกรโยธา', NULL, '{\"title_name\": \"วิศวกรโยธา\"}', 1
        ),
        (
                'PT2PG4', 'PT2PG4PN15', NULL, 'position_name', 'วิศวกรเครื่องกล', NULL, '{\"title_name\": \"วิศวกรเครื่องกล\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN1', NULL, 'position_name', 'นักกายภาพบำบัด', NULL, '{\"code\": \"3-6-2001\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN2', NULL, 'position_name', 'นักกิจกรรมบำบัด', NULL, '{\"code\": \"3-6-2002\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN3', NULL, 'position_name', 'นักจิตวิทยาคลินิก', NULL, '{\"code\": \"3-6-2004\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN4', NULL, 'position_name', 'ทันตแพทย์', NULL, '{\"code\": \"3-6-2005\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN5', NULL, 'position_name', 'นักเทคนิคการแพทย', NULL, '{\"code\": \"3-6-2006\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN6', NULL, 'position_name', 'นายสัตวแพทย', NULL, '{\"code\": \"3-6-2007\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN7', NULL, 'position_name', 'พยาบาลวิชาชีพ', NULL, '{\"code\": \"3-6-2008\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN8', NULL, 'position_name', 'นายแพทย์', NULL, '{\"code\": \"3-6-2009\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN9', NULL, 'position_name', 'แพทย์แผนไทย', NULL, '{\"code\": \"3-6-2010\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN10', NULL, 'position_name', 'เภสัชกร', NULL, '{\"code\": \"3-6-2011\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN11', NULL, 'position_name', 'นักรังสีการแพทย', NULL, '{\"code\": \"3-6-2013\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN12', NULL, 'position_name', 'นักเวชศาสตร์การสื่อความหมาย', NULL, '{\"code\": \"3-6-2019\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN13', NULL, 'position_name', 'นักเทคโนโลยีหัวใจและทรวงอก', NULL, '{\"code\": \"3-6-2020\", \"note\": \"นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN14', NULL, 'position_name', 'นักฟิสิกส์การแพทย', NULL, '{\"code\": \"3-6-2021\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN15', NULL, 'position_name', 'นักทัศนมาตร', NULL, '{\"code\": \"3-6-2022\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN16', NULL, 'position_name', 'นักกายอุปกรณ', NULL, '{\"code\": \"3-7-2001\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG1PN17', NULL, 'position_name', 'วิศวกรไฟฟ้า', NULL, '{\"code\": \"3-7-2020\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN1', NULL, 'position_name', 'นักวิชาการศึกษาพิเศษ', NULL, '{\"code\": \"3-8-2022\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN2', NULL, 'position_name', 'นักฟิสิกส์รังสี', NULL, '{\"code\": \"3-5-2007\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN3', NULL, 'position_name', 'นักวิทยาศาสตร์', NULL, '{\"code\": \"3-5-2010\", \"note\": \"นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN4', NULL, 'position_name', 'นักจิตวิทยา', NULL, '{\"code\": \"3-6-2003\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN5', NULL, 'position_name', 'นักโภชนาการ', NULL, '{\"code\": \"3-6-2012\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN6', NULL, 'position_name', 'นักวิชาการสาธารณสุข', NULL, '{\"code\": \"3-6-2015\", \"note\": \"นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN7', NULL, 'position_name', 'นักอาชีวบำบัด', NULL, '{\"code\": \"3-6-2016\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN8', NULL, 'position_name', 'นักวิชาการอาหารและยา', NULL, '{\"code\": \"3-6-2017\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN9', NULL, 'position_name', 'นักวิทยาศาสตร์การแพทย์', NULL, '{\"code\": \"3-6-2018\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN10', NULL, 'position_name', 'ช่างภาพการแพทย์', NULL, '{\"code\": \"3-7-2004\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG2PN11', NULL, 'position_name', 'นักสังคมสงเคราะห์', NULL, '{\"code\": \"3-8-2026\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG3', 'PT3PG3PN1', NULL, 'position_name', 'นักวิชาการคอมพิวเตอร์', NULL, '{\"code\": \"3-1-2013\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN1', NULL, 'position_name', 'นักจัดการงานทั่วไป', NULL, '{\"code\": \"3-1-2004\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN2', NULL, 'position_name', 'นักทรัพยากรบุคคล', NULL, '{\"code\": \"3-1-2006\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN3', NULL, 'position_name', 'นิติกร', NULL, '{\"code\": \"3-1-2008\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN4', NULL, 'position_name', 'นักวิเคราะห์นโยบายและแผน', NULL, '{\"code\": \"3-1-2012\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN5', NULL, 'position_name', 'นักเทคโนโลยีสารสนเทศ', NULL, '{\"code\": \"3-1-2015\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN6', NULL, 'position_name', 'นักวิชาการพัสดุ', NULL, '{\"code\": \"3-1-2016\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN7', NULL, 'position_name', 'นักวิชาการสถิติ', NULL, '{\"code\": \"3-1-2019\", \"note\": \"นักวิชาการสถิติ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN8', NULL, 'position_name', 'นักวิเทศสัมพันธ์', NULL, '{\"code\": \"3-1-2021\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN9', NULL, 'position_name', 'นักวิชาการเงินและบัญชี', NULL, '{\"code\": \"3-2-2006\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN10', NULL, 'position_name', 'นักวิชาการตรวจสอบภายใน', NULL, '{\"code\": \"3-2-2009\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN11', NULL, 'position_name', 'นักประชาสัมพันธ์', NULL, '{\"code\": \"3-3-2005\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN12', NULL, 'position_name', 'นักวิชาการเผยแพร่', NULL, '{\"code\": \"3-3-2007\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN13', NULL, 'position_name', 'นักวิชาการโสตทัศนศึกษา', NULL, '{\"code\": \"3-3-2008\", \"note\": \"นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN14', NULL, 'position_name', 'นักวิชาการเกษตร', NULL, '{\"code\": \"3-4-2001\", \"note\": \"นักวิชาการเกษตร เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN15', NULL, 'position_name', 'วิศวกร', NULL, '{\"code\": \"3-7-2015\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN16', NULL, 'position_name', 'บรรณารักษ์', NULL, '{\"code\": \"3-8-2003\", \"note\": \"บรรณารักษ์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN17', NULL, 'position_name', 'นักวิชาการศึกษา', NULL, '{\"code\": \"3-8-2021\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG4', 'PT3PG4PN18', NULL, 'position_name', 'วิทยาจารย์', NULL, '{\"code\": \"3-8-2025\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN1', NULL, 'position_name', 'เจ้าพนักงานธุรการ', NULL, '{\"code\": \"4-1-2001\", \"note\": \"เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN2', NULL, 'position_name', 'เจ้าพนักงานพัสดุ', NULL, '{\"code\": \"4-1-2002\", \"note\": \"เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN3', NULL, 'position_name', 'เจ้าพนักงานเวชสถิติ', NULL, '{\"code\": \"4-1-2004\", \"note\": \"เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN4', NULL, 'position_name', 'เจ้าพนักงานสถิติ', NULL, '{\"code\": \"4-1-2005\", \"note\": \"เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN5', NULL, 'position_name', 'เจ้าพนักงานการเงินและบัญชี', NULL, '{\"code\": \"4-2-2002\", \"note\": \"เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN6', NULL, 'position_name', 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', NULL, '{\"code\": \"4-3-2004\", \"note\": \"เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN7', NULL, 'position_name', 'เจ้าพนักงานโสตทัศนศึกษา', NULL, '{\"code\": \"4-3-2007\", \"note\": \"เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN8', NULL, 'position_name', 'เจ้าพนักงานการเกษตร', NULL, '{\"code\": \"4-4-2001\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN9', NULL, 'position_name', 'เจ้าพนักงานทันตสาธารณสุข', NULL, '{\"code\": \"4-6-2001\", \"note\": \"เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN10', NULL, 'position_name', 'เจ้าพนักงานเภสัชกรรม', NULL, '{\"code\": \"4-6-2002\", \"note\": \"เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN11', NULL, 'position_name', 'โภชนากร', NULL, '{\"code\": \"4-6-2003\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN12', NULL, 'position_name', 'เจ้าพนักงานรังสีการแพทย', NULL, '{\"code\": \"4-6-2004\", \"note\": \"เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN13', NULL, 'position_name', 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', NULL, '{\"code\": \"4-6-2005\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN14', NULL, 'position_name', 'เจ้าพนักงานสาธารณสุข', NULL, '{\"code\": \"4-6-2007\", \"note\": \"เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN15', NULL, 'position_name', 'เจ้าพนักงานอาชีวบำบัด', NULL, '{\"code\": \"4-6-2008\", \"note\": \"เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN16', NULL, 'position_name', 'เจ้าพนักงานเวชกิจฉุกเฉิน', NULL, '{\"code\": \"4-6-2011\", \"note\": \"เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN17', NULL, 'position_name', 'เจ้าพนักงานการแพทย์แผนไทย', NULL, '{\"code\": \"4-6-2012\", \"note\": \"เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN18', NULL, 'position_name', 'นายช่างศิลป์', NULL, '{\"code\": \"4-7-2003\", \"note\": \"นายช่างศิลป์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN19', NULL, 'position_name', 'ช่างกายอุปกรณ์', NULL, '{\"code\": \"4-7-2006\", \"note\": \"ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN20', NULL, 'position_name', 'เจ้าพนักงานเครื่องคอมพิวเตอร์', NULL, '{\"code\": \"4-7-2007\", \"note\": \"เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN21', NULL, 'position_name', 'ช่างทันตกรรม', NULL, '{\"code\": \"4-7-2012\", \"note\": \"ช่างทันตกรรม เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN22', NULL, 'position_name', 'นายช่างเทคนิค', NULL, '{\"code\": \"4-7-2013\", \"note\": \"นายช่างเทคนิค เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN23', NULL, 'position_name', 'นายช่างไฟฟ้า', NULL, '{\"code\": \"4-7-2014\", \"note\": \"นายช่างไฟฟ้า เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN24', NULL, 'position_name', 'นายช่างโยธา', NULL, '{\"code\": \"4-7-2016\", \"note\": \"นายช่างโยธา เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN25', NULL, 'position_name', 'ครูการศึกษาพิเศษ', NULL, '{\"code\": \"4-8-2001\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG5', 'PT3PG5PN26', NULL, 'position_name', 'เจ้าพนักงานห้องสมุด', NULL, '{\"code\": \"7-8-2015\", \"note\": \"เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN1', NULL, 'position_name', 'พนักงานประจำตึก', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN2', NULL, 'position_name', 'พนักงานเปล', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN3', NULL, 'position_name', 'พนักงานซักฟอก', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN4', NULL, 'position_name', 'พนักงานบริการ', NULL, '{\"code\": \"\", \"note\": \"พนักงานบริการ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN5', NULL, 'position_name', 'พนักงานรับโทรศัพท์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN6', NULL, 'position_name', 'พนักงานเกษตรพื้นฐาน', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN7', NULL, 'position_name', 'พนักงานเรือยนต', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN8', NULL, 'position_name', 'พนักงานบริการเอกสารทั่วไป', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN9', NULL, 'position_name', 'พนักงานเก็บเอกสาร', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN10', NULL, 'position_name', 'พนักงานบริการสื่ออุปกรณ์การสอน', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN11', NULL, 'position_name', 'พนักงานเก็บเงิน', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN12', NULL, 'position_name', 'พนักงานโสตทัศนศึกษา', NULL, '{\"code\": \"\", \"note\": \"พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN13', NULL, 'position_name', 'พนักงานผลิตน้ำประปา', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN14', NULL, 'position_name', 'พนักงานการเงินและบัญชี', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN15', NULL, 'position_name', 'พนักงานพัสดุ', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN16', NULL, 'position_name', 'พนักงานธุรการ', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN17', NULL, 'position_name', 'พนักงานพิมพ์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN18', NULL, 'position_name', 'พนักงานประเมินผล', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN19', NULL, 'position_name', 'พนักงานการศึกษา', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN20', NULL, 'position_name', 'พนักงานห้องสมุด', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN21', NULL, 'position_name', 'พนักงานสื่อสาร', NULL, '{\"code\": \"\", \"note\": \"พนักงานสื่อสาร เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN22', NULL, 'position_name', 'ล่ามภาษาต่างประเทศ', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN23', NULL, 'position_name', 'ครูพี่เลี้ยง', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN24', NULL, 'position_name', 'พี่เลี้ยง', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN25', NULL, 'position_name', 'พนักงานช่วยการพยาบาล', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN26', NULL, 'position_name', 'พนักงานช่วยเหลือคนไข้', NULL, '{\"code\": \"\", \"note\": \"พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN27', NULL, 'position_name', 'ผู้ช่วยพยาบาล', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN28', NULL, 'position_name', 'ผู้ช่วยทันตแพทย์', NULL, '{\"code\": \"\", \"note\": \"ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN29', NULL, 'position_name', 'พนักงานเภสัชกรรม', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN30', NULL, 'position_name', 'พนักงานประจำห้องยา', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN31', NULL, 'position_name', 'ผู้ช่วยพนักงานสุขศึกษา', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN32', NULL, 'position_name', 'ผู้ช่วยเจ้าหน้าที่อนามัย', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN33', NULL, 'position_name', 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN34', NULL, 'position_name', 'พนักงานการแพทย์และรังสีเทคนิค', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN35', NULL, 'position_name', 'พนักงานจุลทัศนกร', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN36', NULL, 'position_name', 'พนักงานประกอบอาหาร', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN37', NULL, 'position_name', 'พนักงานห้องผ่าตัด', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN38', NULL, 'position_name', 'พนักงานผ่าและรักษาศพ', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN39', NULL, 'position_name', 'พนักงานบัตรรายงานโรค', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN40', NULL, 'position_name', 'พนักงานปฏิบัติการทดลองพาหะนำโรค', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN41', NULL, 'position_name', 'ผู้ช่วยนักกายภาพบำบัด', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN42', NULL, 'position_name', 'พนักงานกู้ชีพ', NULL, '{\"code\": \"\", \"note\": \"พนักงานกู้ชีพเริ่มใช้ 9/06/2566\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN43', NULL, 'position_name', 'พนักงานประจำห้องทดลอง', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN44', NULL, 'position_name', 'พนักงานวิทยาศาสตร์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN45', NULL, 'position_name', 'พนักงานพิธีสงฆ์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN46', NULL, 'position_name', 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN47', NULL, 'position_name', 'ช่างเหล็ก', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN48', NULL, 'position_name', 'ช่างฝีมือทั่วไป', NULL, '{\"code\": \"\", \"note\": \"ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN49', NULL, 'position_name', 'ช่างต่อท่อ', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN50', NULL, 'position_name', 'ช่างศิลป์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN51', NULL, 'position_name', 'ช่างตัดเย็บผ้า', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN52', NULL, 'position_name', 'ช่างตัดผม', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN53', NULL, 'position_name', 'ช่างซ่อมเครื่องทำความเย็น', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN54', NULL, 'position_name', 'ช่างเครื่องช่วยคนพิการ', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN55', NULL, 'position_name', 'ผู้ช่วยช่างทั่วไป', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG6PN56', NULL, 'position_name', 'นักปฏิบัติการฉุกเฉินการแพทย์', NULL, '{\"code\": \"3-6-2023\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG6PN57', NULL, 'position_name', 'นักกำหนดอาหาร', NULL, '{\"code\": \"3-6-2024\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG6', 'PT3PG6PN58', NULL, 'position_name', 'พนักงานขับรถยนต์', NULL, '{\"code\": \"\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG2', 'PT3PG6PN59', NULL, 'position_name', 'นักนิติวิทยาศาสตร์', NULL, '{\"code\": \"3-5-004-1\", \"note\": \"\"}', 1
        ),
        (
                'PT3PG1', 'PT3PG6PN60', NULL, 'position_name', 'นักสาธารณสุข', NULL, '{\"code\": \"3-6-022-1\", \"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN1', NULL, 'position_name', 'นักกายภาพบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN2', NULL, 'position_name', 'นักกิจกรรมบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN3', NULL, 'position_name', 'นักจิตวิทยาคลินิก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN4', NULL, 'position_name', 'ทันตแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN5', NULL, 'position_name', 'นักเทคนิคการแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN6', NULL, 'position_name', 'นายสัตวแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN7', NULL, 'position_name', 'พยาบาลวิชาชีพ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN8', NULL, 'position_name', 'นายแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN9', NULL, 'position_name', 'แพทย์แผนไทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN10', NULL, 'position_name', 'เภสัชกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN11', NULL, 'position_name', 'นักรังสีการแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN12', NULL, 'position_name', 'นักเวชศาสตร์การสื่อความหมาย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN13', NULL, 'position_name', 'นักเทคโนโลยีหัวใจและทรวงอก', NULL, '{\"note\": \"นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN14', NULL, 'position_name', 'นักฟิสิกส์การแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN15', NULL, 'position_name', 'นักทัศนมาตร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN16', NULL, 'position_name', 'นักกายอุปกรณ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN17', NULL, 'position_name', 'นักสาธารณสุข', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG1', 'PT4PG1PN18', NULL, 'position_name', 'วิศวกรไฟฟ้า', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN1', NULL, 'position_name', 'นักวิชาการศึกษาพิเศษ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN2', NULL, 'position_name', 'นักฟิสิกส์รังสี', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN3', NULL, 'position_name', 'นักวิทยาศาสตร์', NULL, '{\"note\": \"นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN4', NULL, 'position_name', 'นักจิตวิทยา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN5', NULL, 'position_name', 'นักโภชนาการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN6', NULL, 'position_name', 'นักวิชาการสาธารณสุข', NULL, '{\"note\": \"นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN7', NULL, 'position_name', 'นักอาชีวบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN8', NULL, 'position_name', 'นักวิชาการอาหารและยา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN9', NULL, 'position_name', 'นักวิทยาศาสตร์การแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN10', NULL, 'position_name', 'ช่างภาพการแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN11', NULL, 'position_name', 'นักสังคมสงเคราะห์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN12', NULL, 'position_name', 'นักปฏิบัติการฉุกเฉินการแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN13', NULL, 'position_name', 'นักกำหนดอาหาร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG2', 'PT4PG2PN14', NULL, 'position_name', 'นักนิติวิทยาศาสตร์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG3', 'PT4PG3PN1', NULL, 'position_name', 'นักวิชาการคอมพิวเตอร์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN2', NULL, 'position_name', 'นักจัดการงานทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN3', NULL, 'position_name', 'นักทรัพยากรบุคคล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN4', NULL, 'position_name', 'นิติกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN5', NULL, 'position_name', 'นักวิเคราะห์นโยบายและแผน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN6', NULL, 'position_name', 'นักเทคโนโลยีสารสนเทศ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN7', NULL, 'position_name', 'นักวิชาการพัสดุ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN8', NULL, 'position_name', 'นักวิชาการสถิติ', NULL, '{\"note\": \"นักวิชาการสถิติ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN9', NULL, 'position_name', 'นักวิเทศสัมพันธ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN10', NULL, 'position_name', 'นักวิชาการเงินและบัญชี', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN11', NULL, 'position_name', 'นักวิชาการตรวจสอบภายใน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN12', NULL, 'position_name', 'นักประชาสัมพันธ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN13', NULL, 'position_name', 'นักวิชาการเผยแพร่', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN14', NULL, 'position_name', 'นักวิชาการโสตทัศนศึกษา', NULL, '{\"note\": \"นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN15', NULL, 'position_name', 'นักวิชาการเกษตร', NULL, '{\"note\": \"นักวิชาการเกษตร เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN16', NULL, 'position_name', 'วิศวกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN17', NULL, 'position_name', 'บรรณารักษ์', NULL, '{\"note\": \"บรรณารักษ์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN18', NULL, 'position_name', 'นักวิชาการศึกษา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG4', 'PT4PG3PN19', NULL, 'position_name', 'วิทยาจารย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN1', NULL, 'position_name', 'เจ้าพนักงานธุรการ', NULL, '{\"note\": \"เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN2', NULL, 'position_name', 'เจ้าพนักงานพัสดุ', NULL, '{\"note\": \"เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN3', NULL, 'position_name', 'เจ้าพนักงานเวชสถิติ', NULL, '{\"note\": \"เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN4', NULL, 'position_name', 'เจ้าพนักงานสถิติ', NULL, '{\"note\": \"เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN5', NULL, 'position_name', 'เจ้าพนักงานการเงินและบัญชี', NULL, '{\"note\": \"เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN6', NULL, 'position_name', 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', NULL, '{\"note\": \"เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN7', NULL, 'position_name', 'เจ้าพนักงานโสตทัศนศึกษา', NULL, '{\"note\": \"เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN8', NULL, 'position_name', 'เจ้าพนักงานการเกษตร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN9', NULL, 'position_name', 'เจ้าพนักงานทันตสาธารณสุข', NULL, '{\"note\": \"เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN10', NULL, 'position_name', 'เจ้าพนักงานเภสัชกรรม', NULL, '{\"note\": \"เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN11', NULL, 'position_name', 'โภชนากร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN12', NULL, 'position_name', 'เจ้าพนักงานรังสีการแพทย', NULL, '{\"note\": \"เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN13', NULL, 'position_name', 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN14', NULL, 'position_name', 'เจ้าพนักงานสาธารณสุข', NULL, '{\"note\": \"เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN15', NULL, 'position_name', 'เจ้าพนักงานอาชีวบำบัด', NULL, '{\"note\": \"เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN16', NULL, 'position_name', 'เจ้าพนักงานเวชกิจฉุกเฉิน', NULL, '{\"note\": \"เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN17', NULL, 'position_name', 'เจ้าพนักงานการแพทย์แผนไทย', NULL, '{\"note\": \"เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN18', NULL, 'position_name', 'นายช่างศิลป์', NULL, '{\"note\": \"นายช่างศิลป์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN19', NULL, 'position_name', 'ช่างกายอุปกรณ์', NULL, '{\"note\": \"ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN20', NULL, 'position_name', 'เจ้าพนักงานเครื่องคอมพิวเตอร์', NULL, '{\"note\": \"เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN21', NULL, 'position_name', 'ช่างทันตกรรม', NULL, '{\"note\": \"ช่างทันตกรรม เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN22', NULL, 'position_name', 'นายช่างเทคนิค', NULL, '{\"note\": \"นายช่างเทคนิค เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN23', NULL, 'position_name', 'นายช่างไฟฟ้า', NULL, '{\"note\": \"นายช่างไฟฟ้า เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN24', NULL, 'position_name', 'นายช่างโยธา', NULL, '{\"note\": \"นายช่างโยธา เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN25', NULL, 'position_name', 'ครูการศึกษาพิเศษ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG5', 'PT4PG5PN26', NULL, 'position_name', 'เจ้าพนักงานห้องสมุด', NULL, '{\"note\": \"เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN1', NULL, 'position_name', 'พนักงานประจำตึก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN2', NULL, 'position_name', 'พนักงานเปล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN3', NULL, 'position_name', 'พนักงานซักฟอก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN4', NULL, 'position_name', 'พนักงานบริการ', NULL, '{\"note\": \"พนักงานบริการ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN5', NULL, 'position_name', 'พนักงานรับโทรศัพท์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN6', NULL, 'position_name', 'พนักงานเกษตรพื้นฐาน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN7', NULL, 'position_name', 'พนักงานเรือยนต', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN8', NULL, 'position_name', 'พนักงานบริการเอกสารทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN9', NULL, 'position_name', 'พนักงานเก็บเอกสาร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN10', NULL, 'position_name', 'พนักงานบริการสื่ออุปกรณ์การสอน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN11', NULL, 'position_name', 'พนักงานเก็บเงิน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN12', NULL, 'position_name', 'พนักงานโสตทัศนศึกษา', NULL, '{\"note\": \"พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN13', NULL, 'position_name', 'พนักงานผลิตน้ำประปา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN14', NULL, 'position_name', 'พนักงานการเงินและบัญชี', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN15', NULL, 'position_name', 'พนักงานพัสดุ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN16', NULL, 'position_name', 'พนักงานธุรการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN17', NULL, 'position_name', 'พนักงานพิมพ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN18', NULL, 'position_name', 'พนักงานประเมินผล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN19', NULL, 'position_name', 'พนักงานการศึกษา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN20', NULL, 'position_name', 'พนักงานห้องสมุด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN21', NULL, 'position_name', 'พนักงานสื่อสาร', NULL, '{\"note\": \"พนักงานสื่อสาร เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN22', NULL, 'position_name', 'ล่ามภาษาต่างประเทศ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN23', NULL, 'position_name', 'ครูพี่เลี้ยง', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN24', NULL, 'position_name', 'พี่เลี้ยง', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN25', NULL, 'position_name', 'พนักงานช่วยการพยาบาล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN26', NULL, 'position_name', 'พนักงานช่วยเหลือคนไข้', NULL, '{\"note\": \"พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN27', NULL, 'position_name', 'ผู้ช่วยพยาบาล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN28', NULL, 'position_name', 'ผู้ช่วยทันตแพทย์', NULL, '{\"note\": \"ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN29', NULL, 'position_name', 'พนักงานเภสัชกรรม', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN30', NULL, 'position_name', 'พนักงานประจำห้องยา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN31', NULL, 'position_name', 'ผู้ช่วยพนักงานสุขศึกษา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN32', NULL, 'position_name', 'ผู้ช่วยเจ้าหน้าที่อนามัย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN33', NULL, 'position_name', 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN34', NULL, 'position_name', 'พนักงานการแพทย์และรังสีเทคนิค', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN35', NULL, 'position_name', 'พนักงานจุลทัศนกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN36', NULL, 'position_name', 'พนักงานประกอบอาหาร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN37', NULL, 'position_name', 'พนักงานห้องผ่าตัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN38', NULL, 'position_name', 'พนักงานผ่าและรักษาศพ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN39', NULL, 'position_name', 'พนักงานบัตรรายงานโรค', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN40', NULL, 'position_name', 'พนักงานปฏิบัติการทดลองพาหะนำโรค', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN41', NULL, 'position_name', 'ผู้ช่วยนักกายภาพบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN42', NULL, 'position_name', 'พนักงานกู้ชีพ', NULL, '{\"note\": \"พนักงานกู้ชีพเริ่มใช้ 9/06/2566\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN43', NULL, 'position_name', 'พนักงานประจำห้องทดลอง', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN44', NULL, 'position_name', 'พนักงานวิทยาศาสตร์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN45', NULL, 'position_name', 'พนักงานพิธีสงฆ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN46', NULL, 'position_name', 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN47', NULL, 'position_name', 'ช่างเหล็ก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN48', NULL, 'position_name', 'ช่างฝีมือทั่วไป', NULL, '{\"note\": \"ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN49', NULL, 'position_name', 'ช่างต่อท่อ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN50', NULL, 'position_name', 'ช่างศิลป์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN52', NULL, 'position_name', 'ช่างตัดเย็บผ้า', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN52', NULL, 'position_name', 'ช่างตัดผม', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN53', NULL, 'position_name', 'ช่างซ่อมเครื่องทำความเย็น', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN54', NULL, 'position_name', 'ช่างเครื่องช่วยคนพิการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN55', NULL, 'position_name', 'ผู้ช่วยช่างทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT4PG6', 'PT4PG6PN56', NULL, 'position_name', 'พนักงานขับรถยนต์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN1', NULL, 'position_name', 'นักกายภาพบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN2', NULL, 'position_name', 'นักกิจกรรมบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN3', NULL, 'position_name', 'นักจิตวิทยาคลินิก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN4', NULL, 'position_name', 'ทันตแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN5', NULL, 'position_name', 'นักเทคนิคการแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN6', NULL, 'position_name', 'นายสัตวแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN7', NULL, 'position_name', 'พยาบาลวิชาชีพ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN8', NULL, 'position_name', 'นายแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN9', NULL, 'position_name', 'แพทย์แผนไทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN10', NULL, 'position_name', 'เภสัชกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN11', NULL, 'position_name', 'นักรังสีการแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN12', NULL, 'position_name', 'นักเวชศาสตร์การสื่อความหมาย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN13', NULL, 'position_name', 'นักเทคโนโลยีหัวใจและทรวงอก', NULL, '{\"note\": \"นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN14', NULL, 'position_name', 'นักฟิสิกส์การแพทย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN15', NULL, 'position_name', 'นักทัศนมาตร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN16', NULL, 'position_name', 'นักกายอุปกรณ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN27', NULL, 'position_name', 'วิศวกรไฟฟ้า', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG1', 'PT5PG1PN28', NULL, 'position_name', 'นักสาธารณสุข', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN1', NULL, 'position_name', 'นักวิชาการศึกษาพิเศษ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN2', NULL, 'position_name', 'นักฟิสิกส์รังสี', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2P3', NULL, 'position_name', 'นักวิทยาศาสตร์', NULL, '{\"note\": \"นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN4', NULL, 'position_name', 'นักจิตวิทยา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN5', NULL, 'position_name', 'นักโภชนาการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN6', NULL, 'position_name', 'นักวิชาการสาธารณสุข', NULL, '{\"note\": \"นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN7', NULL, 'position_name', 'นักอาชีวบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN8', NULL, 'position_name', 'นักวิชาการอาหารและยา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN9', NULL, 'position_name', 'นักวิทยาศาสตร์การแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN10', NULL, 'position_name', 'ช่างภาพการแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN11', NULL, 'position_name', 'นักสังคมสงเคราะห์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN12', NULL, 'position_name', 'นักปฏิบัติการฉุกเฉินการแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN13', NULL, 'position_name', 'นักกำหนดอาหาร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG2', 'PT5PG2PN14', NULL, 'position_name', 'นักนิติวิทยาศาสตร์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG3', 'PT5PG3PN1', NULL, 'position_name', 'นักวิชาการคอมพิวเตอร์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN1', NULL, 'position_name', 'นักจัดการงานทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN2', NULL, 'position_name', 'นักทรัพยากรบุคคล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN3', NULL, 'position_name', 'นิติกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN4', NULL, 'position_name', 'นักวิเคราะห์นโยบายและแผน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN5', NULL, 'position_name', 'นักเทคโนโลยีสารสนเทศ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN6', NULL, 'position_name', 'นักวิชาการพัสดุ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN7', NULL, 'position_name', 'นักวิชาการสถิติ', NULL, '{\"note\": \"นักวิชาการสถิติ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN8', NULL, 'position_name', 'นักวิเทศสัมพันธ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN9', NULL, 'position_name', 'นักวิชาการเงินและบัญชี', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN10', NULL, 'position_name', 'นักวิชาการตรวจสอบภายใน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN11', NULL, 'position_name', 'นักประชาสัมพันธ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN12', NULL, 'position_name', 'นักวิชาการเผยแพร่', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN13', NULL, 'position_name', 'นักวิชาการโสตทัศนศึกษา', NULL, '{\"note\": \"นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN14', NULL, 'position_name', 'นักวิชาการเกษตร', NULL, '{\"note\": \"นักวิชาการเกษตร เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN15', NULL, 'position_name', 'วิศวกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN16', NULL, 'position_name', 'บรรณารักษ์', NULL, '{\"note\": \"บรรณารักษ์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN17', NULL, 'position_name', 'นักวิชาการศึกษา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG4', 'PT5PG4PN18', NULL, 'position_name', 'วิทยาจารย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN1', NULL, 'position_name', 'เจ้าพนักงานธุรการ', NULL, '{\"note\": \"เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN2', NULL, 'position_name', 'เจ้าพนักงานพัสดุ', NULL, '{\"note\": \"เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN3', NULL, 'position_name', 'เจ้าพนักงานเวชสถิติ', NULL, '{\"note\": \"เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN4', NULL, 'position_name', 'เจ้าพนักงานสถิติ', NULL, '{\"note\": \"เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN5', NULL, 'position_name', 'เจ้าพนักงานการเงินและบัญชี', NULL, '{\"note\": \"เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN6', NULL, 'position_name', 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', NULL, '{\"note\": \"เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN7', NULL, 'position_name', 'เจ้าพนักงานโสตทัศนศึกษา', NULL, '{\"note\": \"เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN8', NULL, 'position_name', 'เจ้าพนักงานการเกษตร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN9', NULL, 'position_name', 'เจ้าพนักงานทันตสาธารณสุข', NULL, '{\"note\": \"เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN10', NULL, 'position_name', 'เจ้าพนักงานเภสัชกรรม', NULL, '{\"note\": \"เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN11', NULL, 'position_name', 'โภชนากร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN12', NULL, 'position_name', 'เจ้าพนักงานรังสีการแพทย', NULL, '{\"note\": \"เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN13', NULL, 'position_name', 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN14', NULL, 'position_name', 'เจ้าพนักงานสาธารณสุข', NULL, '{\"note\": \"เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN15', NULL, 'position_name', 'เจ้าพนักงานอาชีวบำบัด', NULL, '{\"note\": \"เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN16', NULL, 'position_name', 'เจ้าพนักงานเวชกิจฉุกเฉิน', NULL, '{\"note\": \"เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN17', NULL, 'position_name', 'เจ้าพนักงานการแพทย์แผนไทย', NULL, '{\"note\": \"เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN18', NULL, 'position_name', 'นายช่างศิลป์', NULL, '{\"note\": \"นายช่างศิลป์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN19', NULL, 'position_name', 'ช่างกายอุปกรณ์', NULL, '{\"note\": \"ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN20', NULL, 'position_name', 'เจ้าพนักงานเครื่องคอมพิวเตอร์', NULL, '{\"note\": \"เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN21', NULL, 'position_name', 'ช่างทันตกรรม', NULL, '{\"note\": \"ช่างทันตกรรม เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN22', NULL, 'position_name', 'นายช่างเทคนิค', NULL, '{\"note\": \"นายช่างเทคนิค เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN23', NULL, 'position_name', 'นายช่างไฟฟ้า', NULL, '{\"note\": \"นายช่างไฟฟ้า เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN24', NULL, 'position_name', 'นายช่างโยธา', NULL, '{\"note\": \"นายช่างโยธา เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN25', NULL, 'position_name', 'ครูการศึกษาพิเศษ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG5', 'PT5PG5PN26', NULL, 'position_name', 'เจ้าพนักงานห้องสมุด', NULL, '{\"note\": \"เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN1', NULL, 'position_name', 'พนักงานประจำตึก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN2', NULL, 'position_name', 'พนักงานเปล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN3', NULL, 'position_name', 'พนักงานซักฟอก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN4', NULL, 'position_name', 'พนักงานบริการ', NULL, '{\"note\": \"พนักงานบริการ เริ่มใช้ 24/12/2562\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN5', NULL, 'position_name', 'พนักงานรับโทรศัพท์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN6', NULL, 'position_name', 'พนักงานเกษตรพื้นฐาน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN7', NULL, 'position_name', 'พนักงานเรือยนต', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN8', NULL, 'position_name', 'พนักงานบริการเอกสารทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN9', NULL, 'position_name', 'พนักงานเก็บเอกสาร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN10', NULL, 'position_name', 'พนักงานบริการสื่ออุปกรณ์การสอน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN11', NULL, 'position_name', 'พนักงานเก็บเงิน', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN12', NULL, 'position_name', 'พนักงานโสตทัศนศึกษา', NULL, '{\"note\": \"พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN13', NULL, 'position_name', 'พนักงานผลิตน้ำประปา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN14', NULL, 'position_name', 'พนักงานการเงินและบัญชี', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN15', NULL, 'position_name', 'พนักงานพัสดุ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN16', NULL, 'position_name', 'พนักงานธุรการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN17', NULL, 'position_name', 'พนักงานพิมพ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN18', NULL, 'position_name', 'พนักงานประเมินผล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN19', NULL, 'position_name', 'พนักงานการศึกษา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN20', NULL, 'position_name', 'พนักงานห้องสมุด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN21', NULL, 'position_name', 'พนักงานสื่อสาร', NULL, '{\"note\": \"พนักงานสื่อสาร เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN22', NULL, 'position_name', 'ล่ามภาษาต่างประเทศ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN23', NULL, 'position_name', 'ครูพี่เลี้ยง', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN24', NULL, 'position_name', 'พี่เลี้ยง', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN25', NULL, 'position_name', 'พนักงานช่วยการพยาบาล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN26', NULL, 'position_name', 'พนักงานช่วยเหลือคนไข้', NULL, '{\"note\": \"พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN27', NULL, 'position_name', 'ผู้ช่วยพยาบาล', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN28', NULL, 'position_name', 'ผู้ช่วยทันตแพทย์', NULL, '{\"note\": \"ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN29', NULL, 'position_name', 'พนักงานเภสัชกรรม', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN30', NULL, 'position_name', 'พนักงานประจำห้องยา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN31', NULL, 'position_name', 'ผู้ช่วยพนักงานสุขศึกษา', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN32', NULL, 'position_name', 'ผู้ช่วยเจ้าหน้าที่อนามัย', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN33', NULL, 'position_name', 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN34', NULL, 'position_name', 'พนักงานการแพทย์และรังสีเทคนิค', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN35', NULL, 'position_name', 'พนักงานจุลทัศนกร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN36', NULL, 'position_name', 'พนักงานประกอบอาหาร', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN37', NULL, 'position_name', 'พนักงานห้องผ่าตัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN38', NULL, 'position_name', 'พนักงานผ่าและรักษาศพ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN39', NULL, 'position_name', 'พนักงานบัตรรายงานโรค', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN40', NULL, 'position_name', 'พนักงานปฏิบัติการทดลองพาหะนำโรค', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN41', NULL, 'position_name', 'ผู้ช่วยนักกายภาพบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN42', NULL, 'position_name', 'พนักงานกู้ชีพ', NULL, '{\"note\": \"พนักงานกู้ชีพเริ่มใช้ 9/06/2566\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN43', NULL, 'position_name', 'พนักงานประจำห้องทดลอง', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN44', NULL, 'position_name', 'พนักงานวิทยาศาสตร์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN45', NULL, 'position_name', 'พนักงานพิธีสงฆ์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN46', NULL, 'position_name', 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN47', NULL, 'position_name', 'ช่างเหล็ก', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN48', NULL, 'position_name', 'ช่างฝีมือทั่วไป', NULL, '{\"note\": \"ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN49', NULL, 'position_name', 'ช่างต่อท่อ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN50', NULL, 'position_name', 'ช่างศิลป์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN51', NULL, 'position_name', 'ช่างตัดเย็บผ้า', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN52', NULL, 'position_name', 'ช่างตัดผม', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN53', NULL, 'position_name', 'ช่างซ่อมเครื่องทำความเย็น', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN54', NULL, 'position_name', 'ช่างเครื่องช่วยคนพิการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN55', NULL, 'position_name', 'ผู้ช่วยช่างทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN56', NULL, 'position_name', 'พนักงานขับรถยนต์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT6PG3', 'PT6PG3PN1', NULL, 'position_name', 'พนักงานกายภาพบำบัด', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT6PG3', 'PT6PG3PN2', NULL, 'position_name', 'พนักงานช่วยเหลือคนไข้', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT6PG3', 'PT6PG3PN3', NULL, 'position_name', 'ผู้ช่วยทันตแพทย์', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT6PG3', 'PT6PG3PN4', NULL, 'position_name', 'พนักงานเภสัชกรรม', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT6PG3', 'PT6PG3PN5', NULL, 'position_name', 'พนักงานธุรการ', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT7PG1', 'PT7PG1PN1', NULL, 'position_name', 'พนักงานทั่วไป', NULL, '{\"note\": \"\"}', 1
        ),
        (
                'PT5PG6', 'PT5PG6PN57', NULL, 'position_name', 'พนักงานบริการ (ทำความสะอาด)', '', NULL, 1
        ),
        (
                'PT5PG6', 'PT5PG6PN58', NULL, 'position_name', 'พนักงานบริการ (รักษาความปลอดภัย)', '', NULL, 1
        ),
        (
                'PT6PG3', 'PT6PG3PN6', NULL, 'position_name', 'ช่างไฟฟ้าและอิเลคทรอนิกส์', '', NULL, 1
        ),
        (
                'PT4PG6', 'PT4PG6PN57', NULL, 'position_name', 'พนักงานบริการ (ทำความสะอาด)', '', NULL, 1
        ),
        (
                'PT4PG6', 'PT4PG6PN58', NULL, 'position_name', 'พนักงานบริการ (รักษาความปลอดภัย)', '', NULL, 1
        );
