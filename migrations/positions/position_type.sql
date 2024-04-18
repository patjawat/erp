/*
-- Query: select category_id,code,name,title,description,data_json,active from categorise where name = "position_type"
LIMIT 0, 1000

-- Date: 2024-02-26 21:50
*/

INSERT INTO categorise (`category_id`,`code`,`name`,`title`,`description`,`data_json`,`active`) VALUES 
(NULL,'PT1','position_type','ข้าราชการ',NULL,NULL,1),
(NULL,'PT2','position_type','พนักงานราชการ',NULL,NULL,1),
(NULL,'PT3','position_type','พนักงานกระทรวง (พกส.)',NULL,NULL,1),
(NULL,'PT4','position_type','ลูกจ้างชั่วคราวรายเดือน',NULL,NULL,1),
(NULL,'PT5','position_type','ลูกจ้างชั่วคราวรายวัน',NULL,NULL,1);
