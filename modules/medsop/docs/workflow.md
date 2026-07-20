# Workflow และ Revision Policy

## สถานะเอกสาร

1. `DRAFT`: ผู้ดูแลสร้างและแก้ไขข้อมูลได้
2. `PENDING`: สร้างรายการ `approve` ชื่อ `medsop` และล็อกการแก้ไข
3. `PUBLISHED`: อนุมัติครบแล้ว เปิดให้ผู้ใช้ในแผนกอ่านได้
4. `REJECTED`: ถูกปฏิเสธหรือส่งกลับ ผู้ดูแลแก้ไขและส่งใหม่ได้
5. `ARCHIVED`: เลิกใช้แล้ว แต่ยังเก็บประวัติและ Revision

## Revision

- การแก้เอกสารที่ Published ต้องสร้าง Working Revision ใหม่
- Snapshot เก็บ metadata, objective, scope และ steps เป็น JSON
- ไฟล์ของแต่ละ Revision ใช้ `file_ref` แยกกัน
- Revision ก่อนหน้ายังคงอ่านย้อนหลังได้
- ห้ามเรียก hard delete ของ filemanager หาก Revision ยังอ้างอิงไฟล์

## การอนุมัติ

- MedSOP สร้างและอ่านรายการผ่าน `ApprovalIntegrationService`
- ใช้ `approve.name = medsop` และ `approve.from_id = document.id`
- `Pending` หมายถึงอยู่ระหว่างอนุมัติ
- `Pass` ทุกระดับจึงเผยแพร่ได้
- `Reject` เปลี่ยนเอกสารเป็น `REJECTED`
