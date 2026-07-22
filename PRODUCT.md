# Product

## Register

product

## Users

บุคลากรของโรงพยาบาลด่านซ้าย ใช้งานในบริบทของระบบ ERP ราชการ ครอบคลุมทุก role:

- **เจ้าหน้าที่ทั่วไป** (พยาบาล, ธุรการ): ขอลา, จอง/ยืมรถ-ห้องประชุม, แจ้งซ่อม, ดูหนังสือราชการ
- **หัวหน้างาน/ผู้อนุมัติ**: อนุมัติใบลา/จอง/แจ้งซ่อม ตามลำดับชั้นการอนุมัติ
- **ช่างซ่อม/ผู้ดูแลระบบ**: รับงานซ่อม, จัดการห้องประชุม, admin tasks

บริบทการใช้งาน mobile module: เปิดผ่านมือถือระหว่างปฏิบัติงานจริงในโรงพยาบาล (เดินอยู่, แสงไฟผันแปร, มือไม่ว่าง) หรือผ่าน Telegram MiniApp จาก notification งานที่รออนุมัติ ต้องสแกนหา action สำคัญได้ใน 3 วินาที ไม่ต้องคิด

## Product Purpose

ระบบ ERP ภายในของโรงพยาบาลด่านซ้าย ครอบคลุม HR, จองรถ/ห้องประชุม, แจ้งซ่อม, ลา, ทรัพย์สิน, สารบรรณ ฯลฯ บน Yii2 + Bootstrap 5 + MySQL

`modules/mobile` คือ surface สำหรับมือถือโดยเฉพาะ — quick services + approvals + notifications — เพื่อให้บุคลากรปฏิบัติงาน ERP ผ่านมือถือ/Telegram MiniApp ได้โดยไม่ต้องเปิดเครื่อง desktop

ความสำเร็จคือ: เจ้าหน้าที่กรอกใบลา/จองรถ/อนุมัติได้จบบนมือถือใน <30 วินาที โดยไม่ต้องสลับไปที่ desktop UI

## Data Rules

- **วัสดุ = `group_id = MATER`** — ทุก query/report/dropdown/import/export ที่ดึง "วัสดุ" ใน `inventoryV2` ต้องกรองเฉพาะรายการ `StockItem`/`categorise` ที่เป็น `group_id = MATER` เสมอ เพื่อไม่ให้ดึงพัสดุกลุ่มอื่นปนเข้ามา

## Table Conventions

กฎมาตรฐานเมื่อ **สร้างตารางใหม่** ในระบบ:

### 1. คอลัมน์มาตรฐาน (audit + file ref)

ทุกตารางใหม่ต้องมีคอลัมน์เหล่านี้เสมอ:

```php
 * @property string|null   $ref               token ต่อ record (ใช้ผูกไฟล์ในระบบ filemanager)
 * @property string|null   $created_at
 * @property string|null   $updated_at
 * @property int|null      $created_by        ผู้สร้าง
 * @property int|null      $updated_by        ผู้แก้ไข
```

- `ref` — สร้างตอน insert ด้วย `substr(Yii::$app->getSecurity()->generateRandomString(), 10)` (ใน `beforeSave` เมื่อ `$insert`)
- `created_at` / `updated_at` — เซ็ตใน `beforeSave` (`updated_at` ทุกครั้ง, `created_at` เฉพาะ insert)
- `created_by` / `updated_by` — เก็บ `Yii::$app->user->id`

### 2. ระบบอัปโหลดไฟล์ = ใช้ `modules/filemanager` เสมอ

ห้ามเขียนระบบเก็บไฟล์เอง (เช่น `saveAs` ลง `web/uploads/...` แล้วเก็บ path ดิบ). ถ้า record มีการอัปโหลดไฟล์:

- เก็บไฟล์ผ่าน `FileManagerHelper::saveUploadedFile($file, $ref, $name, $replaceSlot)` → บันทึกลงตาราง `uploads` + สร้าง thumbnail อัตโนมัติ
- **`ref` ของ `uploads` = `ref` ของ record ต้นทาง** → ไฟล์ทั้งหมดของ record อยู่ใน `fileupload/<record.ref>/` (**1 record = 1 folder**, ตรวจย้อนกลับหาต้นทางได้) — ห้ามใช้ ref เป็นชื่อคงที่รวมทุก record
- แยกชนิดไฟล์ในโฟลเดอร์เดียวด้วย `name` slot เช่น `avatar`, `signature`, `cover`, `step_media`
- retrieval: `Uploads::find()->where(['ref' => $model->ref, 'name' => $slot])`; serve ผ่าน `/filemanager/uploads/show?id=` หรือ `get-image?id=`
- ต้นแบบ: `modules/hr/models/Employees.php` (`ShowAvatar()`) + `modules/medsop` (cover/step media)

## Design

See [DESIGN.md](DESIGN.md).
