        SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
        SET time_zone = "+00:00";

--   วิธีได้มา
        INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
         (1,'method_get','ซื้อ'),
         (2,'method_get','จ้างก่อสร้าง'),
         (3,'method_get','จ้างทำของ/จ้างเหมาบริการ'),
         (4,'method_get','เช่า'),
         (5,'method_get','บริจาค');

--   การจัดซื้อ
        INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
        (1,'purchase','เฉพาะเจาะจง'), 
        (2,'purchase','บริจาค'); 

--   งบที่ใช้
        INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
        (1,'budget_detail','งบประมาณ'), 
        (2,'budget_detail','เงิน UC'), 
        (3,'budget_detail','เงินบำรุง'), 
        (4,'budget_detail','เงินบริจาค'), 
        (5,'budget_detail','เงินอื่นๆ'), 
        (6,'budget_detail','เงินค่าบริการทางการแพทย์ที่เบิกจ่ายในลักษณะงบลงทุน');

--   สถานะการใช้งาน
        INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
        (1,'assetstatus','ปกติ'), 
        (2,'assetstatus','จำหน่ายแล้ว'), 
        (3,'assetstatus','รอจำหน่าย'), 
        (4,'assetstatus','ถูกยืม');  

--   การบำรุงรักษา PM
        INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
        (1,'maintain_pm','โดยหน่วยงานภายใน'), 
        (2,'maintain_pm','โดยหน่วยงานภายนอก'), 
        (3,'maintain_pm','ไม่ระบุ');  

--   การสอบ CAL
        INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
        (1,'test_cal','โดยหน่วยงานภายใน'), 
        (2,'test_cal','โดยหน่วยงานภายนอก'), 
        (3,'test_cal','ไม่ระบุ'); 

 --   ความเสี่ยง
          INSERT INTO `categorise` (`code`, `name`, `title`) VALUES
        (1,'asset_risk','ต่ำ'), 
        (2,'asset_risk','กลาง'), 
        (3,'asset_risk','สูง'); 
      