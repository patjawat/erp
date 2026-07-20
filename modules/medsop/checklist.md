# MedSOP PRO Development Checklist

อัปเดตล่าสุด: 18 กรกฎาคม 2569

## การส่งต่อให้ทีมทดสอบ

- รัน migration ด้วย `php yii migrate/up --migrationPath=@app/migrations --interactive=0`
- Migration ของ MedSOP ถูก apply ในฐานข้อมูล development แล้ว และตรวจซ้ำพบว่าไม่มี migration ค้าง
- เอกสารทดสอบ ID 4 อยู่สถานะ `PUBLISHED` พร้อมผู้รับ 5 คน รวม employee ID 141 สำหรับทดสอบการเปิดอ่านและลงชื่อรับทราบ
- ก่อนขึ้น production ให้ทีมทดสอบสิทธิ์ผู้ดูแล/ผู้รับเอกสาร, การเปิดอ่าน, การลงชื่อรับทราบ, Slide view และการส่งออก PDF อีกครั้งใน environment เป้าหมาย

## ขอบเขตที่อนุญาต

- [x] แก้ไขได้เฉพาะ `modules/medsop/**`
- [x] แก้ไข `config/**` ได้เฉพาะการลงทะเบียน MedSOP
- [x] เพิ่ม `migrations/**` ได้เฉพาะฐานข้อมูล MedSOP
- [x] โมดูลอื่นใช้สำหรับอ่านและเชื่อมต่อเท่านั้น ห้ามแก้ไข
- [x] UX/UI ต้องปฏิบัติตาม `PRODUCT.md` อย่างเคร่งครัด

## ระยะที่ 1: สำรวจระบบและออกแบบ

- [x] ตรวจสอบ `Employees` และความสัมพันธ์ `user_id`, `department`
- [x] ตรวจสอบ `Organization` ซึ่งใช้ตาราง `tree`
- [x] ตรวจสอบโครงสร้าง `approveV2` และตาราง `approve`
- [x] ตรวจสอบ `filemanager` และตาราง `uploads`
- [x] จัดทำ Data Mapping ฉบับใช้งานจริง
- [x] จัดทำ Permission Matrix ฉบับใช้งานจริง
- [x] จัดทำ Workflow และ Revision Policy ฉบับใช้งานจริง

## ระยะที่ 2: โครงสร้างระบบ

- [x] สร้าง Module และลงทะเบียนใน config
- [x] สร้าง Migration ตารางเอกสาร ขั้นตอน Revision และการตั้งค่า
- [x] นำ Migration ของ MedSOP เข้า database `erp` หลังคืนข้อมูลวันที่ 14 กรกฎาคม 2569
- [x] สร้าง ActiveRecord และ Search Model
- [x] สร้าง Service สำหรับเอกสาร สิทธิ์ การอนุมัติ และไฟล์
- [x] สร้าง Controller พร้อม server-side access control ระยะแรก

## ระยะที่ 3: หน้าจอ

- [x] คลังเอกสารและตัวกรอง
- [x] Desktop table และ Mobile card list
- [x] ฟอร์มสร้าง/แก้ไขพร้อม Dynamic Steps
- [x] SweetAlert2 ยืนยันก่อนบันทึก แสดง loading ตามผลจริง และ redirect ไปหน้า View หลังสำเร็จ
- [x] หน้ารายละเอียดและ Steps Timeline
- [x] สถานะ Access Denied
- [x] เพิ่มเมนู “คลัง SOP/WI” ใน navigation หลัก
- [ ] Admin Console และ KPI
- [ ] Empty, loading, validation และ error states

## ระยะที่ 4: Integration

- [x] เชื่อม Employees กับผู้สร้างและผู้แก้ไข
- [x] เชื่อม Organization กับแผนกเจ้าของเอกสาร
- [ ] เชื่อมคำขออนุมัติกับ `approveV2` (สร้าง Adapter แล้ว รอหน้ากำหนดผู้อนุมัติและ callback สถานะ)
- [ ] เชื่อมไฟล์และเอกสารแนบกับ `filemanager` (สร้าง Adapter และ `file_ref` แล้ว รอ UI upload/proxy download)
- [ ] รองรับ Revision เมื่อแก้เอกสารที่เผยแพร่แล้ว

## ระยะที่ 5: คุณภาพและการทดสอบ

- [ ] ทดสอบ CRUD และ transaction rollback
- [ ] ทดสอบลำดับ Dynamic Steps
- [ ] ทดสอบ Workflow อนุมัติ ส่งกลับ และเผยแพร่
- [ ] ทดสอบสิทธิ์ Admin, Director, HR/QA และ General User
- [ ] ทดสอบการเปิด URL โดยตรงและการเข้าถึงต่างแผนก
- [ ] ทดสอบ file access และ Revision ย้อนหลัง
- [ ] ตรวจ N+1 และประสิทธิภาพ Query
- [ ] ตรวจ Responsive, WCAG 2.1 AA และ keyboard navigation
- [ ] ตรวจ UI ตาม `PRODUCT.md`

## หมายเหตุการสำรวจ

- `Employees::tableName()` ใช้ข้อมูลบุคลากร และฟิลด์ `department` อ้างอิง `Organization::id`
- `Organization::tableName()` คืนค่า `tree` และใช้ nested set
- `approveV2` ใช้ตาราง `approve` โดยเชื่อมเอกสารด้วย `name` และ `from_id`
- สถานะที่พบใน `approveV2` ได้แก่ `Pending`, `Pass`, `Reject` และ `None`
- `filemanager` ใช้ตาราง `uploads` และเชื่อมไฟล์ด้วย `ref` กับ `name`
- การลบของ `filemanager` เป็น hard delete ทั้ง record และไฟล์จริง จึงห้ามเรียกโดยตรงกับไฟล์ที่ Revision ยังอ้างอิง
