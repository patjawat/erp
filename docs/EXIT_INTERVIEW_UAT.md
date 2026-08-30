# Exit Interview — UAT Checklist

สถานะ: รอผู้อนุมัติทดสอบ

เอกสารนี้ใช้ทดสอบ branch `codex/fix-exit-interview-readiness` หลังติดตั้ง migration ในสภาพแวดล้อมทดสอบเท่านั้น ห้ามใช้ข้อมูลบุคลากรจริงที่อ่อนไหวหากสภาพแวดล้อมยังไม่ได้รับอนุญาต

## ผู้ทดสอบและบัญชี

- ผู้ดูแลระบบ: มีสิทธิ Exit Interview ครบทุกสิทธิ
- HR ผู้จัดการรายการ: `exitInterviewManage`
- HR ผู้ดูคำตอบระบุตัวตน: `exitInterviewViewIdentified`
- HR ผู้ดู Analytics: `exitInterviewViewAnalytics`
- HR ผู้จัดการแบบสอบถาม: `exitInterviewManageTemplate`
- HR ผู้นำเข้า/ส่งออก: `exitInterviewImport`, `exitInterviewExportIdentified`
- พนักงานทดสอบ: ไม่มีสิทธิ HR และมี Telegram ID ทดสอบหากต้องทดสอบการส่งข้อความ
- บุคคลภายนอกระบบ: เปิด public link แบบไม่เข้าสู่ระบบ

ใช้บัญชีแยกกันจริงสำหรับแต่ละบทบาท ห้ามทดสอบ permission matrix ด้วยบัญชี admin เพียงบัญชีเดียว

## ข้อมูลทดสอบขั้นต่ำ

- พนักงานทดสอบอย่างน้อย 6 คน เพื่อทดสอบเกณฑ์ปกปิด Analytics ที่กลุ่มน้อยกว่า 5 คน
- อย่างน้อย 2 หน่วยงาน
- รายการลาออกทั้ง `pending`, `draft` และ `submitted`
- แบบสอบถาม published 1 เวอร์ชัน และ draft 1 เวอร์ชัน
- คำถาม rating, single choice, ranking, short text และ long text

## UAT-01 สิทธิ์และเมนู

- [ ] ผู้ไม่มีสิทธิ Exit Interview ไม่เห็นเมนูและเข้าหน้าโดย URL ตรงไม่ได้
- [ ] `exitInterviewManage` เปิดทะเบียนและสร้างรายการได้ แต่ไม่เห็นข้อมูลที่สิทธิอื่นไม่อนุญาต
- [ ] `exitInterviewViewIdentified` เปิดคำตอบระบุตัวตนได้
- [ ] `exitInterviewViewAnalytics` เปิด Dashboard Analytics ได้
- [ ] `exitInterviewManageTemplate` จัดการแบบสอบถามได้
- [ ] `exitInterviewImport` ดาวน์โหลด template และนำเข้าได้
- [ ] `exitInterviewExportIdentified` ส่งออกข้อมูลระบุตัวตนได้

ผลที่คาดหวัง: ทุกหน้าปฏิเสธด้วย HTTP 403 เมื่อไม่มีสิทธิ และ UI ไม่แสดง action ที่ใช้ไม่ได้

## UAT-02 สร้างและแก้ไขรายการ

- [ ] HR สร้างรายการจากพนักงานที่มีอยู่ได้
- [ ] ชื่อ หน่วยงาน ตำแหน่ง ประเภทบุคลากร และวันที่เริ่มงานถูก snapshot
- [ ] บันทึกร่างแล้วกลับมาแก้ต่อได้
- [ ] ส่งคำตอบแล้วสถานะเป็น `submitted` และมีเวลา `submitted_at`
- [ ] การแก้คำตอบที่ส่งแล้วต้องระบุเหตุผล
- [ ] Audit log บันทึกผู้แก้ เวลา ค่าเดิม ค่าใหม่ และเหตุผล

## UAT-03 Public link และความเป็นส่วนตัว

- [ ] สร้างลิงก์ได้เฉพาะรายการสถานะที่อนุญาต
- [ ] อายุลิงก์ไม่เกิน 90 วัน
- [ ] การสร้างลิงก์ใหม่ทำให้ลิงก์เดิมใช้ไม่ได้
- [ ] token ปลอม หมดอายุ ถูกถอน หรือส่งแล้ว เปิดแบบสอบถามไม่ได้
- [ ] ผู้ไม่เข้าสู่ระบบตอบและบันทึกร่างผ่านลิงก์ได้
- [ ] คำถาม `is_hr_only` ไม่ปรากฏใน public form และไม่สามารถส่งค่าปลอมเข้ามาได้
- [ ] ต้องให้ consent ก่อน submit
- [ ] หลัง submit ลิงก์ใช้ส่งคำตอบซ้ำไม่ได้
- [ ] หน้า error สาธารณะไม่แสดง stack trace, SQL หรือรายละเอียด exception ภายใน

## UAT-04 Validation

- [ ] คำถาม required ว่างไม่ได้เมื่อ submit
- [ ] rating ต่ำกว่า/สูงกว่าช่วงที่กำหนดถูกปฏิเสธ
- [ ] single choice รับเฉพาะ option ของคำถามนั้น
- [ ] multi choice/ranking รับเฉพาะ option ของคำถามนั้น
- [ ] ranking ห้ามเลือกซ้ำและห้ามเกิน `max_selections`
- [ ] ไม่สามารถส่ง question ID จาก template หรือ version อื่นได้
- [ ] ข้อมูลไม่ผ่าน validation ไม่ถูกบันทึกบางส่วน

## UAT-05 Template lifecycle

- [ ] clone จาก published version เป็น draft ใหม่ได้ครบ section, question และ option
- [ ] แก้คำถามได้เฉพาะ draft
- [ ] publish ไม่สำเร็จเมื่อโครงสร้างแบบสอบถามไม่สมบูรณ์
- [ ] publish สำเร็จแล้ว version เดิมเปลี่ยนเป็น retired
- [ ] รายการเดิมยังอ้างอิง version เดิมและแสดงคำถามได้ถูกต้อง

## UAT-06 Import และ Export

- [ ] ดาวน์โหลด Excel template ได้เมื่อมี published version
- [ ] หากไม่มี published version ระบบแจ้งข้อความที่เข้าใจได้และไม่เกิด PHP error
- [ ] header ว่างหรือซ้ำถูกปฏิเสธ
- [ ] แถวที่มีจำนวนคอลัมน์เกิน header ไม่ทำให้ระบบล่ม
- [ ] import หลายแถวรายงานจำนวนสำเร็จและข้อผิดพลาดแยกรายแถว
- [ ] เมื่อแถวหนึ่งผิด transaction ของแถวนั้นไม่ทิ้งข้อมูลครึ่งรายการ
- [ ] CSV export ป้องกัน formula injection สำหรับค่าที่ขึ้นต้นด้วย `=`, `+`, `-`, `@`

## UAT-07 Analytics privacy

- [ ] Dashboard แสดงเฉพาะรายการสถานะ submitted
- [ ] ตัวกรองวันที่ หน่วยงาน และประเภทการออกทำงานถูกต้อง
- [ ] กลุ่มที่มีข้อมูลน้อยกว่า 5 คนไม่แสดงผลสรุปที่ระบุกลุ่มได้
- [ ] คะแนน เหตุผล และหน่วยงานทั้งหมดใช้เกณฑ์ขั้นต่ำเดียวกัน
- [ ] ผู้ไม่มีสิทธิ Analytics ไม่สามารถเรียกข้อมูลผ่าน URL โดยตรง

## UAT-08 Telegram (ถ้ามี component ใน test environment)

- [ ] ผู้ไม่มี Telegram ID ได้รับข้อความเตือนโดยไม่สร้างความเสียหายกับรายการ
- [ ] ส่งสำเร็จแล้วลิงก์เปิดได้
- [ ] ส่งล้มเหลวแล้วมีข้อความให้ใช้วิธีคัดลอกลิงก์แทน
- [ ] ข้อความ Telegram ไม่มีคำตอบหรือข้อมูลอ่อนไหว มีเพียงคำเชิญและลิงก์

## เกณฑ์ผ่าน

- ทุกข้อ Security, Permission, Public link และ Privacy ต้องผ่านทั้งหมด
- ห้ามมี Critical/High defect ค้าง
- Medium defect ต้องมีผู้รับผิดชอบและแผนแก้ที่ได้รับอนุมัติ
- แนบหลักฐานหน้าจอเฉพาะข้อมูลทดสอบและลบ token ออกจากภาพ/ข้อความ
- ผู้แทน HR และผู้ดูแลระบบลงชื่อยอมรับก่อน merge หรือ deploy

## บันทึกผล

| วันที่ | Environment | ผู้ทดสอบ | Scenario | ผล | Issue/หลักฐาน |
|---|---|---|---|---|---|
| | | | | Pass / Fail | |
