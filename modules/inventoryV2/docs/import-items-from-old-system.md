# วิธีนำสินค้า/วัสดุจากระบบคลังเดิม เข้าระบบคลัง v2

เอกสารนี้สรุปตัวเลือกในการนำรายการพัสดุ (master) จากระบบเดิมเข้า **stock_item** ของคลัง v2 พร้อมข้อดีและข้อเสีย

**หมายเหตุ:** เมื่อคลัง v2 เสร็จสมบูรณ์ จะ**ปิดการใช้งานระบบคลังเดิม** ดังนั้นการนำเข้าจากระบบเดิมเป็นแบบ **ครั้งเดียว (one-time migration)** เป็นหลัก ไม่จำเป็นต้องออกแบบ sync สองทางระยะยาว

---

## แหล่งข้อมูลระบบเดิม

- **ตาราง:** `categorise`
- **เงื่อนไข:** `name = 'asset_item'` (รายการพัสดุ/สินค้า) และถ้ามีคอลัมน์ `group_id` อาจใช้ `group_id = 'MATER'` กรองเฉพาะวัสดุ
- **ฟิลด์หลัก:** `code` (รหัส), `title` (ชื่อ), `category_id` (ประเภท), `ref`, `data_json` (เช่น `$.unit` = หน่วยนับ)

## การ map ไป stock_item (v2)

| categorise (เดิม) | stock_item (v2) |
|------------------|-----------------|
| code             | item_code       |
| title            | item_name       |
| category_id      | category_id     |
| ref              | ref             |
| data_json->unit  | data_json->unit_name |
| -                | is_asset = 0 (วัสดุ) |
| -                | is_active = 1   |
| -                | created_at (เช่น UNIX_TIMESTAMP()) |

---

## ตัวเลือกการนำเข้า

### 1. One-time SQL (INSERT ... SELECT)

รันคำสั่ง SQL โดยตรงใน DB หรือผ่าน migration/script ครั้งเดียว

**ตัวอย่าง (เฉพาะรายการที่ยังไม่มีใน stock_item):**

```sql
INSERT INTO stock_item (ref, category_id, item_code, item_name, is_asset, is_active, data_json, created_at)
SELECT c.ref, c.category_id, c.code, c.title, 0, 1,
  JSON_OBJECT('unit_name', JSON_UNQUOTE(JSON_EXTRACT(c.data_json, '$.unit'))),
  UNIX_TIMESTAMP()
FROM categorise c
WHERE c.name = 'asset_item'
  AND (c.group_id = 'MATER' OR c.group_id IS NULL)  -- ถ้ามีคอลัมน์ group_id
  AND NOT EXISTS (SELECT 1 FROM stock_item s WHERE s.item_code = c.code);
```

| ข้อดี | ข้อเสีย |
|--------|----------|
| ทำได้เร็ว ไม่ต้องเขียน UI | ไม่มีหน้าจอให้เลือก/ตรวจก่อนนำเข้า |
| ใช้ได้กับ DBA / dev ที่คุ้น SQL | ถ้า item_code ซ้ำต้องจัดการ (ใช้ NOT EXISTS หรือ INSERT IGNORE) |
| ทำครั้งเดียวจบได้ | ไม่มี log แบบละรายการในแอป |
| | ต้องแก้เงื่อนไข (เช่น group_id) ตามโครงสร้างจริงของ categorise |

---

### 2. Console command (Yii migrate / custom command)

สร้างคำสั่งเช่น `php yii import-stock-items/from-categorise [--dry-run] [--limit=N]`

- อ่านจาก `categorise` (name = 'asset_item') → สร้างหรือข้ามรายการที่ item_code มีใน stock_item แล้ว
- รองรับ dry-run, limit, และ log ลง console/ไฟล์

| ข้อดี | ข้อเสีย |
|--------|----------|
| ทำซ้ำได้ ควบคุมได้ (limit, dry-run) | ต้องรันบน server/CLI |
| มี log และตรวจผลก่อนรันจริงได้ | ผู้ใช้ทั่วไปไม่ชอบใช้ command line |
| แก้ logic map / เงื่อนไขในโค้ดได้ง่าย | |

---

### 3. หน้าเว็บ "นำเข้าจากระบบเดิม" (แนะนำถ้าต้องการให้ผู้ใช้กดเอง)

เพิ่มหน้าในโมดูล inventoryV2 เช่น `/inventory-v2/stock-item/import-from-legacy`:

- Query รายการจาก `categorise` (asset_item) ที่ **ยังไม่มี** ใน `stock_item` (เทียบจาก code = item_code)
- แสดงเป็นตารางพร้อม checkbox เลือกรายการที่จะนำเข้า
- ปุ่ม "นำเข้าที่เลือก" → บันทึกเข้า stock_item (และอาจ ref กลับไป categorise ผ่าน `ref`)

| ข้อดี | ข้อเสีย |
|--------|----------|
| ผู้ใช้เลือกได้ว่าอันไหนนำเข้า | ต้องพัฒนาหน้าและ backend |
| เห็นรายการก่อนนำเข้า ตรวจสอบได้ | ใช้เวลาพัฒนามากกว่าวิธี 1–2 |
| ทำทีละ batch ได้ ลดความเสี่ยงผิดพลาด | |

---

### 4. Sync เป็นระยะ (คงการซิงก์สองทาง)

สร้าง job (cron) หรือปุ่ม "ซิงก์จากระบบเดิม" ที่สร้าง/อัปเดต stock_item จาก categorise เป็นระยะ

- เช่น เฉพาะรายการที่เพิ่มใหม่ใน categorise (ตาม created_at หรือ flag)
- ต้องกำหนดชัดว่า "ระบบเดิมเป็น master" หรือ "v2 เป็น master" เพื่อไม่ให้ข้อมูลชนกัน

| ข้อดี | ข้อเสีย |
|--------|----------|
| รายการใหม่ในระบบเดิมมา v2 อัตโนมัติ | ออกแบบซับซ้อน (ทิศทาง sync, conflict) |
| ไม่ต้องจำนำเข้าทุกครั้ง | อาจชนกับการแก้ใน v2 ถ้าแก้ทั้งสองระบบ |
| | ต้องมี rule ชัดเจนว่าใครเป็นต้นทาง |

**หมายเหตุ:** ถ้ามีแผน**ปิดระบบเดิมเมื่อ v2 เสร็จ** วิธีนี้ไม่จำเป็นสำหรับระยะยาว ใช้ได้แค่ช่วงเปลี่ยนผ่านเท่านั้น

---

### 5. นำเข้าจาก Excel/CSV

- Export รายการจากระบบเดิม (หรือจาก `categorise`) เป็น Excel/CSV
- ใช้หน้าหรือคำสั่งนำเข้า CSV ที่มีอยู่ (เช่น โมดูล sm/import-product) โดย map column กับ stock_item

| ข้อดี | ข้อเสีย |
|--------|----------|
| ใช้ flow นำเข้าเดิมได้ | ต้อง map ฟิลด์และอาจแก้ format |
| แก้/กรองข้อมูลใน Excel ก่อนอัปโหลดได้ | ไม่ real-time ต้อง export → แก้ → อัปโหลด |
| เหมาะกับผู้ใช้ที่คุ้น Excel | |

---

## สรุปแนะนำ

**เมื่อ v2 เสร็จจะปิดระบบเดิม** → โฟกัสที่ **นำเข้าครั้งเดียว (one-time migration)** เป็นหลัก

| เป้าหมาย | แนะนำ |
|----------|--------|
| นำเข้าครั้งเดียวเร็ว ๆ โดย dev/DBA | **วิธี 1 (SQL)** หรือ **วิธี 2 (console command)** |
| ให้ผู้ใช้กดนำเข้าจากรายการที่เลือกเอง | **วิธี 3 (หน้าเว็บนำเข้าจากระบบเดิม)** |
| ช่วงเปลี่ยนผ่าน อยากดึงรายการใหม่จากเดิมชั่วคราว | **วิธี 4 (sync)** — ใช้ได้แค่ช่วงก่อนปิดระบบเดิม |
| มีไฟล์ Excel อยู่แล้ว / ชอบแก้ในสเปรดชีต | **วิธี 5 (Excel/CSV)** |

---

## หมายเหตุ

- ถ้าตาราง `categorise` ไม่มีคอลัมน์ `group_id` ให้ใช้เฉพาะ `name = 'asset_item'` ใน WHERE และกรองประเภทอื่น (ถ้ามี) ทาง application หรือเงื่อนไขอื่นตามโครงสร้างจริง
- หลังนำเข้า ควรตรวจสอบว่า **category_id** ใน stock_item สอดคล้องกับประเภทในคลัง v2 (เช่น warehouse allowed types) และหน่วยนับใน data_json ถูกต้อง
