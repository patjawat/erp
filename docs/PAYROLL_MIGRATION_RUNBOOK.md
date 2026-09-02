# Payroll — Migration Runbook (แก้ตารางขาดบน production)

สถานะ: แก้ฝั่งโค้ดแล้ว รอรันบน production

## อาการ

เปิด `/finance/payroll/employee-items?group=monthly_pay` บน production แล้วได้

```
SQLSTATE[42S02]: Base table or view not found: 1146
Table 'dansai.payroll_item_type' doesn't exist
```

## สาเหตุ

Migration 5 ตัวของ payroll เคยถูกวางไว้ในโฟลเดอร์ย่อย `migrations/payroll/` แต่ `yii migrate`
อ่านเฉพาะ `@app/migrations` ชั้นเดียว **ไม่ไล่โฟลเดอร์ย่อย** ทั้ง `script/migrate.sh` ตอน deploy
และคำสั่งมาตรฐาน `php yii migrate/up --migrationPath=@app/migrations` จึงข้ามไฟล์ชุดนี้ทั้งหมด

ผลคือไฟล์ถูก build ติดไปกับ image (ไม่ได้ถูก `.dockerignore` กัน) แต่ไม่เคยถูกสั่งรัน

หลักฐานเทียบฐานข้อมูล: `yii2basic_test` ที่รัน `migrate` ตามปกติ ขาดตารางชุดเดียวกับ production เป๊ะ
ส่วน `erp` บนเครื่อง dev มีครบเพราะเคยรันมือด้วย `--migrationPath=@app/migrations/payroll`

## Migration ที่เกี่ยวข้อง

1. `m260830_150000_create_payroll_configuration` — สร้าง `payroll_item_type`, `payroll_contribution_rule` + seed
2. `m260830_160000_create_payroll_employee_item` — สร้าง `payroll_employee_item`
3. `m260830_170000_add_payroll_item_group_and_document_order` — เพิ่ม `item_type.item_group`, `employee_item.document_order`
4. `m260901_200000_add_payroll_run_type` — เพิ่ม `payroll_period.period_type`, `payroll_item_type.payroll_scope`
5. `m260901_201000_separate_payroll_preparation_period` — อัปเดตข้อมูล period เดิมเป็น `preparation`

ตัวที่ 4 มี guard `getTableSchema()` ตรวจก่อนทุกครั้ง รันซ้ำไม่พัง

## หน้าที่ได้รับผลกระทบ

- `/finance/payroll/employee-items` ทุก group, `/add-item-employees`, `/settings`
- `/finance/payroll/payroll-runs`, `/payslip` (ใช้ `period_type` / `payroll_scope`)
- สลิปเงินเดือนฝั่ง `/profile`

## การแก้ในโค้ด

ย้ายไฟล์ทั้ง 5 จาก `migrations/payroll/` ขึ้นมาไว้ที่ `migrations/` ชั้นเดียวกับตัวอื่น

ปลอดภัยเพราะ Yii บันทึกประวัติในตาราง `migration` ด้วย**ชื่อคลาสล้วน ไม่มี path** ฐานที่รันชุดนี้ไปแล้ว
(เช่น `erp` บนเครื่อง dev) จะเห็นว่า version ซ้ำแล้วข้ามเอง ไม่รันซ้ำ

เลือกวิธีนี้แทนการตั้ง `migrationPath` เป็น array ใน `config/console.php` เพราะถ้า `script/migrate.sh`
ส่ง `--migrationPath=@app/migrations` มาทาง CLI ค่าใน config จะถูก override ทิ้งแล้วเงียบไปเฉย ๆ

## รันบน production

ไฟล์อยู่ใน container อยู่แล้ว **ไม่ต้อง deploy image ใหม่** ก่อนรันต้อง backup ฐานข้อมูลและทดสอบ restore

```bash
docker compose exec app php yii migrate/up --migrationPath=@app/migrations/payroll --interactive=0
```

หลัง deploy รอบถัดไป (ไฟล์ย้ายขึ้นมาแล้ว) ใช้คำสั่งปกติได้เลย

```bash
php yii migrate/up --migrationPath=@app/migrations --interactive=0
```

## ตรวจหลังรัน

```sql
SELECT table_name FROM information_schema.tables
 WHERE table_schema = 'dansai' AND table_name LIKE 'payroll%';
```

ต้องได้ครบ 7 ตาราง: `payroll_audit_log`, `payroll_bank_account`, `payroll_contribution_rule`,
`payroll_employee_item`, `payroll_item_type`, `payroll_period`, `payroll_period_employee`

แล้วเปิด `/finance/payroll/employee-items?group=monthly_pay` และ `/finance/payroll/payroll-runs` ให้ผ่าน

## Rollback

`safeDown()` ของข้อ 1 และ 2 จะ **drop ตารางพร้อมข้อมูลรายการรายบุคคลทั้งหมด** ห้าม rollback บน production
หลังเริ่มบันทึกรายการจริง เว้นแต่มี change approval และ backup ที่กู้คืนได้จริง

## กติกาต่อจากนี้

migration ทุกไฟล์ต้องวางใน `migrations/` ชั้นเดียว ห้ามสร้างโฟลเดอร์ย่อย เพราะ `yii migrate` มองไม่เห็น
ถ้าต้องแยกจริง ๆ ให้เพิ่ม path ใน `script/migrate.sh` บนเครื่อง deploy ด้วยทุกครั้ง
