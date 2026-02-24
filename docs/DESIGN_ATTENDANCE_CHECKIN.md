# ออกแบบระบบบันทึกเวลาเข้างาน (Check-in)

## ภาพรวม

ระบบบันทึกเวลาเข้างาน (Attendance Check-in) รองรับการลงเวลา 3 แบบ ได้แก่ **สแกน QR Code**, **ถ่ายรูป Check-in** และ **กดลงเวลาแบบ Manual** พร้อมตรวจสอบพิกัด (ถ้านอกบริเวณต้องกรอกเหตุผล) และให้หัวหน้าอนุมัติหลังลงเวลา สามารถนำเข้าข้อมูลจาก CSV ได้ และในอนาคตเชื่อมกับระบบตารางเวร (work_shift) ได้

---

## ความสามารถหลัก

| ความสามารถ | รายละเอียด |
|------------|------------|
| **สแกน QR Code** | สแกน QR ที่จุดลงเวลา → ระบบบันทึกเวลาและพิกัด (ถ้ามี) |
| **ถ่ายรูป Check-in** | ถ่ายรูป selfie/สถานที่ → อัปโหลดเป็นหลักฐานแนบกับรายการลงเวลา |
| **กดลงเวลา** | ปุ่มลงเวลาโดยไม่ต้องสแกนหรือถ่ายรูป (Manual) |
| **ตรวจสอบบริเวณ** | กำหนดบริเวณที่อนุญาต (พิกัด + รัศมี) ถ้าลงเวลานอกบริเวณ → บังคับกรอกเหตุผล |
| **บันทึกพิกัด** | บันทึก latitude, longitude ทุกครั้งที่ลงเวลา (จาก Browser/App) |
| **อนุมัติโดยหัวหน้า** | หลัง Check-in สร้างรายการในระบบ Approve ให้หัวหน้าอนุมัติ (ใช้ตาราง `approve` เดิม) |
| **นำเข้า CSV** | นำเข้าข้อมูลลงเวลาจากไฟล์ CSV (วันที่, เวลา, รหัสพนักงาน ฯลฯ) |
| **เชื่อมตารางเวร** | (ระยะที่ 2) เชื่อมกับ `employees.work_shift` และตารางเวรเพื่อตรวจช่วงเวลาที่ลงเวลาได้ |

---

## โครงสร้างฐานข้อมูล

### 1. ตาราง `checkin_location` (บริเวณที่อนุญาตให้ลงเวลา)

| คอลัมน์ | ประเภท | คำอธิบาย |
|--------|--------|----------|
| id | PK | |
| name | string | ชื่อจุด/บริเวณ (เช่น "อาคาร A", "ที่ทำการหลัก") |
| lat | decimal(10,7) | Latitude ศูนย์กลางบริเวณ |
| lng | decimal(10,7) | Longitude ศูนย์กลางบริเวณ |
| radius_m | int | รัศมีเป็นเมตร (ถ้า 0 = ไม่บังคับตรวจพิกัด) |
| qr_token | string, unique | ค่า QR ที่สแกนแล้วถือว่าอยู่ที่จุดนี้ (nullable) |
| active | tinyint | 1=ใช้งาน 0=ปิด |
| created_at, updated_at, created_by, updated_by | | |

- ถ้า `radius_m > 0` ระบบจะตรวจว่า lat/lng ของผู้ลงเวลาอยู่ในวงกลมนี้หรือไม่
- ถ้านอกบริเวณ → บังคับกรอก `out_of_location_reason` ใน `checkin_record`

### 2. ตาราง `checkin_record` (บันทึกการลงเวลา)

| คอลัมน์ | ประเภท | คำอธิบาย |
|--------|--------|----------|
| id | PK | |
| emp_id | int, FK→employees.id | พนักงานที่ลงเวลา |
| checkin_at | datetime | วันเวลาที่ลงเวลา |
| method | enum('qrcode','photo','manual') | วิธีลงเวลา |
| lat | decimal(10,7), null | พิกัด Latitude |
| lng | decimal(10,7), null | พิกัด Longitude |
| location_id | int, null, FK→checkin_location | จุดที่ลง (จาก QR หรือการจับคู่บริเวณ) |
| is_in_location | tinyint | 1=อยู่ในบริเวณ 0=นอกบริเวณ |
| out_of_location_reason | text, null | เหตุผลเมื่อลงเวลานอกบริเวณ (บังคับถ้า is_in_location=0) |
| photo_path | string, null | path รูปถ่าย (เมื่อ method=photo) |
| qr_token | string, null | ค่า QR ที่สแกน (เมื่อ method=qrcode) |
| data_json | json, null | ข้อมูลเพิ่ม (device, user_agent ฯลฯ) |
| status | enum('pending','approved','rejected') | สถานะการอนุมัติ (pending=รอหัวหน้า) |
| approved_by | int, null | ผู้อนุมัติ (emp_id) |
| approved_at | datetime, null | เวลาอนุมัติ |
| comment | text, null | ความเห็นจากผู้อนุมัติ |
| created_at, updated_at, created_by, updated_by | | |

- หลังสร้าง `checkin_record` จะสร้าง record ในตาราง `approve` โดย `name='checkin'`, `from_id=checkin_record.id`, `level=1` (หัวหน้าอนุมัติ)
- สถานะ `status` อัปเดตตามผลการอนุมัติ (Pass → approved, Not pass → rejected)

---

## การทำงานของระบบ

### การลงเวลา

1. **QR Code**  
   - ผู้ใช้สแกน QR ที่จุดลงเวลา → ระบบอ่าน `qr_token` จับคู่กับ `checkin_location` (ถ้ามี)  
   - ได้พิกัดจาก Browser/App (ถ้าอนุญาต) → ตรวจสอบว่าอยู่ใน `radius_m` หรือไม่  
   - บันทึก `checkin_record` (method=qrcode, location_id, lat, lng, is_in_location, out_of_location_reason ถ้านอกบริเวณ)

2. **ถ่ายรูป**  
   - ผู้ใช้ถ่ายรูปแล้วอัปโหลด → บันทึก path ลง `photo_path`  
   - ได้พิกัดจาก Browser → ตรวจบริเวณเหมือนด้านบน  
   - บันทึก `checkin_record` (method=photo, photo_path, lat, lng, …)

3. **Manual**  
   - กดปุ่ม "ลงเวลา" โดยไม่สแกน/ไม่ถ่ายรูป  
   - ได้พิกัดจาก Browser (ถ้ามี) → ตรวจบริเวณ  
   - บันทึก `checkin_record` (method=manual, lat, lng, …)

4. **กรณีนอกบริเวณ**  
   - ถ้า `is_in_location = 0` ระบบบังคับให้กรอก `out_of_location_reason` ก่อนบันทึก

5. **หลังบันทึก**  
   - สร้างรายการใน `approve` (name='checkin', from_id=id ของ checkin_record, level=1)  
   - หัวหน้าเห็นรายการในหน้ารวมอนุมัติ (approve-v2) และอนุมัติ/ไม่อนุมัติได้

### การอนุมัติ

- ใช้โมดูล approve-v2 เดิม เพิ่มแท็บ/เมนู "ลงเวลา" (checkin)
- เมื่อหัวหน้าอนุมัติ (Pass/Not pass) → อัปเดต `checkin_record.status` (approved/rejected) และ `approved_by`, `approved_at`, `comment`

### นำเข้า CSV

- ฟอร์มอัปโหลดไฟล์ CSV
- รูปแบบคอลัมน์ตัวอย่าง: `emp_id หรือ code, checkin_at (Y-m-d H:i:s), method, lat, lng, out_of_location_reason, ...`
- อ่านแถวแล้วสร้าง `checkin_record` (และสร้าง approve ถ้าต้องการให้รออนุมัติเหมือนลงเวลาปกติ)

### การเชื่อมตารางเวร (ระยะที่ 2)

- ใช้ฟิลด์ `employees.work_shift` และตารางเวร (ถ้ามี) เพื่อ:
  - ตรวจว่าในวันนั้นพนักงานอยู่เวรไหน
  - เปรียบเทียบ `checkin_at` กับช่วงเวลาเวร (เข้างานตรงเวลา/สาย/ฯลฯ)
- จะออกแบบรายละเอียดเมื่อระบบ Check-in หลักและ Approve ทำงานครบแล้ว

---

## โครงสร้าง Module (Yii2)

- **Module name:** `attendance`
- **Namespace:** `app\modules\attendance`
- **Controllers**
  - `DefaultController`: หน้าแรก/แดชบอร์ด, หน้ากดลงเวลา (QR/Photo/Manual)
  - `CheckinController`: รายการลงเวลา (index, view), นำเข้า CSV
  - `LocationController`: จัดการจุดลงเวลา (checkin_location) CRUD
- **Models**
  - `CheckinLocation`
  - `CheckinRecord`
  - `CheckinRecordSearch`
- **Integrations**
  - `approve` table: name=`checkin`, from_id = checkin_record.id
  - `ApproveHelper`: เพิ่มประเภท checkin ใน Info() และใน tab_menu ของ approve-v2
  - เมนู "ลงเวลา" ใน me หรือ HR ตามนโยบาย

---

## ไฟล์ที่เกี่ยวข้อง

- Migration: สร้าง `checkin_location`, `checkin_record`
- Module: `modules/attendance/`
- Config: ลงทะเบียน module ใน `config/add_modules.php`
- Approve: แก้ไข `ApproveHelper::Info()` และ `approveV2` tab_menu เพื่อรวมรายการ checkin

---

## สรุป

- ลงเวลาได้ 3 แบบ: **QR**, **ถ่ายรูป**, **Manual**
- ตรวจสอบ**บริเวณ**จากพิกัดและรัศมี; นอกบริเวณต้อง**กรอกเหตุผล**
- บันทึก**พิกัด** (lat/lng) ได้
- หลังลงเวลา **หัวหน้าอนุมัติ** ผ่านระบบ approve เดิม
- **นำเข้า CSV** ได้
- **เชื่อมตารางเวร** จะทำในระยะที่ 2 หลังระบบ Check-in และ Approve ใช้งานได้ครบ
