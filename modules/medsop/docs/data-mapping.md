# Data Mapping

| MedSOP | แหล่งข้อมูล | การเชื่อมโยง |
|---|---|---|
| ผู้ใช้งานปัจจุบัน | `Yii::$app->user` | `user.id = employees.user_id` |
| บุคลากร | `employees` | `medsop_document.created_emp_id = employees.id` |
| แผนกเจ้าของเอกสาร | `tree` ผ่าน `Organization` | `medsop_document.organization_id = tree.id` |
| ขั้นตอนปฏิบัติงาน | `medsop_document_step` | `document_id = medsop_document.id` |
| ฉบับเอกสาร | `medsop_document_revision` | `document_id = medsop_document.id` |
| รายการอนุมัติ | `approve` ผ่าน `approveV2` | `name = medsop`, `from_id = document.id` |
| ไฟล์แนบ | `uploads` ผ่าน `filemanager` | `uploads.ref = revision.file_ref` |

## กติกา

- ไม่สร้างข้อมูล Employees หรือ Organization ซ้ำ
- ไม่เพิ่มคอลัมน์ในตารางของโมดูลอื่น
- External reference ใช้ index แต่ไม่สร้าง foreign key ข้ามโมดูล เพื่อลด coupling
- Foreign key ใช้เฉพาะระหว่างตาราง MedSOP
- ไฟล์แต่ละ Revision ใช้ `file_ref` คนละค่า เพื่อห้ามไฟล์ฉบับใหม่ทับฉบับเก่า
