# Checklist งานกระทบยอดปิดเดือน inventoryV2 (มิ.ย. 2569)

> อัปเดตล่าสุด: 2026-07-21 · Tenant DB: `dansai` (container `dansai_db`) · Branch: `dev`
> เอกสารนี้สำหรับส่งต่อให้คนอื่นทำต่อ — สรุปว่าทำอะไรไปแล้ว ข้อมูลอยู่สถานะไหน ปัญหาคืออะไร

---

## 1. ที่มา / โจทย์

ผู้ใช้พบว่า **"ยอดยกไป" หน้าปิดเดือน (material-summary)** กับ **"ยอดมูลค่ารวม" หน้าวัสดุคงเหลือ (main-stock/balance)** ไม่ตรงกัน โดยเฉพาะหมวด M1 และ M22 → สืบจนเจอหลายชั้น: เรื่องเวลา (งวด), วิธีตีราคา, warehouse ไม่ตรง, และบั๊กข้อมูลหลายตัว

---

## 2. โค้ดที่แก้ไปแล้ว (⚠️ ยังไม่ commit)

**ไฟล์:** `modules/inventoryV2/controllers/ReportController.php` · เมธอด `computeMonthlyRows()`

| จุด | เดิม | แก้เป็น |
|---|---|---|
| ตีราคา **OUT** (~บรรทัด 1860) | reprice ที่ราคา IN lot ล่าสุด (`in_lot.unit_price`) | ใช้ราคาบนแถว `sd.unit_price` |
| ตีราคา **ADJUST** (~บรรทัด 1918) | reprice ที่ราคา IN item ล่าสุด (`in_item.unit_price`) | ใช้ราคาบนแถว `sd.unit_price` |
| ลบ subquery `$latestInPrice` + `$latestInPriceByItem` ที่ไม่ใช้แล้ว | | |

**ผล:** ยอดปิดเดือนตีมูลค่า IN/OUT/ADJUST ด้วยราคาบนแถวจริง = ตรงกับหน้า balance (`loadLedgerValues`) 100%
**Verify แล้ว:** M1 งวด (ที่มี ADJUST) = 302,012.95 ตรงกับ balance เป๊ะ

> ✅ อัปเดต 2026-07-21: ปิดเดือนอ่าน `value_delta` ของ ADJUST แบบ "ปรับมูลค่าล้วน" (qty=0) แล้ว และรองรับข้อมูลเก่าที่ `data_json` ยังเป็น double-encoded string

---

## 3. ข้อมูลที่แก้ในฐานข้อมูลจริง (data mutations) + rollback

Backup ทั้งหมดเก็บถาวรที่ **`docs/close-month-recon-202506/backups/`** (คัดลอกจาก scratchpad แล้ว — scratchpad จะหายเมื่อจบ session)

| # | สิ่งที่ทำ | สถานะ | ไฟล์ rollback |
|---|---|---|---|
| 1 | ย้าย ADJUST **M1** 18 ใบ ก.ค. → 30 มิ.ย. | **คงไว้** | `rollback_m1_dates.sql` |
| 2 | ย้าย ADJUST **M3** 13 ใบ ก.ค. → 30 มิ.ย. | **คงไว้** | `rollback_m3_dates.sql` |
| 3 | ย้าย ADJUST **M22** 6 ใบ ก.ค. → 30 มิ.ย. แล้ว **ย้อนกลับ ก.ค.** | **ย้อนแล้ว (สุทธิ = อยู่ ก.ค.)** | `rollback_m22_dates.sql` |
| 4 | ย้าย **M22-3** 2 ใบ (สร้าง 21 ก.ค. แต่ลงวันที่ 30 มิ.ย.) → 21 ก.ค. | **คงไว้** | `rollback_m223_dates.sql` |
| 5 | ย้าย **setting** วัสดุกายภาพบำบัด M22 **25 ตัว** จากคลัง 7 → คลัง 4 | **คงไว้** | `rollback_setting_wh.sql` |
| 6 | แก้ราคา **OUT M22-3** (stock_detail id=6129) `380 → 680` | **คงไว้** | `rollback_m223_price.sql` |
| 7 | (ครั้งแรก) ย้าย ADJUST ทั้ง 57 ใบ → มิ.ย. แล้ว rollback หมด | **ย้อนแล้ว (ไม่มีผลค้าง)** | `rollback_adjust_dates.sql` |
| 8 | ย้าย ADJUST **M19** 10 ใบ ก.ค. → 30 มิ.ย. | **คงไว้** | `rollback_m19_dates.sql` |
| 9 | เพิ่มใบ OUT สังเคราะห์ **M19 Face shield 19-00069** x1 @700 (คลัง3→sub34, 8 มิ.ย.) — ปิด gap 700 ให้ตรงบัญชี | **คงไว้** | `rollback_m19_faceshield_out.sql` |
| 10 | unwrap double-encode `data_json` เฉพาะ **7 แถว M19** (แก้หน้า balance อ่าน value-only) | **คงไว้** | `rollback_m19_unwrap_datajson.sql` |
| 11 | ลบ ADJUST ผี **19-00069** (recount ก.ค. +3@33.33=+100, qty/value ขัดกันเอง คลังจริง 2 อัน) | **คงไว้** | `rollback_faceshield_adjust_delete.sql` |
| 12 | ลบ ADJUST เกินซ้ำ **M19-14** (order 3526, ก.ค. +1@2,140) — แก้ซ้ำที่เกิดจากบั๊ก double-encode ทำหน้า balance โชว์ต่ำ | **คงไว้** | `rollback_m1914_adjust_delete.sql` |
| 13 | **M7 07-00169** ลบบรรทัดเบิกซ้ำ REQ-690139 (detail 872) — ยอดต้นติดลบ 1 | **คงไว้** | `rollback_m7_00169_dup_delete.sql` |
| 14 | **M7 M7-5** ลดใบเบิกเกิน REQ-690749 (detail 2174) 18→17 (เบิก 18 มีของ 12) | **คงไว้** | `rollback_m7_m75_qty.sql` |
| 15 | **M7 07-00412** เพิ่ม ADJUST recount +1@8,500 (31 พ.ค.) แก้ยอดต้นติดลบจากเบิกก่อนรับข้ามเดือน | **คงไว้** | `rollback_m7_00412_recount_adjust.sql` (apply: `apply_...`) |
| 16 | **M7 true-up** เพิ่ม value-only ADJUST 26 ใบ (30 มิ.ย.) ปรับยอดแต่ละ item = บัญชี → M7 มิ.ย. = 676,740.70 เป๊ะ | **คงไว้** | `rollback_m7_trueup.sql` (apply: `apply_m7_trueup.sql`) |

| 17 | **M22** ลบ ADJUST พันกัน 4 ใบ (M22-2 3ใบ 3446/47/48 + 22-00234 3451, เกิดจาก double-encode แก้ซ้ำ) แล้วเพิ่ม ADJUST สะอาด 2 ใบ (30 มิ.ย.): M22-2 recount −1@380→12,130 · 22-00234 value-only −225→7,500 → M22 มิ.ย. = 325,282.71 เป๊ะ | **คงไว้** | `rollback_m22_adjust_cleanup.sql` (re-insert 4) + `rollback_m22_clean_adjust.sql` (ลบ 2) · apply: `apply_m22_clean_adjust.sql` |

| 18 | **M4 04-00154** ลบ ADJUST พันกัน 3 ใบ (3510/3511/3512, double-encode ตัดซ้ำ −2,400) + เพิ่ม value-only −1,200 สะอาด (30 มิ.ย.) → 04-00154 = 0 → M4 = 0 (=บัญชี) | **คงไว้** | `rollback_m4_00154_cleanup.sql` (re-insert 3) + `rollback_m4_00154_clean.sql` (ลบ 1) |

> ✅ 2026-07-22: เช็ค M5/M6/M8/M26 = 0 ตรงบัญชี (ตัดหมดแล้วจริง) · M4 มี 04-00154 ตัดเกินจาก double-encode แก้แล้ว

| 19 | **M1** ลบ adjust กดซ้ำ/เกิน 4 ใบ: 01-00200 (3416/3417 +445×2), M1-20 (3403 value-only +70 ซ้ำ qty), 01-00130 (3414 −31.22 เกิน) → M1 = 302,012.95 เป๊ะ | **คงไว้** | `rollback_m1_adjust_dup_delete.sql` |
| 20 | **M3** ลบ adjust กดซ้ำ 1 ใบ: 03-00089 สเปรย์ปรับอากาศ (3439 value-only +59.16 ซ้ำ qty 3440) → M3 = 473,321.96 | **คงไว้** | `rollback_m3_00089_dup_delete.sql` |

> **Pattern กดซ้ำ 15-16 ก.ค.:** M1/M3 เคย verify ตรงแล้ว แต่มีคนคีย์ adjust recount ซ้ำ (value-only + qty วิธีเดียวกัน หรือกดปุ่มซ้ำ) เข้ามาภายหลัง ทำให้เพี้ยน → หมวดที่ verify ก่อนหน้านี้ควรเช็คซ้ำก่อนปิดจริง

**⚠️ หมายเหตุ M7 true-up (รายการ 16):** เป็นการ **plug ให้ยอดตรงบัญชี** ตามที่ผู้ใช้เลือก ไม่ได้แก้ต้นตอจริง (opening deficit −16,604 งวดก่อน + recount 21 ก.ค. เกิน +12,471 หักล้างกันไม่ลงตัว) · net plug = −12,471.25 · ถ้าจะแก้ให้สะอาดจริงต้องไล่ opening deficit 15 item (bucket 4) + ตรวจ recount ที่เกิน ~2 เท่า (bucket 3) ทีหลัง · recount 21 ก.ค. = ของรอบ ก.ค. รอจ่าย (ผู้ใช้ยืนยันว่าปกติ)

**หมายเหตุสำคัญเรื่องการแก้ข้อมูล:**
- คอลัมน์ `stock_order.order_date` = DATETIME · `stock_item_warehouse_setting.updated_at` = **INT (unix timestamp)** → ห้ามเซ็ต `NOW()` (จะ error out of range)
- setting move: ย้ายเฉพาะ 25 ใบที่**ยังไม่มี** setting คลัง 4 (อีก 13 ใบมีอยู่แล้ว ห้ามแตะ = ชน unique key)

---

## 4. สถานะตัวเลขปัจจุบัน (ต่อหมวด)

| หมวด | หน้า balance | ยอดยกไป มิ.ย. | ตรงกัน? | หมายเหตุ |
|---|--:|--:|:-:|---|
| **M1** วัสดุสำนักงาน | 302,012.95 | 302,012.95 | ✅ | ADJUST อยู่ มิ.ย. |
| **M3** งานบ้านงานครัว | 473,321.95 | 473,321.95 | ✅ | ADJUST อยู่ มิ.ย. |
| **M22** การแพทย์ทั่วไป | 338,262.71 (bal=ก.ค.) | 325,282.71 | ✅ | ปิด มิ.ย. = บัญชี 325,282.71 เป๊ะ (2026-07-22) |

**M22 เหลือต่างจากบัญชี 605** = 3 รายการที่บัญชีปรับมือ (ดูข้อ 6-7):
- M22-2 +380 (ระบบ 21 ชิ้น / บัญชีนับ 20)
- 22-00234 +225 (value-only −225 โดนดรอป)
- ~~M22-3 +300~~ **แก้แล้ว** (ราคา OUT 380→680)

**M19 วัสดุทันตกรรม — ✅ กระทบยอดตรงแล้ว (2026-07-21):**
- ยอดส่งบัญชี = **498,106.87** · ยอดระบบหลังแก้ = **498,106.86** (ต่าง 0.01 ปัดเศษสตางค์สะสม = ตรง)
- **เทียบราย item ครบ 436 รายการ closing ตรงทุกตัว** (ต่ำกว่าครึ่งสตางค์)
- เรื่อง value-only ไม่ใช่ปัญหา: บัญชีพับ adjust (~19,876.53) เข้า "ยอดต้น" ไปแล้ว ระบบลงเป็น adjust ในเดือน มิ.ย. → ผล closing เท่ากัน
- **ต่างจริงเหลือแค่ 700 ตัวเดียว** = `19-00069` Face shield: บัญชีนับจ่าย 1 อัน (700) ใน มิ.ย. แต่ระบบไม่ได้ลง เพราะการโอนจากคลังหลัก3→คลัง34 (ใบ `IN-691811`/order 3311, 8 มิ.ย.) ถูกบันทึกเป็น **IN ลงคลัง SUB** ไม่ใช่ OUT ของคลังหลัก → รายงานปิดเดือนมองไม่เห็น
- **แก้:** เพิ่มใบ OUT สังเคราะห์เฉพาะ item นี้ (order_no `RECON-M19-20260608-19-00069`, order id 3565) qty1 @700 คลัง3→sub34 ตั้ง `ref='V1'` ไม่ให้กระทบยอดคลัง34 · rollback: `rollback_m19_faceshield_out.sql` (apply: `apply_m19_faceshield_out.sql`)

**⚠️ ปัญหาเชิงระบบที่เจอ (ยังไม่แก้):** pattern "IN ลงคลัง SUB" (order_type=IN, main_warehouse_id เป็นคลัง SUB) มี **1,217 ใบทั้งระบบ** (จาก v1) เฉพาะ มิ.ย.2026 confirmed ~40 ใบ กระจายทุกหน่วยเบิก → เป็นการโอนออกจากคลังหลักที่ไม่ถูกนับเป็น OUT ในการปิดเดือน **หมวดอื่น (เช่น M7) อาจมี closing เกินจากกลไกเดียวกัน** M19 บังเอิญโดนแค่ใบเดียว (19-00069) เพราะบัญชีนับเฉพาะ item นั้น

---

## 5. ⚠️ ความไม่สอดคล้องที่ต้องตัดสินใจ

**การวาง ADJUST งวด ก.ค. ไม่สม่ำเสมอข้ามหมวด** (สถานะ ณ ปัจจุบัน):

| หมวด | ADJUST ใน มิ.ย. | ADJUST ใน ก.ค. |
|---|--:|--:|
| M1 | 18 | 0 |
| M3 | 23 | 0 |
| M22 | 0 | 8 |
| M2 | 1 | 3 |
| M4 | 9 | 2 |
| M7 | 17 | 1 |
| M18 | 8 | 4 |
| M19 | 12 | 0 |

- **M1, M3** = ย้ายรายการตรวจนับ ก.ค. เข้า มิ.ย. หมด
- **M22** = ย้อนกลับให้อยู่ ก.ค.
- **M2, M4, M7, M18** = ยังไม่แตะ (รายการตรวจนับ ก.ค. ยังอยู่ ก.ค.)

👉 **ต้องเคาะนโยบายเดียว:** รายการตรวจนับกลางเดือน ก.ค. ถือเป็นของงวด **มิ.ย.** (ปิดยอดสิ้นเดือน) หรือ **ก.ค.** (เกิดจริง ก.ค.)? แล้วทำให้ทุกหมวดเหมือนกัน

---

## 6. บั๊กที่พบ / สถานะแก้ไข

### บั๊ก #1 — `data_json` double-encode ✅ แก้ write path + unwrap M19 แล้ว / migration เต็มยังไม่รัน
> ✅ 2026-07-21: **`loadLedgerValues` (หน้า balance) อ่านชั้นเดียว** → ตกหล่น value-only adjust มิ.ย. ของ M19 รวม **11,186.25** (JSON_TYPE=STRING, JSON_EXTRACT คืน NULL → qty×price=0). unwrap เฉพาะ 7 แถว M19 แล้ว (backup: `rollback_m19_unwrap_datajson.sql`) → balance M19 461,973.81 → **473,160.06** ถูกต้อง. หมวดอื่นยังไม่ unwrap (รอ migration เต็ม หลังลบ dup M1/M2)

- `data_json` เป็นคอลัมน์ชนิด `json` แต่ `StockAdjustController` เขียน `->data_json = json_encode([...])` (สตริง) → Yii encode ซ้ำ → `JSON_TYPE=STRING` (double-encoded)
- กระทบ **57 แถว** (ADJUST order ทุกใบ + OUT บางส่วน) · จุดผิด: `StockAdjustController.php:269, 303, 310, 462, 493` (แก้เป็น assign array ตรงๆ)
- **ผลร้ายแรง:** value-only ADJUST **20 แถวมูลค่าถูกดรอป** เพราะ `JSON_EXTRACT` ชั้นเดียวอ่าน `value_delta` ไม่ได้ → รวมมูลค่าหาย **~12,332 บาท**
- เพิ่ม **migration** unwrap แถวที่เป็น STRING กลับเป็น OBJECT แล้ว: `m260721_120000_unwrap_inventory_stock_data_json.php` (เงื่อนไขเฉพาะ `JSON_TYPE='STRING'` + inner JSON valid + inner เริ่มด้วย object/array)

### บั๊ก #2 — ปิดเดือนไม่อ่าน `value_delta` ✅ แก้แล้ว
- `computeMonthlyRows` คิดมูลค่า ADJUST = `qty × unit_price` → value-only (qty=0) ได้ 0 เสมอ
- เพิ่ม logic อ่าน `value_delta` ในบล็อก ADJUST แล้ว โดยรองรับทั้ง JSON object และ double-encoded string ระหว่างรอ migration

### บั๊ก #3 — จ่ายเกินสต็อก (over-issue) ที่ 07-00169
- 07-00169 (M7): qty คงเหลือ 0 แต่ ledger = **−1,400** เพราะจ่ายออก 15 ชิ้น รับเข้า 13 ชิ้น (lot BMT2601-078 รับ 2 จ่าย 9 ครั้ง) → FIFO allocation ผิด

---

## 7. รายการยอดแปลกทั้งระบบ (audit)

### กลุ่ม A — value-only ADJUST ที่มูลค่าถูกดรอป (20 รายการ ทุกหมวด)
ตัวใหญ่: 19-00219 (+4,800), 19-00048 (+2,664.30), 19-00309 (+1,560), M19-14 (+1,070), 04-00154 (−1,200), 04-00155 (+960), 22-00234 (−225 = `ADJ-20260716-102829-786`)
- **กดซ้ำ:** 01-00200 (+445 ×2, M1) · 02-00025 (+500 ×2, M2)

### กลุ่ม B — มูลค่าผี (qty=0 แต่มีมูลค่า)
- 07-00169 (M7): **−1,400** (over-issue — บั๊ก #3)
- M22-3 (M22): **+380** (คู่ ADJUST ก.ค. +1@760/−1@380 ราคาไม่สมมาตร)

### กลุ่ม C — ข้อมูลเสียอื่นๆ
- item_code เป็นชื่อไทย: `สายคล้องแขน(arm sling) 2-5 ขวบ`, `...5-7 ขวบ (SS)` (M22) — ควรตั้งรหัสจริง

---

## 8. งานที่ควรทำต่อ (เรียงความสำคัญ)

- [ ] **เคาะนโยบายงวด ADJUST ตรวจนับ ก.ค.** (ข้อ 5) แล้วจัดทุกหมวดให้เหมือนกัน
- [x] **แก้บั๊ก #1 double-encode** (write path + migration file) — แก้โค้ดเขียน `data_json` เป็น array แล้ว · เพิ่ม migration `m260721_120000_unwrap_inventory_stock_data_json.php` แล้ว (**ยังไม่รัน migration บน DB**)
- [x] **แก้บั๊ก #2** ปิดเดือนอ่าน `value_delta` (คู่กับ #1) — `computeMonthlyRows()` รองรับ value-only qty=0 และอ่านได้ทั้ง JSON object / double-encoded string
- [ ] **แก้บั๊ก #3** over-issue 07-00169 (ตรวจ FIFO allocation ทั้งระบบ เผื่อมีตัวอื่น)
- [ ] ลบรายการกดซ้ำ 01-00200, 02-00025 (ก่อนแก้ #1 ไม่งั้นมูลค่าเด้งเกิน)
- [ ] ตั้ง item_code จริงให้ 2 รายการ arm sling
- [ ] **commit** โค้ด `ReportController.php` (valuation fix) — สร้าง branch ใหม่ก่อน (อยู่ `dev`)
- [ ] พิจารณาให้หน้า balance/ปิดเดือน แสดง**หมายเหตุ**ว่า "ยอดยกไป = ภาพ ณ สิ้นงวด" กันสับสน

---

## 9. วิธี verify / reproduce (รันใน container)

```bash
# ตัวเลขปิดเดือนต่อหมวด (เขียน PHP ชั่วคราวเรียก computeMonthlyRows)
docker exec dansai php <script.php>
# โครงสร้างเรียก: ReportController::buildOpeningForMonth($wh,2026,6) + computeMonthlyRows($wh,2026,6,$opening)
# คลังหลัก = [1,2,3,4,7] · หมวดกรองจาก categorise (name='asset_item', group_id='MATER', category_id='M22')

# หน้า balance ต่อหมวด: ReportController::loadLedgerValues([1,2,3,4,7]) + กรองด้วย setting+active
# (ต้อง Yii::setAlias('@web','/') ก่อน ถ้าจะเรียก loadBalanceData เต็ม)

# query DB ตรง
docker exec -i dansai_db mysql --default-character-set=utf8mb4 -uroot -pdocker dansai < query.sql
```

**สำคัญ:** host PHP 8.5 พังกับ vendor เก่า → ต้องรันผ่าน `docker exec dansai php ...` เสมอ · mysqldump/mysql อยู่ใน container `dansai_db`

---

## 10. ไฟล์ที่เกี่ยวข้อง

- โค้ดแก้: `modules/inventoryV2/controllers/ReportController.php` (uncommitted)
- Backup rollback: `docs/close-month-recon-202506/backups/*.sql` (7 ไฟล์)
- Excel ที่ทำให้ผู้ใช้ (อยู่ `~/Downloads/`): `ADJUST_M22_มิถุนายน2569.xlsx`, `M22_กระทบยอด_มิถุนายน2569.xlsx`
- Excel ต้นฉบับผู้ใช้: `~/Desktop/รายงานสรุปวัสดุคงคลังประจำเดือน มิถุนายน.xlsx` (ยอดส่งบัญชี M22 = 325,282.71)
