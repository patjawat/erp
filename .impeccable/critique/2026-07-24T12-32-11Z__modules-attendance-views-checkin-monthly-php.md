---
target: หน้าสรุปการลงเวลารายเดือน (attendance monthly matrix)
total_score: 20
p0_count: 2
p1_count: 3
timestamp: 2026-07-24T12-32-11Z
slug: modules-attendance-views-checkin-monthly-php
---
# Critique — สรุปการลงเวลารายเดือน (`modules/attendance/views/checkin/monthly.php`)

Target: หน้าสรุปการลงเวลารายเดือน (matrix 271 คน × 31 วัน) + `CheckinController::buildMonthlyMatrix()`
Register: product
หมายเหตุ: หน้าจริงอยู่หลัง login ตรวจด้วย browser overlay ไม่ได้ ใช้ source review + วัดผลจริงผ่าน console command + query ฐานข้อมูลจริงแทน

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 2 | ไม่บอกเกณฑ์ "สาย" (08:30 ซ่อนใน const), ไม่บอกว่าข้อมูลครอบคลุมถึงวันไหน, เปลี่ยนเดือนแล้วไม่มี loading |
| 2 | Match System / Real World | 2 | "ขาด" ไม่ตรงความจริง — ระบบมี checkin_record แค่ 32 แถว/7 คน แต่หน้าแสดง 271 คน |
| 3 | User Control and Freedom | 2 | ไม่มี reset filter, ไม่มี sort, ไม่มี drill-down กลับไปดูรายคน |
| 4 | Consistency and Standards | 2 | ปุ่ม Export ไม่ตาม pattern มาตรฐานใน DESIGN (confirm→loading→success + fetch/blob); ใช้สีเขียวเองแทน `btn-success` |
| 5 | Error Prevention | 1 | ไม่มีอะไรกันผู้อ่านเข้าใจผิดว่าทั้งโรงพยาบาลขาดงาน; `try/catch` กลืน exception เงียบ (เคยทำให้ข้อมูลลาหายมาแล้ว) |
| 6 | Recognition Rather Than Recall | 3 | legend ครบ, tooltip บอกรายละเอียดครบทุกเหตุการณ์ในวันนั้น |
| 7 | Flexibility and Efficiency | 1 | 271 คนไม่มีช่องค้นหา ไม่มีเรียงลำดับ ไม่มี filter "เฉพาะคนที่มีปัญหา" |
| 8 | Aesthetic and Minimalist Design | 3 | restraint ดี, token ตรง DESIGN, ไม่มี decoration ส่วนเกิน |
| 9 | Error Recovery | 2 | query ล้ม = แสดงว่างเปล่าเงียบๆ ผู้ใช้ไม่รู้ว่าข้อมูลหาย |
| 10 | Help and Documentation | 2 | legend ช่วยได้ แต่ไม่บอกที่มาข้อมูล/เกณฑ์/ความหมายของช่องว่าง |
| **Total** | | **20/40** | **Needs work** |

## Anti-Patterns Verdict

**LLM assessment**: ไม่ใช่ AI slop ผ่าน — ไม่มี card grid ซ้ำ, ไม่มี eyebrow, ไม่มี gradient text, ไม่มี hero-metric, ไม่มี glassmorphism, ไม่มี primary tint เป็น background decoration, ไม่มี text-uppercase สีสถานะไม่เกินงบ ≤3 หลัก (เขียว/ส้ม + ม่วง/เขียวหัวเป็ดเป็นตัวอักษร) โครงหน้าเป็น matrix ที่มีเหตุผลจริงตามงาน ไม่ใช่ scaffold

**Deterministic scan**: `detect.mjs` บน view file → `[]` (0 findings)

**Visual overlays**: ทำไม่ได้ หน้าอยู่หลัง login และห้ามกรอก credential — fallback เป็น source review + วัดผลจริงจาก DB/console

## Overall Impression

งานฝีมือดี แต่รายงานกำลังบอกเรื่องที่ไม่จริง

ตารางสวย อ่านง่าย token ตรงระบบ tooltip มีประโยชน์จริง แต่ข้อมูลจริงในระบบมี **checkin_record 32 แถว จาก 7 คน ทั้งระบบ** (ก.ค. 2569 มีแค่ 2 แถว) ขณะที่หน้ารายงานไล่ 271 คน × วันทำการ ผลคือช่อง "—" (ขาด) ประมาณ 4,300 ช่องที่ไม่ได้แปลว่าขาดงานจริง แต่แปลว่า "ยังไม่ได้เริ่มใช้ระบบ" ถ้าเอารายงานนี้เสนอผู้บริหารตอนนี้ จะเป็นการกล่าวหาทั้งโรงพยาบาล

โอกาสเดียวที่ใหญ่ที่สุด: แยก "ไม่มีข้อมูล" ออกจาก "ขาดงาน" ให้ชัด แล้วรายงานจะเชื่อถือได้ทันที

## What's Working

1. **Tooltip เป็น single source of truth ต่อวัน** — รวมลงเวลา/ลา/ไปราชการ/วันหยุด ไว้ที่เดียว ซ้อนกันได้ (ลาครึ่งวันแล้วมาลงเวลา) ใช้ delegate ตัวเดียวคุม 8,400 เซลล์ ไม่สร้าง instance ทีละตัว
2. **Batch prefetch ทุกแหล่งข้อมูลรอง** — avatar/ลา/ไปราชการ/วันหยุด ดึงชุดเดียวทั้งหมด ไม่ใช่ต่อแถว
3. **State precedence เขียนชัดและถูก** — เสาร์อาทิตย์ > วันหยุด > ลา > ราชการ > อนาคต > ขาด อ่านโค้ดแล้วเข้าใจกฎทันที และ "ไปราชการ/ลา/วันหยุด" ไม่ถูกนับเป็นขาด

## Priority Issues

### [P0] "ขาด" เป็นค่า default ของทั้งตาราง
- **Why it matters**: ข้อมูลจริง — `checkin_record` ทั้งตารางมี 32 แถว, 7 คน, ช่วง 25 ก.พ. – 6 ก.ค. 2569; เดือน ก.ค. มี 2 แถว หน้ารายงานแสดง 271 คน จึงเกิด "—" (ขาด) ~4,300 ช่องที่เป็นเท็จ รายงานนี้ใช้ตัดสินคนได้ ความผิดพลาดจึงมีต้นทุนสูงกว่าปัญหาความสวยงามทั้งหมดรวมกัน
- **Fix**: เพิ่ม state `no-data` แยกจาก `absent` — กำหนดวันเริ่มใช้ระบบ (go-live) ระดับองค์กรหรือรายหน่วยงาน; วันก่อน go-live หรือคนที่ยังไม่เคยลงเวลาเลย = ช่องว่างจาง + tooltip "ยังไม่เริ่มใช้ระบบลงเวลา" และ **ไม่นับใน "รวมขาด"**; แถบสรุปบอกตรงๆ ว่า "ครอบคลุมบุคลากรที่เริ่มใช้ระบบแล้ว X จาก 271 คน"
- **Suggested command**: `/impeccable harden modules/attendance/views/checkin/monthly.php`

### [P0] N+1 — 779 queries / 2.9 วินาที ต่อการเปิดหน้า 1 ครั้ง
- **Why it matters**: วัดจริงด้วย console command: `persons: 271 | queries: 779 | time: 2924 ms` ต้นเหตุคือ `$emp->positionName` และ `$emp->departmentName()` (→ `empDepartment`) ถูกเรียกใน loop ต่อคน = 271 × ~2.85 queries ผิดกฎ performance ของ DESIGN ที่ batch prefetch ส่วนอื่นไว้หมดแล้ว และ Excel export ก็เรียก `buildMonthlyMatrix()` ตัวเดียวกัน จึงช้าซ้ำ
- **Fix**: `$empQuery->with(['positionName', 'empDepartment'])` ควรลดเหลือ ~8 queries
- **Suggested command**: `/impeccable optimize modules/attendance/controllers/CheckinController.php`

### [P1] ไม่มีคอลัมน์ "รวมขาด" ทั้งที่เป็นตัวเลขที่ HR ใช้จริงที่สุด
- **Why it matters**: มีรวมไปราชการ/รวมลา/รวมสาย แต่ตัวที่ต้องตามจริงๆ คือขาด ผู้ใช้ต้องนับ "—" เองทีละแถว 31 ช่อง
- **Fix**: เพิ่ม `absentCount` ใน `buildMonthlyMatrix()` + คอลัมน์ sticky ตัวที่ 4 + คอลัมน์ใน Excel (ต้องทำหลัง P0 ไม่งั้นตัวเลขจะผิดทั้งหมด)
- **Suggested command**: `/impeccable craft คอลัมน์รวมขาด`

### [P1] ปุ่ม Export ไม่ตาม pattern มาตรฐานของระบบ
- **Why it matters**: DESIGN.md กำหนด flow `confirm → loading → success` ผ่าน SweetAlert + `fetch()/blob()` เพื่อให้ loading ผูกกับ completion จริง หน้านี้เป็น `<a href>` เปล่า กด 271 คน × 31 วันแล้วรอ ~3 วินาทีโดยไม่มี feedback อะไรเลย และปุ่มใช้ `.att-btn--success` สีเขียวที่นิยามเอง แทน `btn btn-sm btn-success` ที่ DESIGN บอกว่า "ห้ามใส่ border/สีเอง"
- **Fix**: ยก `exportExcel()` จาก `modules/inventoryV2/views/report/_balance.php` มาใช้ตรงๆ + เปลี่ยน class ปุ่ม
- **Suggested command**: `/impeccable polish modules/attendance/views/checkin/monthly.php`

### [P1] มือถือใช้ไม่ได้จริง
- **Why it matters**: ตารางกว้าง 228 + 31×42 + 3×64 ≈ **1,722px** บนจอ 375px ต้อง scroll 2 ทิศทาง ซึ่ง DESIGN ระบุเป็น anti-reference ตรงตัว ("Dense desktop table ที่ยัดลงจอเล็ก") และ PRODUCT บอกว่าบุคลากรเปิดผ่านมือถือระหว่างปฏิบัติงาน
- **Fix**: `@media (max-width: 820px)` สลับเป็นมุมมองรายคน — เลือกคน 1 คนแล้วแสดงเป็นปฏิทินเดือน 7 คอลัมน์ (หรือ list รายวันเฉพาะวันที่มีเหตุการณ์) แทนที่จะย่อ matrix
- **Suggested command**: `/impeccable adapt modules/attendance/views/checkin/monthly.php`

## Persona Red Flags

**เจ้าหน้าที่ HR (Power User — ทำรายงานนี้ทุกสิ้นเดือน)**
- ต้องหาคนคนเดียวใน 271 แถว: ไม่มีช่องค้นหาชื่อ ไม่มี sort ต้องเลื่อนตาไล่
- อยากรู้ว่า "ใครสายบ่อยสุด": ไม่มี sort ตาม รวมสาย ต้องส่งออก Excel ไป sort เอง
- อยากรู้ว่า "วันไหนคนมาสายเยอะ": ไม่มีแถวรวมท้ายตารางต่อวัน
- กด Export แล้วเงียบ ~3 วินาที: ไม่รู้ว่ากดติดไหม กดซ้ำ

**หัวหน้ากลุ่มงาน (First-Timer — เปิดปีละไม่กี่ครั้ง)**
- เห็น "—" เต็มตาราง: ไม่มีอะไรบอกว่านั่นแปลว่า "ยังไม่เริ่มใช้ระบบ" ไม่ใช่ "ลูกน้องขาดงาน"
- ไม่รู้ว่า "สาย" นับจากกี่โมง: 08:30 ไม่ปรากฏที่ไหนในหน้าเลย
- คลิกที่ชื่อคน: ไม่เกิดอะไรขึ้น (คาดว่าจะเห็นประวัติรายคน)

## Minor Observations

- คลิกชื่อ/แถว ไม่ลิงก์ไปหน้าประวัติรายคน (`checkin/index`) — drill-down ที่ผู้ใช้คาดหวังโดยอัตโนมัติ
- ไม่มี `<caption>` และ `scope="col"/"row"` บน matrix 8,400 เซลล์ — screen reader หลงทาง
- tooltip ตั้ง `trigger: 'hover focus'` แต่ `<td>` ไม่ focusable → คีย์บอร์ดเข้าไม่ถึงรายละเอียดเลย
- `try/catch` ครอบ query สำคัญแล้วกลืน exception เงียบ (เคยทำให้ข้อมูลลาหายทั้งหมดมาแล้ว) — ควร log
- ลาครึ่งวัน: schema มี `date_start_type` / `leave_time_type` แต่ยังไม่ได้ใช้ (ข้อมูลจริง ก.ค. ยังไม่มี half-day จึงยังไม่เร่ง)
- เวลาออกงาน: `CHECK_TYPE_OUT` มีในระบบแต่มีข้อมูลแค่ 8 แถวทั้งตาราง — ยังไม่คุ้มทำจนกว่าคนจะใช้จริง
- ไม่มี print CSS — รายงานราชการมักต้องปริ้นแนบเสนอ
- ไม่มี "สรุประดับกลุ่มงาน" — ผู้บริหารอยากเห็นภาพรวมต่อหน่วยงาน ไม่ใช่รายคน 271 แถว

## Questions to Consider

- ถ้ารายงานนี้พิมพ์แล้ววางบนโต๊ะผู้อำนวยการพรุ่งนี้ ตัวเลข "ขาด" ตอนนี้ปกป้องได้ไหม
- ผู้ใช้จริงเปิดหน้านี้เพื่อตอบคำถามอะไร — "ใครมีปัญหา" หรือ "ทุกคนเป็นยังไง" ถ้าเป็นข้อแรก ทำไม default ถึงแสดงทุกคน
- 271 แถวคือสิ่งที่ผู้ใช้ต้องการจริง หรือเป็นแค่สิ่งที่ query คืนมา
