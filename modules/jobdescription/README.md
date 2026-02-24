# โมดูล คำอธิบายงาน (Job Description — JD)

## ความสามารถ

- **Template ต่อตำแหน่งงาน**: สร้าง/แก้ไข template JD แยกตามตำแหน่ง (ผูกกับ `categorise` ชื่อ `position_name`)
- **Employee JD**: ในโปรไฟล์พนักงาน (HR > ทะเบียนบุคลากร > ดูพนักงาน) มีเมนู "คำอธิบายงาน (JD)" — โหลด template ตามตำแหน่งปัจจุบันได้ แล้วแก้ไข/เพิ่ม/ลบหัวข้อได้

## โครงสร้างตาราง (migrations อยู่ในโมดูล)

- `jd_template` — template ต่อตำแหน่ง (name, position_code, is_active)
- `jd_template_section` — หัวข้อใน template (title, content, sort_order)
- `jd_employee` — JD ต่อพนักงาน (emp_id, template_id อ้างอิง template ที่โหลดมา)
- `jd_employee_section` — หัวข้อใน JD พนักงาน (แก้ไขได้)

## การรัน Migration

```bash
php yii migrate --migrationPath=@app/modules/jobdescription/migrations
```

## เมนู/จุดเข้าใช้

- **จัดการ Template**: HR > ตั้งค่า (dropdown) > **Template คำอธิบายงาน (JD)** หรือ `/jobdescription/template/index`
- **JD ของพนักงาน**: HR > ทะเบียนบุคลากร > เลือกพนักงาน > **คำอธิบายงาน (JD)** (เมนูซ้าย) หรือ `/jobdescription/employee-jd/view?emp_id=...`
