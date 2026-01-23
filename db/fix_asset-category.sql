-- แก้ไขหมวดหมู่ทรัพย์สิน
-- ลบ asset_group,asset_type เดิมออก 
DELETE FROM `categorise` WHERE name = 'asset_group';
DELETE FROM `categorise` WHERE `category_id` ='3' AND `name`='asset_type';

UPDATE asset 
SET data_json = JSON_SET(data_json, '$.old_fsn', code);

INSERT INTO categorise (name, title, code, data_json) VALUES
                ('asset_group','ที่ดิน', 'LAND', JSON_OBJECT('description', 'ที่ดินและสิทธิในที่ดิน')),
                ('asset_group','อาคาร', 'BLDG', JSON_OBJECT('description', 'อาคารและสิ่งปลูกสร้างขนาดใหญ่')),
                ('asset_group','สิ่งปลูกสร้าง', 'CONST', JSON_OBJECT('description', 'โครงสร้างพื้นฐานและสาธารณูปโภค')),
                ('asset_group','ครุภัณฑ์', 'EQUIP', JSON_OBJECT('description', 'อุปกรณ์และเครื่องมือต่างๆ')),
                ('asset_group','ครุภัณฑ์ต่ำกว่าเกณฑ์', 'MINOR', JSON_OBJECT('description', 'ครุภัณฑ์มูลค่าต่ำ')),
                ('asset_group','สินทรัพย์ไม่มีตัวตน', 'INTAN', JSON_OBJECT('description', 'ลิขสิทธิ์ สิทธิบัตร ซอฟต์แวร์')),
                ('asset_group','วัสดุ', 'MATER', JSON_OBJECT('description', 'วัสดุสิ้นเปลืองและคงคลัง'));

INSERT INTO categorise 
        (name,group_id, title, code, data_json) 
        VALUES
        ('asset_type','4','ครุภัณฑ์การแพทย์', 'MED', JSON_OBJECT('title_en', 'Medical Equipment', 'description','อุปกรณ์ทางการแพทย์และเครื่องมือรักษาพยาบาล')),
        ('asset_type','4','ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ELE', JSON_OBJECT('title_en','Electrical and Radio Equipment', 'description', 'อุปกรณ์ไฟฟ้าและเครื่องมือวิทยุกสารสนเทศ')),
        ('asset_type','4','ครุภัณฑ์โรงงาน', 'IND', JSON_OBJECT('title_en','Industrial Equipment', 'description', 'เครื่องจักรและอุปกรณ์ในงานโรงงาน การผลิต')),
        ('asset_type','4','ครุภัณฑ์การเกษตร', 'AGR', JSON_OBJECT('title_en','Agricultural Equipment', 'description', 'เครื่องมือและอุปกรณ์ทางการเกษตร')),
        ('asset_type','4','ครุภัณฑ์การศึกษา', 'EDU', JSON_OBJECT('title_en','Educational Equipment', 'description', 'อุปกรณ์การเรียนการสอนและวัสดุการศึกษา')),
        ('asset_type','4','ครุภัณฑ์คอมพิวเตอร์', 'COM', JSON_OBJECT('title_en','Computer Equipment', 'description', 'เครื่องคอมพิวเตอร์และอุปกรณ์เทคโนโลยีสารสนเทศ')),
        ('asset_type','4','ครุภัณฑ์โฆษณาและเผยแพร่', 'ADV', JSON_OBJECT('title_en','Advertising and Publishing Equipment', 'description', 'อุปกรณ์โฆษณา ประชาสัมพันธ์ และเผยแพร่ข้อมูล')),
        ('asset_type','4','ครุภัณฑ์งานบ้านงานครัว', 'HOM', JSON_OBJECT('title_en','Household and Kitchen Equipment', 'description', 'อุปกรณ์ใช้ในบ้านและครัว สำหรับงานทั่วไป')),
        ('asset_type','4','ครุภัณฑ์ยานพาหนะ', 'VEH', JSON_OBJECT('title_en','Vehicle Equipment', 'description', 'ยานพาหนะและอุปกรณ์การขนส่ง')),
        ('asset_type','4','ครุภัณฑ์วิทยาศาสตร์', 'SCI', JSON_OBJECT('title_en','Scientific Equipment', 'description', 'เครื่องมือและอุปกรณ์ทางวิทยาศาสตร์และการวิจัย')),
        ('asset_type','4','ครุภัณฑ์สำนักงาน', 'OFF', JSON_OBJECT('title_en','Office Equipment', 'description', 'อุปกรณ์สำนักงานและเครื่องใช้ในการบริหารงาน'));

        INSERT INTO categorise (name, category_id, title, code, data_json) VALUES
        ('asset_category','MED','กระตุกไฟฟ้าหัวใจ','DF',JSON_OBJECT('title_en', 'Defibrillation')),
        ('asset_category','MED','กล้องจุลทรรศน์ในการผ่าตัด','MC',JSON_OBJECT('title_en', 'Microscopy')),
        ('asset_category','MED','กล้องส่องตรวจวินิจฉัยและรักษา','ES',JSON_OBJECT('title_en', 'Endoscopic examination')),
        ('asset_category','MED','กายภาพบำบัด','PT',JSON_OBJECT('title_en', 'Physical Therapy')),
        ('asset_category','MED','คลังเลือด','BB',JSON_OBJECT('title_en', 'Blood Bank')),
        ('asset_category','MED','ควบคุมการให้สารน้ำ','IP',JSON_OBJECT('title_en', 'Infusion pump')),
        ('asset_category','MED','โคมไฟผ่าตัด','LO',JSON_OBJECT('title_en', 'Lamp operation')),
        ('asset_category','MED','จักษุ','EM',JSON_OBJECT('title_en', 'Eye medical')),
        ('asset_category','MED','จ่ายกลาง','CSSD',JSON_OBJECT('title_en', 'Central Sterile Supply Department')),
        ('asset_category','MED','จี้ห้ามเลือดและตัดเนื้อเยื่อ','CE',JSON_OBJECT('title_en', 'Cauterization equipment')),
        ('asset_category','MED','ช่วยหายใจ','RS',JSON_OBJECT('title_en', 'Respiration')),
        ('asset_category','MED','ชันสูตร','LAB',JSON_OBJECT('title_en', 'Laboratory')),
        ('asset_category','MED','ตรวจทารกในครรภ์','FT',JSON_OBJECT('title_en', 'Fetus')),
        ('asset_category','MED','ตรวจรักษาหัวใจและปอด','HL',JSON_OBJECT('title_en', 'Heart Lung')),
        ('asset_category','MED','ตรวจวินิจฉัยและรักษาสมอง','NE',JSON_OBJECT('title_en', 'Neuro equipment')),
        ('asset_category','MED','ติดตามการทำงานของหัวใจและสัญญาณชีพ','ME',JSON_OBJECT('title_en', 'Monitor equipment')),
        ('asset_category','MED','เตียงผ่าตัด-คลอด','OB',JSON_OBJECT('title_en', 'Operation Bed')),
        ('asset_category','MED','เตียงผู้ป่วย','BP',JSON_OBJECT('title_en', 'Bed patient')),
        ('asset_category','MED','ไตเทียม','CKD',JSON_OBJECT('title_en', 'Chronic Kidney Disease')),
        ('asset_category','MED','ทันตกรรม','DE',JSON_OBJECT('title_en', 'Dental equipment')),
        ('asset_category','MED','ทารกแรกคลอด','NB',JSON_OBJECT('title_en', 'New born')),
        ('asset_category','MED','ผ่าตัด','OE',JSON_OBJECT('title_en', 'Operation equipment')),
        ('asset_category','MED','เภสัชกรรม','PHR',JSON_OBJECT('title_en', 'Pharmacy')),
        ('asset_category','MED','รังสีรักษา','RT',JSON_OBJECT('title_en', 'Radio therapy')),
        ('asset_category','MED','วิสัญญี','AE',JSON_OBJECT('title_en', 'Anesthesia equipment')),
        ('asset_category','MED','ศัลยกรรมออร์โธปิดิกส์','ORT',JSON_OBJECT('title_en', 'Orthopedic')),
        ('asset_category','MED','ศัลยศาสตร์ทางเดินปัสสาวะ','URO',JSON_OBJECT('title_en', 'Urology')),
        ('asset_category','MED','สนับสนุนการแพทย์','MP',JSON_OBJECT('title_en', 'Medical Support')),
        ('asset_category','MED','หู คอ จมูก','ENT',JSON_OBJECT('title_en', 'Ear Nose Throat')),
        ('asset_category','MED','อัลตราซาวด์','US',JSON_OBJECT('title_en', 'Ultrasound')),
        ('asset_category','MED','เอกซเรย์','XR',JSON_OBJECT('title_en', 'Xray')),
        ('asset_category','ELE','ไฟฟ้าและวิทยุ','EE',JSON_OBJECT('title_en', 'Electric equipment')),
        ('asset_category','IND','ช่างซ่อมบำรุง','HT',JSON_OBJECT('title_en', 'Hand Tool')),
        ('asset_category','AGR','การเกษตร','AC',JSON_OBJECT('title_en', 'Agriculture')),
        ('asset_category','EDU','การศึกษา','ED',JSON_OBJECT('title_en', 'Education')),
        ('asset_category','COM','กล้องโทรทัศน์วงจรปิด','CCTV',JSON_OBJECT('title_en', 'Close Circuit Television')),
        ('asset_category','COM','คอมพิวเตอร์และอุปกรณ์','COM',JSON_OBJECT('title_en', 'Computer')),
        ('asset_category','ADV','โฆษณาและเผยแพร่','AP',JSON_OBJECT('title_en', 'Advertise and publish')),
        ('asset_category','HOM','งานบ้านงานครัว','HK',JSON_OBJECT('title_en', 'Housework kitchen work')),
        ('asset_category','VEH','ยานพาหนะบริการทางการแพทย์','VM',JSON_OBJECT('title_en', 'Vehicle medical')),
        ('asset_category','VEH','ยานพาหนะและขนส่ง','VT',JSON_OBJECT('title_en', 'Vehicle transport')),
        ('asset_category','SCI','วิทยาศาสตร์','SC',JSON_OBJECT('title_en', 'Science')),
        ('asset_category','OFF','สำนักงาน','OFF',JSON_OBJECT('title_en', 'Office')),
        ('asset_category','OFF','เครื่องปรับอากาศและฟอกอากาศ','AIR',JSON_OBJECT('title_en', 'Air condition'));

## update ประเภ ทรรัพสินย์
UPDATE `asset` a
INNER JOIN `categorise` c 
  ON c.title = a.data_json->>'$.asset_type_text' 
  AND c.name = 'asset_type' 
  AND c.group_id = 'EQUIP'
SET a.asset_type_id = c.code
WHERE a.asset_group_id = 4;
