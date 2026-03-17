# โมดูล amSurvey — การสำรวจครุภัณฑ์ประจำปี

โมดูลสำหรับการสำรวจครุภัณฑ์ประจำปี (Annual Government Asset Survey) ทำงานร่วมกับโมดูล `am` โดยไม่แก้ไข core ของ am

## การติดตั้ง

1. รัน migration:
   ```bash
   php yii migrate
   ```
   (เลือก migrations ที่ขึ้นต้นด้วย `m260314_120000` ... `m260314_120002`)

2. โมดูลลงทะเบียนใน `config/add_modules.php` แล้ว

## URL หลัก

| URL | คำอธิบาย |
|-----|----------|
| `/am-survey/default/dashboard` | แดชบอร์ดสรุปและเลือกโครงการสำรวจ |
| `/am-survey/survey/index` | รายการโครงการสำรวจ |
| `/am-survey/survey/create` | สร้างโครงการสำรวจ |
| `/am-survey/scan/index` | สำรวจผ่าน Web (ค้นหา + ยืนยันที่ตั้ง/หน่วยงาน) |
| `/am-survey/import/index?survey_id=X` | นำเข้า CSV ตามโครงการ |
| `/am-survey/report/summary?survey_id=X` | รายงานสรุป |
| `/am-survey/report/missing?survey_id=X` | รายการครุภัณฑ์ไม่พบ |
| `/am-survey/report/relocated?survey_id=X` | รายการย้ายที่/หน่วยงานไม่ตรง |

## API สำหรับมือถือ (QR Scan)

- **POST** `/mobile/survey/scan`
- พารามิเตอร์: `asset_number`, `survey_id`, `department_id` (optional), `survey_location` (optional)
- ส่งกลับ JSON: `success`, `message`, `found_status`, `location_match`, `department_match`

## โครงสร้างตาราง

- **am_asset_surveys** — โครงการสำรวจ (ปี, ชื่อ, สถานะ)
- **am_asset_survey_items** — ผลสำรวจแต่ละรายการ (FOUND/NOT_FOUND/NEW_ASSET, location_match, department_match)
- **am_asset_survey_logs** — บันทึกการเปลี่ยนสถานที่/หน่วยงาน

## วิธีสำรวจ

1. **CSV** — อัปโหลดไฟล์ CSV (คอลัมน์แรกเป็นหมายเลขครุภัณฑ์) ระบบจับคู่กับ asset.code / asset.fsn_number และสร้าง survey items พร้อมสถานะ
2. **Web** — เลือกโครงการ → ค้นหาหมายเลขครุภัณฑ์ → ยืนยันสถานที่/หน่วยงาน → บันทึก
3. **QRCODE** — เรียก API จากแอปมือถือหลังสแกน QR

## การเปรียบเทียบกับระบบ

- **FOUND** — พบครุภัณฑ์ในระบบ
- **NOT_FOUND** — ไม่พบหมายเลขในระบบ
- **location_match** — สถานที่ตรงกับที่บันทึก (data_json.location)
- **department_match** — หน่วยงานตรงกับ asset.department
