# แผนปรับปรุงทะเบียนครุภัณฑ์ — fsn_number แทน code + ค่าเสื่อมที่หมวดทรัพย์สิน

> สร้าง 2026-07-11 · โมดูล `modules/am`

## เป้าหมาย
1. ให้ `fsn_number` เป็น **หมายเลขครุภัณฑ์จริง** (รับบทบาทที่เคยเป็นของ `code`)
2. **FSN prefix** ดึงจากหมวดทรัพย์สิน (`categorise.code`, name=`asset_category`) อัตโนมัติ — ไม่ต้องกรอกซ้ำบน asset
3. **ค่าเสื่อม/อัตราค่าเสื่อม** กำหนดที่หมวดทรัพย์สินเป็นแหล่งเดียว (ล็อกทุกตัว) — ฟอร์มครุภัณฑ์ไม่ต้องกรอก
4. ตัดฟิลด์ที่ซ้ำซ้อนออกจากฟอร์ม ให้กรอกน้อยลง ไม่รก

## การตัดสินใจ (ยืนยันแล้ว)
- **ค่าเสื่อม:** หมวด = แหล่งเดียว, ล็อก. asset ไม่มีช่องให้แก้ → `beforeSave` sync `useful_life`/`depreciation_rate`
  จากหมวดลงคอลัมน์ asset (ให้รายงาน/SQL เดิมที่อ่าน `asset.useful_life` ยังทำงาน) แต่ผู้ใช้แก้ไม่ได้
- **`code`:** เก็บไว้เป็น mirror ของ `fsn_number` เสมอ → QR, `asset_detail.code`, `isCar`, unique constraint,
  sequence generator ไม่ต้องแตะ

## ความหมายฟิลด์ใหม่
| ฟิลด์ | เดิม | ใหม่ |
|---|---|---|
| `asset.fsn_number` | FSN prefix (เช่น `7910-003-0003`) | **หมายเลขครุภัณฑ์เต็ม** (เช่น `7910-003-0003/66.01`) |
| `asset.code` | หมายเลขครุภัณฑ์เต็ม | **mirror ของ `fsn_number`** (sync อัตโนมัติ) |
| FSN prefix | เก็บใน `asset.fsn_number` | ดึงจาก `categorise.code` ของหมวดที่เลือก (ไม่เก็บซ้ำบน asset) |
| ค่าเสื่อม | กรอกบน asset | **คอลัมน์จริง** `categorise.useful_life` + `categorise.depreciation_rate` |

---

## เฟสงาน

### Phase 0 — Data restructure  ❌ ยกเลิก (ผู้ใช้ restore DB + กติกา "ไม่สร้างหมวด")
- เคยลอง migration `140000` (สร้างหมวด FSN + repoint + flip) แต่ผู้ใช้ **restore DB กลับ** และสั่งว่า
  **ห้ามสร้างหมวดใหม่** → ลบไฟล์ 140000 ทิ้ง ไม่แตะข้อมูล asset/categorise
- ปัจจุบัน (หลัง restore + re-apply): `fsn_number` = prefix, `code` = เลขเต็ม, `asset_category_id` = รหัสสั้น (COM/MED) — **สภาพเดิม**
- migration ที่ apply แล้วบน `dansai`: `164243` (collation), `100000` (seed structure→category, งานเดิม), `120000` (คอลัมน์ค่าเสื่อม Phase 1)
- ⚠️ **บทเรียน:** แอปใช้ DB `dansai` (ไม่ใช่ ubonrat) — ตรวจ `docker exec dansai cat .env | grep DB_` ก่อนเสมอ (ดู [[project_dev_docker_env]])

> **⚠️ Coupling ค้าง:** โค้ด Phase 2/3 ออกแบบให้ `asset_category_id` = FSN prefix (ใช้ generate หมายเลข + อ่านค่าเสื่อม)
> แต่ข้อมูลจริงยังเป็นรหัสสั้น (COM/MED) → ถ้าใช้ฟอร์ม equip ตอนนี้ หมายเลขที่ generate จะได้ prefix ผิด (เช่น `COM/68.01`)
> ต้องรอผู้ใช้เคาะวิธีจัดการข้อมูล/หมวดก่อน จึงจะใช้งานจริงถูกต้อง

### Phase 1 — หมวดทรัพย์สินเป็นแหล่งค่าเสื่อม  ✅ เสร็จ
- **เก็บเป็นคอลัมน์จริง** (ไม่ใช่ data_json ตามที่ปรับภายหลัง):
  `migrations/m260711_120000_add_depreciation_fields_to_categorise.php`
  — เพิ่ม `categorise.useful_life` (int) + `categorise.depreciation_rate` (decimal 6,2)
  ชนิดตรงกับตาราง asset · backfill ค่าเดิม 17 รายการจาก data_json เข้าคอลัมน์ + JSON_REMOVE 2 key ออก
- `views/asset-category/_form.php`: เพิ่ม 2 ช่อง `useful_life`, `depreciation_rate` (bind คอลัมน์จริง)
- `models/AssetCategory.php`: rules validation + attributeLabels (ActiveRecord จัดการคอลัมน์เอง ไม่ต้อง virtual)
- `controllers/AssetItemController.php` `actionCategoryDefaults`: อ่านจากคอลัมน์ `$category->useful_life`/`depreciation_rate`
- **หมายเหตุ:** flow สิ่งปลูกสร้าง (`getStructureDefaults`, name=`structure_type`) ยังอ่านจาก data_json อยู่ —
  ถ้าต้องการรวมมาใช้คอลัมน์เดียวกันเป็นงานต่อเนื่อง

### Phase 2 — รวมหมายเลขครุภัณฑ์ (Asset model)  ✅ เสร็จ
- `models/Asset.php` เพิ่ม `applyEquipCategoryRules()` เรียกต้น `beforeSave` (guard ด้วย `asset_category_id`):
  - **ล็อกค่าเสื่อม:** override `useful_life`/`depreciation_rate` จากหมวด (`categorise.code = asset_category_id`) เสมอ
  - **หมายเลข:** `fsn_number` = หมายเลขเต็ม, `code` = mirror. รองรับช่วงเปลี่ยนผ่าน (ฟอร์มเก่าส่งเลขที่ `code`,
    prefix ที่ `fsn_number`) + generate จาก prefix+ปีงบเมื่อยังไม่มีเลข (เฉพาะตอน save จริง ไม่กิน sequence ตอน ajax validate)
- rule: เอา `useful_life` ออกจาก `required` (ถูกล็อกจากหมวดหลัง validation)
- `EquipController` clone: เลิกเรียก `nextAssetCode($fsn_number)` (พังเพราะ fsn_number เป็นเลขเต็มแล้ว) →
  เคลียร์ `code`/`fsn_number` ให้ beforeSave สร้างใหม่, เก็บเลขเดิมไว้ที่ `data_json.fsn_old`
- ทดสอบแล้ว (transaction+rollback): Case ฟอร์มใหม่ (generate), ฟอร์มเก่า (flip code→fsn_number),
  override ค่าเสื่อมจากหมวด, mirror `code==fsn_number` — ผ่านทั้งหมด
- **ค้าง:** unique `fsn_number` (comment "หมายเลข FSN ซ้ำได้" ที่ `ImportController.php:419` ต้องทบทวน) —
  ยังไม่บังคับใน Phase 2; เลขที่ generate ไม่ซ้ำอยู่แล้วจาก sequence per หมวด+ปี

### Phase 3 — ฟอร์มครุภัณฑ์ (ตัดช่องซ้ำ/รก)
- `views/equip/_form.php`: ลบช่อง FSN text (494) → hidden prefix; ช่อง code (503) → rebind เป็น `fsn_number`;
  ลบการ์ด "เตรียมข้อมูลค่าเสื่อม" (328–357) → read-only จากหมวด; แก้ JS `#asset-code` → `#asset-fsn_number`

### Phase 4 — Generator / next-number
- `AssetNumberGenerator::resolveInitialSequence` scan `asset.code` อยู่ — mirror จึงไม่กระทบลำดับถัดไป
  (เสริม: scan ทั้ง `code` และ `fsn_number` กันเหนียว)
- `actionNextAssetNumber` (`EquipController.php:743`): input=prefix, ผลลัพธ์ใส่ `fsn_number`

### Phase 5 — Views/Display
- ที่แสดง `asset.code` ไม่ต้องแก้ (mirror) · แก้เฉพาะ label `fsn_number` ที่เคยหมายถึง prefix
- search/import (`DisposalController`, `AuditController`) รองรับทั้ง `code`/`fsn_number` แล้ว

### Phase 6 — ทดสอบ
- สร้างครุภัณฑ์: เลือกหมวด → prefix+ค่าเสื่อม auto → generate → บันทึก → เช็ก `fsn_number == code`, ค่าเสื่อมตรงหมวด, QR
- ตัวที่ 2 หมวด/ปีเดียวกัน → ลำดับ +1 · แก้หมวด → ของเดิมไม่เพี้ยน
- รันผ่าน docker, tenant DB จาก `.env` ในคอนเทนเนอร์

### Phase 7 — หมวด "ร่าง/ตัวอย่าง" + กันหมวดที่ยังไม่พร้อมผูกกับครุภัณฑ์  ✅ เสร็จ 2026-07-12
- **แนวคิด:** seed หมวดเป็น "ตัวอย่าง" ให้ผู้ใช้เห็นก่อน แล้วค่อยเติมรหัส FSN + เปิดใช้งานทีหลัง
- **กติกาเดียว:** หมวด "พร้อมใช้งาน" = มีรหัส (`code<>''`) **และ** เปิดใช้งาน (`active<>0`) · NULL = ถือว่าเปิด (เฉพาะ `0` เป็นร่าง)
- ใช้คอลัมน์ `categorise.active` ที่มีอยู่แล้วเป็นสถานะร่าง (ไม่ต้องแตะ schema/migration)
- `Asset::validateCategoryReady` (rule ใหม่, guard `asset_group_id==4`): asset_category_id ต้องอ้างหมวดที่พร้อมใช้งาน ไม่งั้น error ที่ช่อง
- `AssetItemController::actionGetAssetCategory`: DepDrop แสดงเฉพาะหมวดพร้อมใช้งาน (ซ่อนร่าง — DepDrop disable ราย option ไม่เสถียร จึงเลือกซ่อน)
- `AssetCategoryController::actionValidator`: เอา required ของ `code` ออก (สร้างร่างได้)
- `views/asset-category/_form.php`: code เว้นว่างได้ + เพิ่ม toggle "เปิดใช้งาน"
- badge "ร่าง" ที่ index + setting-quick/_items + _quick_list_items (เห็นว่าหมวดไหนยังไม่พร้อม)
- **หมายเหตุ edge:** ถ้าปิดใช้งานหมวดที่มี asset อ้างอยู่ การ re-save asset นั้นจะติด validator (ตั้งใจ) — เปิดหมวดกลับก่อน

## ความเสี่ยง
1. unique `fsn_number` vs "FSN ซ้ำได้" เดิม — เคลียร์ก่อน Phase 2
2. ค่าเสื่อมล็อกทุกตัวจะทับค่าต่อตัวตอน save ครั้งถัดไป — backfill หมวดให้ครบก่อน (Phase 0)
3. Backfill `fsn_number = code` เป็น one-way — สำรอง DB ก่อน (mysqldump ใน container `dansai_db`)
4. ฟอร์มอื่นที่ใช้ `#asset-fsn_number` (bulk-create, land, structure, list_item, fsn) — ตรวจ flow ไม่พัง
