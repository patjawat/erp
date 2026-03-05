-- =============================================================
-- Seed: JD Templates กระทรวงสาธารณสุข (15 ตำแหน่ง)
-- นำเข้าผ่าน phpMyAdmin หรือปุ่ม 'นำเข้า Template สาธารณสุข' ในระบบ
-- =============================================================

SET NAMES 'utf8mb4';

-- ── ล้างข้อมูล Seed เดิม (position_code LIKE 'moph_%') ──
DELETE jts FROM jd_template_section jts
  INNER JOIN jd_template jt ON jts.template_id = jt.id
  WHERE jt.position_code LIKE 'moph_%';
DELETE FROM jd_template WHERE position_code LIKE 'moph_%';

-- ── 1. นายแพทย์ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นายแพทย์', 'moph_doctor', 'MD-001', 'Senior',
   'กลุ่มงานเวชกรรม', 'fulltime',
   'ปฏิบัติงานทางการแพทย์ขั้นสูง ตรวจวินิจฉัย รักษาพยาบาลผู้ป่วย และให้บริการสุขภาพแก่ประชาชนอย่างมีคุณภาพตามมาตรฐานวิชาชีพแพทย์',
   'ปริญญาตรีแพทยศาสตร์ และได้รับใบอนุญาตประกอบวิชาชีพเวชกรรม', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการตรวจวินิจฉัยและรักษาพยาบาล', '1. ตรวจ วินิจฉัย บำบัดรักษา และฟื้นฟูสมรรถภาพผู้ป่วยทั้งผู้ป่วยนอกและผู้ป่วยใน
2. ให้การรักษาพยาบาลเบื้องต้นและส่งต่อผู้ป่วยที่อยู่นอกเหนือความสามารถ
3. บันทึกเวชระเบียนผู้ป่วยอย่างครบถ้วนถูกต้อง
4. ปฏิบัติงานเวรตามที่ได้รับมอบหมาย', 1
  FROM jd_template WHERE position_code = 'moph_doctor' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านส่งเสริมสุขภาพและป้องกันโรค', '1. ให้คำแนะนำด้านสุขภาพและป้องกันโรคแก่ผู้ป่วยและญาติ
2. ร่วมออกหน่วยบริการสาธารณสุขชุมชนและเยี่ยมบ้าน
3. ร่วมกิจกรรมเฝ้าระวัง ควบคุม และป้องกันโรคระบาด', 2
  FROM jd_template WHERE position_code = 'moph_doctor' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนาคุณภาพ', '1. ศึกษา ค้นคว้า วิจัยและพัฒนาองค์ความรู้ทางการแพทย์
2. จัดทำแนวทางการรักษา (Clinical Practice Guideline)
3. ร่วมกิจกรรม M&M Conference และ Clinical Conference', 3
  FROM jd_template WHERE position_code = 'moph_doctor' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการสอนและฝึกอบรม', '1. สอนและนิเทศนักศึกษาแพทย์ แพทย์ประจำบ้าน และบุคลากรทีมสุขภาพ
2. บรรยายให้ความรู้แก่บุคลากรสาธารณสุขในหน่วยงาน', 4
  FROM jd_template WHERE position_code = 'moph_doctor' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการบริหารและประสานงาน', '1. ประสานงานกับทีมสหวิชาชีพในการดูแลผู้ป่วย
2. ร่วมประชุมคณะกรรมการต่าง ๆ ของโรงพยาบาล', 5
  FROM jd_template WHERE position_code = 'moph_doctor' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 6
  FROM jd_template WHERE position_code = 'moph_doctor' ORDER BY id DESC LIMIT 1;

-- ── 2. พยาบาลวิชาชีพ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('พยาบาลวิชาชีพ', 'moph_nurse', 'RN-001', 'Senior',
   'กลุ่มงานการพยาบาล', 'fulltime',
   'ให้บริการพยาบาลแก่ผู้ป่วยและประชาชนอย่างมีคุณภาพและปลอดภัย ตามมาตรฐานวิชาชีพการพยาบาล',
   'ปริญญาตรีพยาบาลศาสตร์ และได้รับใบอนุญาตประกอบวิชาชีพการพยาบาล', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการพยาบาลผู้ป่วย', '1. ประเมินภาวะสุขภาพและวางแผนการพยาบาลผู้ป่วย
2. ให้การพยาบาลตามแผนและแนวทางปฏิบัติทางคลินิก
3. ดูแลความปลอดภัยของผู้ป่วยและป้องกันภาวะแทรกซ้อน
4. ปฏิบัติงานเวรตามกำหนด', 1
  FROM jd_template WHERE position_code = 'moph_nurse' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการส่งเสริมสุขภาพ', '1. ให้ความรู้และคำแนะนำด้านสุขภาพแก่ผู้ป่วยและครอบครัว
2. วางแผนจำหน่ายผู้ป่วยและติดตามการดูแลต่อเนื่องที่บ้าน', 2
  FROM jd_template WHERE position_code = 'moph_nurse' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประสานงานและส่งต่อ', '1. ประสานงานกับทีมสหวิชาชีพในการดูแลผู้ป่วยแบบองค์รวม
2. ดำเนินการส่งต่อผู้ป่วยตามระบบส่งต่อของโรงพยาบาล', 3
  FROM jd_template WHERE position_code = 'moph_nurse' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการพัฒนาคุณภาพ', '1. ร่วมพัฒนาแนวทางปฏิบัติทางการพยาบาลและ Care Map
2. เข้าร่วมกิจกรรม CQI และรายงาน Incident Report', 4
  FROM jd_template WHERE position_code = 'moph_nurse' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการบันทึกและเอกสาร', '1. บันทึกข้อมูลผู้ป่วยในระบบ HIS และเวชระเบียน
2. จัดทำสถิติและรายงานผลการดำเนินงานประจำเดือน', 5
  FROM jd_template WHERE position_code = 'moph_nurse' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 6
  FROM jd_template WHERE position_code = 'moph_nurse' ORDER BY id DESC LIMIT 1;

-- ── 3. เภสัชกร ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('เภสัชกร', 'moph_pharmacist', 'PH-001', 'Senior',
   'กลุ่มงานเภสัชกรรม', 'fulltime',
   'ปฏิบัติงานเภสัชกรรมคลินิกและบริหารเวชภัณฑ์ เพื่อให้ผู้ป่วยได้รับยาที่ถูกต้อง ปลอดภัย และมีประสิทธิผล',
   'ปริญญาตรีเภสัชศาสตร์ และได้รับใบอนุญาตประกอบวิชาชีพเภสัชกรรม', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการจ่ายยาและให้คำปรึกษา', '1. ตรวจสอบความถูกต้องของใบสั่งยาก่อนจ่ายยา
2. จ่ายยาและอธิบายวิธีใช้ยา ผลข้างเคียง และข้อควรระวัง
3. ให้คำปรึกษาการใช้ยาแก่แพทย์ พยาบาล และผู้ป่วย', 1
  FROM jd_template WHERE position_code = 'moph_pharmacist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการบริหารเวชภัณฑ์', '1. จัดทำแผนการสั่งซื้อยาและควบคุมสต็อกยา
2. ตรวจรับยาและตรวจสอบคุณภาพเวชภัณฑ์
3. บริหารจัดการยาหมดอายุและยาค้างสต็อก', 2
  FROM jd_template WHERE position_code = 'moph_pharmacist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านเภสัชกรรมคลินิก', '1. ติดตามการใช้ยาของผู้ป่วยในและแจ้งแพทย์กรณีพบปัญหา
2. ทบทวนการสั่งยา (Drug Use Review) และประเมิน Drug Interaction
3. ร่วม Ward Round กับทีมสหวิชาชีพ', 3
  FROM jd_template WHERE position_code = 'moph_pharmacist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการควบคุมคุณภาพ', '1. ควบคุมคุณภาพการเตรียมยา
2. จัดทำรายงาน ADR (Adverse Drug Reaction)
3. ดำเนินการตาม GPP (Good Pharmacy Practice)', 4
  FROM jd_template WHERE position_code = 'moph_pharmacist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนา', '1. จัดทำและเผยแพร่ข้อมูลยาให้บุคลากรทางการแพทย์
2. ร่วมพัฒนา Clinical Practice Guideline ด้านการใช้ยา', 5
  FROM jd_template WHERE position_code = 'moph_pharmacist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 6
  FROM jd_template WHERE position_code = 'moph_pharmacist' ORDER BY id DESC LIMIT 1;

-- ── 4. ทันตแพทย์ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('ทันตแพทย์', 'moph_dentist', 'DT-001', 'Senior',
   'กลุ่มงานทันตกรรม', 'fulltime',
   'ให้บริการทันตกรรมแก่ผู้ป่วยและประชาชน ทั้งด้านการรักษา ส่งเสริมสุขภาพช่องปาก และป้องกันโรคในช่องปาก',
   'ปริญญาตรีทันตแพทยศาสตร์ และได้รับใบอนุญาตประกอบวิชาชีพทันตกรรม', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการรักษาทางทันตกรรม', '1. ตรวจและวินิจฉัยโรคในช่องปาก
2. ถอนฟัน อุดฟัน ขูดหินปูน และรักษาคลองรากฟัน
3. ส่งต่อผู้ป่วยที่เกินขีดความสามารถ', 1
  FROM jd_template WHERE position_code = 'moph_dentist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านทันตสาธารณสุข', '1. จัดกิจกรรมส่งเสริมสุขภาพช่องปากในโรงเรียนและชุมชน
2. ดำเนินโครงการฟลูออไรด์เสริม/เคลือบหลุมร่องฟัน
3. สำรวจและประเมินสุขภาพช่องปากประชาชน', 2
  FROM jd_template WHERE position_code = 'moph_dentist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนาคุณภาพ', '1. ร่วมพัฒนาแนวทางปฏิบัติทางทันตกรรม
2. จัดทำสถิติและรายงานผลงานทันตกรรม', 3
  FROM jd_template WHERE position_code = 'moph_dentist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประสานงาน', '1. ประสานงานกับทีมสหวิชาชีพในการดูแลผู้ป่วยโรคซับซ้อน
2. ประสานงานกับโรงเรียนในโครงการสุขภาพช่องปาก', 4
  FROM jd_template WHERE position_code = 'moph_dentist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_dentist' ORDER BY id DESC LIMIT 1;

-- ── 5. นักเทคนิคการแพทย์ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักเทคนิคการแพทย์', 'moph_med_tech', 'MT-001', 'Senior',
   'กลุ่มงานเทคนิคการแพทย์', 'fulltime',
   'วิเคราะห์ทดสอบตัวอย่างทางห้องปฏิบัติการทางการแพทย์ เพื่อสนับสนุนการวินิจฉัยและการรักษาโรค',
   'ปริญญาตรีเทคนิคการแพทย์ และได้รับใบอนุญาตประกอบวิชาชีพเทคนิคการแพทย์', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการตรวจวิเคราะห์ทางห้องปฏิบัติการ', '1. ตรวจวิเคราะห์ตัวอย่างเลือด ปัสสาวะ อุจจาระ และสิ่งส่งตรวจอื่น ๆ
2. ตรวจทางโลหิตวิทยา เคมีคลินิก จุลชีววิทยา และภูมิคุ้มกันวิทยา
3. จัดทำรายงานผลการตรวจและส่งผลให้แพทย์อย่างทันเวลา', 1
  FROM jd_template WHERE position_code = 'moph_med_tech' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการควบคุมคุณภาพ', '1. ดำเนินการ Internal Quality Control (IQC) ทุกวัน
2. เข้าร่วม External Quality Assessment (EQA)
3. ดำเนินการตามมาตรฐาน ISO 15189', 2
  FROM jd_template WHERE position_code = 'moph_med_tech' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการจัดการสิ่งส่งตรวจ', '1. รับและตรวจสอบความเหมาะสมของสิ่งส่งตรวจ
2. จัดเก็บตัวอย่างให้ถูกต้องตามมาตรฐาน', 3
  FROM jd_template WHERE position_code = 'moph_med_tech' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนา', '1. ศึกษาวิจัยและพัฒนาวิธีการตรวจวิเคราะห์ใหม่ ๆ
2. อบรมนักศึกษาเทคนิคการแพทย์ฝึกงาน', 4
  FROM jd_template WHERE position_code = 'moph_med_tech' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_med_tech' ORDER BY id DESC LIMIT 1;

-- ── 6. นักกายภาพบำบัด ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักกายภาพบำบัด', 'moph_physio', 'PT-001', 'Senior',
   'กลุ่มงานเวชกรรมฟื้นฟู', 'fulltime',
   'ประเมินและให้การรักษาฟื้นฟูสมรรถภาพผู้ป่วยด้วยวิธีกายภาพบำบัด',
   'ปริญญาตรีกายภาพบำบัด และได้รับใบอนุญาตประกอบวิชาชีพกายภาพบำบัด', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประเมินและรักษา', '1. ประเมินสมรรถภาพร่างกายและความสามารถในการทำกิจกรรม
2. วางแผนและให้การรักษาด้วยกายภาพบำบัด
3. ใช้เครื่องมือกายภาพบำบัด เช่น Ultrasound, TENS, Traction', 1
  FROM jd_template WHERE position_code = 'moph_physio' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการฟื้นฟูและส่งเสริมสุขภาพ', '1. ให้การฝึกเดิน ฝึกการทรงตัว และฝึกกิจวัตรประจำวัน
2. ให้ความรู้และฝึกญาติในการดูแลผู้ป่วยที่บ้าน', 2
  FROM jd_template WHERE position_code = 'moph_physio' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประสานงาน', '1. ประสานงานกับทีมสหวิชาชีพในการวางแผนฟื้นฟูผู้ป่วย', 3
  FROM jd_template WHERE position_code = 'moph_physio' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนาคุณภาพ', '1. ศึกษาหาความรู้และเทคนิคการรักษาใหม่ ๆ
2. บันทึกข้อมูลและจัดทำสถิติผลงานประจำเดือน', 4
  FROM jd_template WHERE position_code = 'moph_physio' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_physio' ORDER BY id DESC LIMIT 1;

-- ── 7. นักรังสีการแพทย์ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักรังสีการแพทย์', 'moph_radiology', 'RT-001', 'Senior',
   'กลุ่มงานรังสีวิทยา', 'fulltime',
   'ปฏิบัติงานด้านรังสีวิทยา เพื่อสนับสนุนการวินิจฉัยและการรักษาโรคของแพทย์อย่างมีคุณภาพและปลอดภัย',
   'ปริญญาตรีรังสีเทคนิค และได้รับใบอนุญาตประกอบวิชาชีพรังสีเทคนิค', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการถ่ายภาพรังสีและการตรวจพิเศษ', '1. ถ่ายภาพรังสีทั่วไป (X-Ray) ตามคำสั่งแพทย์
2. ปฏิบัติการตรวจด้วย CT Scan, MRI, Ultrasound
3. บันทึกและจัดเก็บภาพรังสีในระบบ PACS', 1
  FROM jd_template WHERE position_code = 'moph_radiology' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านความปลอดภัยทางรังสี', '1. ควบคุมป้องกันรังสีตามมาตรฐาน Radiation Protection
2. ดูแลบำรุงรักษาเครื่องมือและตรวจ QC ประจำวัน', 2
  FROM jd_template WHERE position_code = 'moph_radiology' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านรังสีรักษา', '1. เตรียมผู้ป่วยและจัดท่าฉายรังสีตามแผนการรักษา
2. บันทึก Dose และติดตามผลข้างเคียงของผู้ป่วย', 3
  FROM jd_template WHERE position_code = 'moph_radiology' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนาคุณภาพ', '1. พัฒนาเทคนิคการถ่ายภาพรังสีเพื่อลด Dose
2. อบรมนักศึกษารังสีเทคนิคฝึกงาน', 4
  FROM jd_template WHERE position_code = 'moph_radiology' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_radiology' ORDER BY id DESC LIMIT 1;

-- ── 8. นักโภชนาการ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักโภชนาการ', 'moph_nutritionist', 'NU-001', 'Senior',
   'กลุ่มงานโภชนศาสตร์', 'fulltime',
   'ให้บริการด้านโภชนาการคลินิกและสาธารณสุข เพื่อส่งเสริมสุขภาพและรักษาโรคที่เกี่ยวข้องกับอาหาร',
   'ปริญญาตรีโภชนาการหรือโภชนศาสตร์ หรือสาขาที่เกี่ยวข้อง', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านโภชนาการคลินิก', '1. ประเมินภาวะโภชนาการผู้ป่วยและวางแผนโภชนบำบัด
2. คำนวณและจัดทำสูตรอาหารทางหลอดเลือดดำและทางสายยาง
3. ให้คำปรึกษาด้านโภชนาการแก่ผู้ป่วยและครอบครัว', 1
  FROM jd_template WHERE position_code = 'moph_nutritionist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านโภชนาการสาธารณสุข', '1. สำรวจภาวะโภชนาการของประชาชนในพื้นที่
2. ดำเนินโครงการแก้ไขปัญหาทุพโภชนาการในกลุ่มเสี่ยง', 2
  FROM jd_template WHERE position_code = 'moph_nutritionist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการจัดการอาหารโรงพยาบาล', '1. วางแผนรายการอาหารสำหรับผู้ป่วยทั้งสามัญและอาหารพิเศษ
2. ควบคุมคุณภาพอาหารและสุขาภิบาลโรงครัว', 3
  FROM jd_template WHERE position_code = 'moph_nutritionist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนา', '1. ศึกษาวิจัยด้านโภชนาการ
2. จัดอบรมให้ความรู้แก่บุคลากรทางการแพทย์', 4
  FROM jd_template WHERE position_code = 'moph_nutritionist' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_nutritionist' ORDER BY id DESC LIMIT 1;

-- ── 9. นักสังคมสงเคราะห์ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักสังคมสงเคราะห์', 'moph_social_worker', 'SW-001', 'Senior',
   'กลุ่มงานสังคมสงเคราะห์', 'fulltime',
   'ให้บริการสังคมสงเคราะห์ทางการแพทย์ เพื่อดูแลมิติทางสังคม เศรษฐกิจ และจิตใจของผู้ป่วยและครอบครัว',
   'ปริญญาตรีสังคมสงเคราะห์ศาสตร์ และได้รับใบอนุญาตประกอบวิชาชีพสังคมสงเคราะห์', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการสังคมสงเคราะห์ทางการแพทย์', '1. ประเมินปัญหาด้านสังคม จิตใจ เศรษฐกิจ และสิ่งแวดล้อมของผู้ป่วย
2. ให้การช่วยเหลือและสนับสนุนทรัพยากรทางสังคมแก่ผู้ป่วยที่ขาดแคลน
3. ดำเนินการเรื่องสิทธิการรักษาพยาบาลและสวัสดิการต่าง ๆ', 1
  FROM jd_template WHERE position_code = 'moph_social_worker' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการคุ้มครองผู้ด้อยโอกาส', '1. ช่วยเหลือผู้ป่วยกลุ่มเปราะบาง เช่น เด็ก ผู้สูงอายุ ผู้พิการ
2. ประสานงานกับหน่วยงานภาครัฐและเอกชนในการให้ความช่วยเหลือ', 2
  FROM jd_template WHERE position_code = 'moph_social_worker' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประสานและส่งต่อ', '1. ประสานงานกับทีมสหวิชาชีพในการดูแลผู้ป่วยแบบองค์รวม
2. ส่งต่อผู้ป่วยไปยังหน่วยงานสังคมสงเคราะห์ในชุมชน
3. ติดตามเยี่ยมบ้านผู้ป่วยที่ต้องการการดูแลต่อเนื่อง', 3
  FROM jd_template WHERE position_code = 'moph_social_worker' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านวิชาการและพัฒนา', '1. จัดทำรายงานสถิติงานสังคมสงเคราะห์
2. ศึกษาวิจัยและพัฒนาคุณภาพงาน', 4
  FROM jd_template WHERE position_code = 'moph_social_worker' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_social_worker' ORDER BY id DESC LIMIT 1;

-- ── 10. นักวิชาการสาธารณสุข ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักวิชาการสาธารณสุข', 'moph_pub_health', 'PHA-001', 'Senior',
   'กลุ่มงานเวชปฏิบัติครอบครัวและชุมชน', 'fulltime',
   'ปฏิบัติงานด้านสาธารณสุขในชุมชน ส่งเสริมสุขภาพ ป้องกันและควบคุมโรค เพื่อให้ประชาชนมีสุขภาพดีอย่างยั่งยืน',
   'ปริญญาตรีสาธารณสุขศาสตร์ หรือสาขาที่เกี่ยวข้อง', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการส่งเสริมสุขภาพ', '1. วางแผนและดำเนินโครงการส่งเสริมสุขภาพในกลุ่มเป้าหมาย
2. จัดกิจกรรมให้ความรู้สุขศึกษาแก่ประชาชน โรงเรียน และชุมชน
3. สนับสนุนงานอนามัยแม่และเด็ก ผู้สูงอายุ และโภชนาการ', 1
  FROM jd_template WHERE position_code = 'moph_pub_health' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการเฝ้าระวังและควบคุมโรค', '1. เฝ้าระวังโรคติดต่อและโรคไม่ติดต่อในพื้นที่รับผิดชอบ
2. สอบสวนโรค รายงาน และดำเนินการควบคุมโรคระบาด
3. รายงาน 506/507 ตามระบบเฝ้าระวังโรคแห่งชาติ', 2
  FROM jd_template WHERE position_code = 'moph_pub_health' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการวางแผนและวิเคราะห์', '1. วิเคราะห์ข้อมูลสุขภาพชุมชนและจัดทำ Community Health Profile
2. วางแผนและจัดทำแผนสาธารณสุขประจำปี', 3
  FROM jd_template WHERE position_code = 'moph_pub_health' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประสานงาน', '1. ประสานงานกับ อสม. ผู้นำชุมชน และภาคีเครือข่ายด้านสุขภาพ', 4
  FROM jd_template WHERE position_code = 'moph_pub_health' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการรายงานและเอกสาร', '1. บันทึกข้อมูลในระบบ HDC, 43 แฟ้ม และระบบรายงานอื่น ๆ
2. จัดทำรายงานผลการดำเนินงานประจำเดือน/ไตรมาส', 5
  FROM jd_template WHERE position_code = 'moph_pub_health' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 6
  FROM jd_template WHERE position_code = 'moph_pub_health' ORDER BY id DESC LIMIT 1;

-- ── 11. เจ้าพนักงานสาธารณสุข ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('เจ้าพนักงานสาธารณสุข', 'moph_health_officer', 'PHO-001', 'Junior',
   'กลุ่มงานเวชปฏิบัติครอบครัวและชุมชน', 'fulltime',
   'ปฏิบัติงานสาธารณสุขระดับปฏิบัติการ สนับสนุนงานส่งเสริมสุขภาพ ป้องกันโรค และให้บริการสุขภาพเบื้องต้น',
   'ประกาศนียบัตรวิชาชีพชั้นสูงสาธารณสุขชุมชน หรือเทียบเท่า', 0, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการให้บริการสุขภาพเบื้องต้น', '1. ให้บริการตรวจรักษาโรคเบื้องต้นและปฐมพยาบาล
2. ดูแลผู้ป่วยโรคเรื้อรัง เช่น ความดันโลหิตสูง เบาหวาน
3. ให้วัคซีนและดูแลสุขภาพตามวัย', 1
  FROM jd_template WHERE position_code = 'moph_health_officer' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการส่งเสริมสุขภาพและป้องกันโรค', '1. ออกเยี่ยมบ้านผู้ป่วยและกลุ่มเสี่ยง
2. สนับสนุนงาน อสม. และสร้างเสริมความเข้มแข็งของชุมชน
3. ดำเนินงานอนามัยสิ่งแวดล้อม', 2
  FROM jd_template WHERE position_code = 'moph_health_officer' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการเฝ้าระวังโรค', '1. เฝ้าระวังและรายงานโรคติดต่อในชุมชน
2. ร่วมสอบสวนโรคกับทีมสาธารณสุข', 3
  FROM jd_template WHERE position_code = 'moph_health_officer' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการบันทึกและรายงาน', '1. บันทึกข้อมูลผู้รับบริการในระบบ HIS และ 43 แฟ้ม
2. จัดทำรายงานผลการดำเนินงานประจำเดือน', 4
  FROM jd_template WHERE position_code = 'moph_health_officer' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_health_officer' ORDER BY id DESC LIMIT 1;

-- ── 12. นักวิเคราะห์นโยบายและแผน ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักวิเคราะห์นโยบายและแผน', 'moph_policy', 'PA-001', 'Senior',
   'กลุ่มงานยุทธศาสตร์และแผนงาน', 'fulltime',
   'วิเคราะห์ จัดทำ และขับเคลื่อนนโยบาย แผนงาน และโครงการ เพื่อพัฒนาระบบสุขภาพของหน่วยงาน',
   'ปริญญาตรีสาขารัฐศาสตร์ สาธารณสุขศาสตร์ บริหารสาธารณสุข หรือสาขาที่เกี่ยวข้อง', 1, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการวิเคราะห์นโยบายและแผน', '1. วิเคราะห์สถานการณ์ด้านสุขภาพและบริบทขององค์กร
2. จัดทำแผนยุทธศาสตร์ แผนปฏิบัติราชการ และแผนประจำปี
3. วิเคราะห์นโยบายจากระดับกระทรวงและแปลงสู่การปฏิบัติ', 1
  FROM jd_template WHERE position_code = 'moph_policy' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการติดตามและประเมินผล', '1. ติดตามความก้าวหน้าการดำเนินงานตามแผนและตัวชี้วัด
2. จัดทำรายงานผลการดำเนินงานประจำปี
3. เสนอแนะแนวทางปรับปรุงและพัฒนาการดำเนินงาน', 2
  FROM jd_template WHERE position_code = 'moph_policy' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการจัดทำเอกสารและรายงาน', '1. จัดทำรายงานสรุปผลนำเสนอผู้บริหาร
2. บริหารจัดการฐานข้อมูลแผนงานขององค์กร', 3
  FROM jd_template WHERE position_code = 'moph_policy' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประสานงาน', '1. ประสานงานกับหน่วยงานภายใน/ภายนอกในการดำเนินงานตามแผน', 4
  FROM jd_template WHERE position_code = 'moph_policy' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_policy' ORDER BY id DESC LIMIT 1;

-- ── 13. นักทรัพยากรบุคคล ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักทรัพยากรบุคคล', 'moph_hr', 'HR-001', 'Senior',
   'กลุ่มงานทรัพยากรบุคคล', 'fulltime',
   'บริหารจัดการทรัพยากรบุคคลขององค์กร ตั้งแต่การสรรหา บรรจุแต่งตั้ง พัฒนา และรักษาบุคลากรที่มีคุณภาพ',
   'ปริญญาตรีสาขาบริหารทรัพยากรบุคคล รัฐประศาสนศาสตร์ หรือสาขาที่เกี่ยวข้อง', 1, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการสรรหาและบรรจุแต่งตั้ง', '1. วางแผนอัตรากำลังและการสรรหาบุคลากร
2. ดำเนินการสอบแข่งขัน คัดเลือก และบรรจุแต่งตั้ง
3. จัดทำคำสั่งแต่งตั้ง โอนย้าย และออกจากราชการ', 1
  FROM jd_template WHERE position_code = 'moph_hr' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการพัฒนาบุคลากร', '1. จัดทำแผนพัฒนาบุคลากร (Training & Development Plan)
2. ดำเนินการฝึกอบรม สัมมนา และพัฒนาทักษะ
3. สนับสนุนการศึกษาต่อและทุนการศึกษา', 2
  FROM jd_template WHERE position_code = 'moph_hr' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการประเมินผลการปฏิบัติงาน', '1. บริหารระบบประเมินผลการปฏิบัติงาน (PMS)
2. ดำเนินการเลื่อนขั้นเงินเดือนและพิจารณาโบนัส', 3
  FROM jd_template WHERE position_code = 'moph_hr' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านสวัสดิการและแรงงานสัมพันธ์', '1. บริหารจัดการสวัสดิการบุคลากร
2. ดูแลระบบลาและการจ่ายค่าตอบแทน
3. รับเรื่องร้องเรียนและแก้ไขปัญหาแรงงานสัมพันธ์', 4
  FROM jd_template WHERE position_code = 'moph_hr' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านฐานข้อมูลและรายงาน', '1. ดูแลระบบฐานข้อมูลบุคลากร (HRIS)
2. จัดทำรายงานสถิติและอัตรากำลัง', 5
  FROM jd_template WHERE position_code = 'moph_hr' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 6
  FROM jd_template WHERE position_code = 'moph_hr' ORDER BY id DESC LIMIT 1;

-- ── 14. นักจัดการงานทั่วไป ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักจัดการงานทั่วไป', 'moph_admin', 'GA-001', 'Senior',
   'กลุ่มงานบริหารทั่วไป', 'fulltime',
   'บริหารจัดการงานธุรการ งานสารบรรณ งานพัสดุ และสนับสนุนการบริหารงานขององค์กรให้มีประสิทธิภาพ',
   'ปริญญาตรีสาขาบริหารธุรกิจ รัฐประศาสนศาสตร์ หรือสาขาที่เกี่ยวข้อง', 1, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการบริหารและธุรการ', '1. บริหารจัดการงานสารบรรณ รับ-ส่ง และจัดเก็บเอกสาร
2. จัดเตรียมการประชุม บันทึกและสรุปรายงานการประชุม
3. ประสานงานภายในและภายนอกองค์กร', 1
  FROM jd_template WHERE position_code = 'moph_admin' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านพัสดุและครุภัณฑ์', '1. จัดซื้อจัดจ้างพัสดุตาม พ.ร.บ. จัดซื้อจัดจ้างฯ
2. ตรวจรับพัสดุและควบคุมทะเบียนทรัพย์สิน
3. จัดทำแผนการจัดซื้อจัดจ้างประจำปี', 2
  FROM jd_template WHERE position_code = 'moph_admin' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการเงินและงบประมาณ', '1. สนับสนุนการจัดทำแผนงบประมาณประจำปี
2. ดำเนินการเบิก-จ่ายเงินตามระเบียบราชการ', 3
  FROM jd_template WHERE position_code = 'moph_admin' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านอาคารสถานที่', '1. ดูแลบำรุงรักษาอาคารสถานที่และสิ่งอำนวยความสะดวก
2. บริหารจัดการยานพาหนะของหน่วยงาน', 4
  FROM jd_template WHERE position_code = 'moph_admin' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_admin' ORDER BY id DESC LIMIT 1;

-- ── 15. นักวิชาการคอมพิวเตอร์ ──
INSERT INTO jd_template
  (name, position_code, job_code, job_level, department, employment_type,
   job_purpose, edu_requirement, exp_years, is_active, created_at, updated_at)
VALUES
  ('นักวิชาการคอมพิวเตอร์', 'moph_it', 'IT-001', 'Senior',
   'กลุ่มงานเทคโนโลยีสารสนเทศ', 'fulltime',
   'ออกแบบ พัฒนา และดูแลระบบเทคโนโลยีสารสนเทศของโรงพยาบาล เพื่อสนับสนุนการให้บริการและการบริหารงาน',
   'ปริญญาตรีวิทยาการคอมพิวเตอร์ เทคโนโลยีสารสนเทศ หรือสาขาที่เกี่ยวข้อง', 1, 1, NOW(), NOW());

INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการพัฒนาและดูแลระบบ', '1. พัฒนาและดูแลระบบ HIS (Hospital Information System)
2. ออกแบบและพัฒนาโปรแกรมและระบบฐานข้อมูล
3. ดูแลและบำรุงรักษาเครือข่าย Server และอุปกรณ์ IT', 1
  FROM jd_template WHERE position_code = 'moph_it' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านความมั่นคงปลอดภัยข้อมูล', '1. กำหนดนโยบายและมาตรการรักษาความปลอดภัยระบบ IT
2. ดำเนินการ Backup ข้อมูลและ Disaster Recovery Plan
3. ดำเนินการตาม PDPA และ Cybersecurity Act', 2
  FROM jd_template WHERE position_code = 'moph_it' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการจัดการข้อมูล', '1. ดูแลระบบฐานข้อมูล 43 แฟ้ม และ HDC
2. จัดทำรายงานสถิติและ Dashboard สำหรับผู้บริหาร', 3
  FROM jd_template WHERE position_code = 'moph_it' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานด้านการสนับสนุนผู้ใช้', '1. จัดอบรมการใช้งานระบบสารสนเทศแก่บุคลากร
2. รับแจ้งและแก้ไขปัญหา IT ผ่าน Helpdesk', 4
  FROM jd_template WHERE position_code = 'moph_it' ORDER BY id DESC LIMIT 1;
INSERT INTO jd_template_section (template_id, title, content, sort_order)
  SELECT id, 'งานอื่น ๆ ตามที่ได้รับมอบหมาย', 'ปฏิบัติงานอื่น ๆ ตามที่ผู้บังคับบัญชามอบหมาย', 5
  FROM jd_template WHERE position_code = 'moph_it' ORDER BY id DESC LIMIT 1;
