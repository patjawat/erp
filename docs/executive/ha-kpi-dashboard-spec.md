# HA KPI Dashboard — สเปกการออกแบบ (ERP)

- **สถานะ:** ร่าง (Draft) — จุดตัดสินใจหลักเคาะครบแล้ว เริ่มเฟส 0 ได้; HA Part seed รอโครงสร้าง HA ฉบับ 6
- **วันที่:** 2026-09-21
- **ผู้เขียน:** decha + Claude
- **ที่มา:** ศึกษาแนวคิดจากระบบภายนอก "GoDashKPI (Smart Health Insights)" แล้ว **ออกแบบใหม่ให้เข้ากับสถาปัตยกรรม/ดีไซน์ของ ERP** (ไม่ลอกทั้งระบบ)
- **โมดูลปลายทาง:** `executive` (ข้อมูลสำหรับผู้บริหาร) — ไม่ใช่โมดูลแยก

---

## 1. เป้าหมาย

ให้ผู้บริหารเห็น **ภาพรวมตัวชี้วัด (KPI) ทั้งโรงพยาบาลตามมาตรฐาน HA** ในที่เดียว:
สรุปสถานะ PASS/GAP, แนวโน้มหลายปี, กรองตามกลุ่ม/ตอน HA/หน่วยงาน และเจาะดูรายตัวชี้วัด

**ขอบเขต (เคาะแล้ว):** ครอบ KPI ทั้ง รพ. **รวมตัวที่อยู่นอกแผนยุทธศาสตร์** (พยาบาล / ระบบงานคุณภาพ / รายแผนก) ไม่จำกัดเฉพาะตัวชี้วัดในแผนยุทธศาสตร์

**สถาปัตยกรรม (เคาะ 2026-09-21):** **ต่อยอดในโมดูล `pm` (ยุทธศาสตร์)** ให้เป็นบ้านของ KPI ทุกกลุ่ม — ไม่แยกโมดูล/ตาราง kpi_* ใหม่แบบ standalone
ตัวชี้วัดยุทธศาสตร์ **มีอยู่แล้ว** (`StrategyIndicator`) ส่วนกลุ่มอื่น ๆ **"เพิ่มจากโมดูลยุทธศาสตร์"**

### กลุ่มตัวชี้วัด (KPI groups) — ตามที่ผู้ใช้กำหนด

| # | กลุ่ม | ที่มาข้อมูล | ผูกกับ |
|---|---|---|---|
| 1 | ตัวชี้วัดระดับยุทธศาสตร์ | มีอยู่แล้ว `StrategyIndicator` (plan-linked) | แผนยุทธศาสตร์ (mission→goal) |
| 2 | ตัวชี้วัดของโรงพยาบาลสำหรับ Monitor | เพิ่มใหม่ (นอกแผน) | ระดับ รพ. |
| 3 | ตัวชี้วัดคุณภาพของทีมประสาน | เพิ่มใหม่ | `org_unit` (type = ทีมประสาน) |
| 4 | ตัวชี้วัดทางงานพยาบาล | เพิ่มใหม่ | กลุ่มการพยาบาล |
| 5 | ตัวชี้วัดของหน่วยงาน | เพิ่มใหม่ (คีย์ในหน้าตัวชี้วัด) | `org_unit` (หน่วยเดียวกับ owner ของ Service Profile) |

> `org_unit` มี "ทีมประสาน" อยู่แล้ว (memory: org-unit-registry) → กลุ่ม 3/5 ใช้ org_unit_id ร่วมกันได้
> กลุ่ม 5 ใช้หน่วยงานเดียวกับที่เป็น owner ของ Service Profile แต่ **ไม่ฝังลงในเอกสาร SP** (SP มีตัวชี้วัดของตัวเองอยู่แล้ว)

---

## 2. ศึกษา GoDashKPI: เอาอะไร / ปรับอะไร / ทิ้งอะไร

| ฟีเจอร์ GoDashKPI | การตัดสินใจใน ERP | เหตุผล |
|---|---|---|
| ตารางตัวชี้วัด + ค่า 5 ปี + badge PASS/GAP + sparkline | ✅ **เอา** (ทำในสไตล์การ์ด/ตาราง ERP, Bootstrap Icons) | คือหัวใจของ dashboard |
| การ์ดสรุป total/PASS/GAP/success% | ✅ **เอา** (ใช้ layout การ์ดแบบ executive เดิม) | ผู้บริหารต้องเห็นทันที |
| หน้ารายละเอียด + กราฟเทรนด์ (เป้า vs จริง) | ✅ **เอา** (ใช้ **ApexCharts** ที่มีในระบบแล้ว เช่น swot) | ไม่ผูก chart lib ของเขา |
| ตัวกรอง ปี/กลุ่ม/HA Part/หน่วยงาน/ค้นหา | ✅ **เอา** | จำเป็นเมื่อ KPI 300+ ตัว |
| pie ภาพรวมสถานะ | ✅ **เอา** (ApexCharts) | |
| ทิศทาง (สูง/ต่ำดี) → คำนวณ PASS/GAP | ✅ **เอา** แต่ใช้แนวคิด `operator` แบบ pm (>=, <=, =) | สอดคล้อง data model เดิม |
| Admin sidebar แยก (จัดการ KPI/กลุ่ม/รอบปี/ผู้ใช้) | 🔄 **ปรับ** — ใช้ระบบ admin/สิทธิ์ของ ERP ที่มีอยู่ ไม่ทำ sidebar ใหม่ | ERP มี RBAC + เมนูกลางแล้ว |
| Landing page แยก (hero + org profile) | ❌ **ทิ้ง** | ERP มี /me + navbar + หน้า "ข้อมูลโรงพยาบาล" (เพิ่งทำใน pm) แล้ว |
| ระบบ user/login ของตัวเอง | ❌ **ทิ้ง** | ใช้ auth/RBAC ของ ERP |

**หลักการ:** ไม่สร้าง "แอปซ้อนแอป" — ฝัง dashboard เป็นส่วนหนึ่งของ ERP ใช้ layout, navbar, สิทธิ์, ดีไซน์เดิม

---

## 3. ตำแหน่งและการนำเสนอ (ปรับ 2026-09-21: page-nav ของโมดูล pm)

**เปลี่ยนจากเดิม (executive) → มาเป็น page-nav ของโมดูล pm** (`modules/pm/views/_menu.php`)
executive **พักไว้ก่อน** ทำใน pm ให้เสร็จแล้วค่อยเชื่อมทีหลัง

### page-nav ของ pm (จัดใหม่)

| ลำดับ | pill | route | เนื้อหา |
|---|---|---|---|
| 1 | **ภาพรวม** | `/pm/default/index` | **เปลี่ยนเป็น KPI Dashboard รวมทุกกลุ่ม** (การ์ดสรุป + ตาราง union + pie) = ชุดนำเสนอผู้บริหาร |
| 2 | **ข้อมูลโรงพยาบาล** | `/pm/strategy-plan/profile` (default แผนปัจจุบัน) | วิสัยทัศน์/พันธกิจ/ค่านิยม (ย้ายจากปุ่มในหน้าแผนมาเป็น pill หลัก) |
| 3 | แผนยุทธศาสตร์ | `/pm/strategy-plan/index` | เดิม |
| 4 | ตัวชี้วัด | `/pm/strategy-catalog/index?type=indicator` | KPI ยุทธศาสตร์ (เดิม) |
| 5 | **KPI โรงพยาบาล** | `/pm/kpi/index` (ใหม่) | กลุ่ม 2-5 แบบ GDashboard: เพิ่ม/ลบ/แก้/จัดการ/แสดงผล |
| 6 | แผนงาน/โครงการ | `/pm/projects/index` | **ย้ายภาพรวมโครงการ/งบเดิมมาไว้ที่นี่** |
| 7 | รายงาน | `/pm/report/index` | เดิม |

### หน้าจอที่ต้องทำ (โมดูล pm)
1. **ภาพรวม = KPI Dashboard** (`DefaultController::actionIndex` เขียนใหม่)
   - การ์ดสรุป total/PASS/GAP/success% + ตัวกรอง (ปี/กลุ่ม/หน่วยงาน/ค้นหา) + ตาราง KPI union + pie
   - ภาพรวมโครงการ/งบเดิม → ย้ายไปเป็นส่วนหนึ่งของ pill แผนงาน/โครงการ
2. **KPI โรงพยาบาล** (`KpiController` ใหม่) — ทะเบียน+จัดการ+แสดงผลกลุ่ม 2-5 (GDashboard-style)
   - index (ตาราง+ตัวกรอง+สถานะ), create/update/delete, กรอกค่ารายปี, detail+กราฟ
3. **ข้อมูลโรงพยาบาล** — ทำเป็น pill หลัก (actionProfile default `StrategyPlan::current()`) + เอาปุ่มเดิมออกจากหน้า strategy-plan/view

**Service:** ใช้ `app\modules\pm\services\KpiRegistry` (ทำแล้วในเฟส 0) — `rows()/summary()` union strategy+standalone
**สิทธิ์:** ดู = ผู้ใช้โมดูล pm (`@`/`pmStrategyView`); จัดการ KPI = `kpiManage`

---

## 4. Data model (ปรับตามทิศทางใหม่ — build within pm)

### บริบท: ERP มีอะไรอยู่แล้ว
โมดูล `pm` มี `pm_strategy_indicator` + `pm_strategy_indicator_year` ฟิลด์ครบมาก
(target_value, actual_value, operator, definition, formula, unit, owner, baseline, weight, รายเดือน)
**แต่** `StrategyIndicator` ผูกกับแผน (ต้องมี `plan_id`, อยู่ใต้ mission→goal, มี publish/clone/version)
→ ตัวชี้วัดยุทธศาสตร์ (กลุ่ม 1) ใช้ของเดิมนี้ต่อ ไม่แตะ

### แนวทางที่เลือก: กลุ่ม 2-5 = ตารางพี่น้องใหม่ในโมดูล pm (Approach Y)

คงตารางยุทธศาสตร์เดิม **ไม่แตะเลย** (กัน publish/clone/import พัง) + เพิ่มทะเบียน KPI นอกแผนใน pm:

```
pm_kpi_group          ทะเบียนกลุ่มตัวชี้วัด (5 กลุ่มตามข้อ 2)
  - id, code, name, kind (strategy | standalone),
    icon, color, sort_order, is_active
  - กลุ่ม 1 (strategy) = marker ชี้ว่าดึงจาก StrategyIndicator
  - กลุ่ม 2-5 (standalone) = ใช้ pm_kpi_indicator

pm_kpi_ha_part        ทะเบียนตอน/หัวข้อ HA (อ้างอิง HA ฉบับ 6 — seed ภายหลัง)
  - id, code (เช่น I, II, III, IV), name, parent_id (null=ตอนหลัก / มี=หัวข้อย่อย),
    sort_order
  - เก็บเป็นทะเบียนแทน field ตายตัว → รองรับหัวข้อย่อยของ HA6 โดยไม่แก้ schema

pm_kpi_ha_map         **แผนที่ "KPI ตอบ HA Part"** (many-to-many) ← concept สำคัญ
  - id, source (strategy | standalone), source_id, ha_part_id, note
  - KPI 1 ตัว map ได้หลาย Part; Part 1 อัน ถูกตอบด้วยหลาย KPI
  - polymorphic ครอบทั้ง 2 แหล่ง: strategic (StrategyIndicator.id) + standalone (pm_kpi_indicator.id)

pm_kpi_indicator      ตัวชี้วัดนอกแผน (กลุ่ม 2-5) — ตัวแม่คงที่ข้ามปี
  - id, group_id, org_unit_id (null),
    name, unit, operator (>=,<=,=,>,<), definition, formula,
    evaluation_method, data_source, owner_name, frequency, sort_order,
    is_active, ref
  - **ไม่มี ha_part_id ตรง ๆ** — ความเชื่อม HA ไปอยู่ที่ pm_kpi_ha_map
  - กลุ่ม 5 (หน่วยงาน): ใช้ org_unit_id (หน่วยเดียวกับที่เป็น owner ของ Service Profile)

pm_kpi_indicator_year ค่ารายปีงบ (เป้า+ผลจริง)
  - id, kpi_indicator_id, fiscal_year, target_value, actual_value,
    status (auto: pass/gap/nodata), note
  - unique(kpi_indicator_id, fiscal_year)
```

> **แนวคิดหลัก:** KPI **ไม่ใช่** "ตัวชี้วัดของ HA" โดยตรง — แต่แต่ละตัว **"มีส่วนตอบ" HA ได้หลาย Part**
> เวลาทำรายงาน HA จะ **ดึง KPI ที่ map ไว้มาตอบในแต่ละ Part** (มุมมอง "ตาม HA Part")
> ตัวชี้วัดยุทธศาสตร์ (กลุ่ม 1) ก็ map ตอบ Part ได้เช่นกัน ผ่าน `source=strategy`

> **HA Part ต้องศึกษาเพิ่มจาก HA มาตรฐานฉบับที่ 6** เพื่อ seed โครงสร้างตอน+หัวข้อย่อยให้ถูกต้อง
> (schema พร้อมรองรับแล้ว รอเนื้อหามาเติม — ผู้ใช้จะส่งเอกสาร/โครงสร้าง HA6 ให้)

**การรวมข้อมูลเพื่อ dashboard (adapter/DTO กลาง):**
Service ฝั่ง executive อ่าน 2 แหล่งแล้ว normalize เป็น DTO เดียว
`KpiRow{ id, source(strategy|standalone), group, name, unit, ha_parts[], org_unit,
target, actual, status, series[ปี=>ค่า] }`  ← ha_parts เป็น list (map ได้หลาย Part)
- กลุ่ม 1 ← `StrategyIndicator` + `StrategyIndicatorYear`
- กลุ่ม 2-5 ← `pm_kpi_indicator` + `pm_kpi_indicator_year`

**สถานะ PASS/GAP — logic กลาง** helper `app\modules\pm\components\KpiStatus::evaluate($target,$actual,$operator)`
- ไม่มี `actual` → `nodata`; เทียบตาม operator (`>=` → actual≥target = PASS; `<=` → actual≤target = PASS)
- ใช้ helper เดียวกันทั้ง 2 แหล่ง (strategic ก็คำนวณด้วยตัวนี้) → เกณฑ์ PASS/GAP สอดคล้องกันทั้งระบบ

**กลุ่ม 5 (หน่วยงาน):** ใช้ `org_unit_id` เท่านั้น — **ไม่ฝังในหน้าเอกสาร Service Profile**
(SP มีตัวชี้วัดในเอกสารของตัวเองอยู่แล้ว) กลุ่มนี้คีย์ค่าใหม่ในหน้าตัวชี้วัด แล้ว dashboard กรองตามหน่วยงานได้

**ทางเลือกอื่น (Approach X — ไม่เลือก):** ทำ `plan_id` ให้ null ได้ + เพิ่ม `group_id` ใน `pm_strategy_indicator` ใช้ตารางเดียวทุกกลุ่ม — รวมง่ายกว่าแต่ต้องไล่ guard logic publish/clone/import ทั้งโมดูล เสี่ยงของเดิมพัง จึงเลือก Y

### หน่วยงาน
ใช้ทะเบียน `org_unit` กลาง (memory: org-unit-registry) เป็น `org_unit_id` — มี "ทีมประสาน" อยู่แล้ว

---

## 5. หน้าจอ (ออกแบบสไตล์ ERP)

**5.1 ภาพรวม = KPI Dashboard** (`/pm/default/index`)
- แถบสรุป 4 การ์ด: total / PASS / GAP / อัตราสำเร็จ%
- แถบตัวกรอง: ค้นหา / ปีงบ / กลุ่ม / หน่วยงาน + ปุ่มกรอง+ล้าง (สไตล์ inventoryV2)
- pie ภาพรวมสถานะ (ApexCharts) + สรุปตามกลุ่ม
- ตาราง KPI (union ทุกกลุ่ม): ชื่อ+กลุ่ม | หน่วยงาน | หน่วย | ค่า N ปี | เป้า | badge PASS/GAP | sparkline
- แถวคลิกได้ → หน้า detail

**5.2 KPI โรงพยาบาล** (`/pm/kpi/index` — กลุ่ม 2-5)
- ตาราง+ตัวกรอง+badge สถานะ + ปุ่มเพิ่ม/แก้/ลบ (GDashboard-style)
- ฟอร์ม create/update + กรอกค่ารายปี (เป้า+ผลจริง) inline หลายปี
- detail รายตัว + กราฟเทรนด์

**5.3 หน้า detail รายตัวชี้วัด**
- กราฟเทรนด์หลายปี: เส้นเป้า (เส้นประ) vs ผลจริง (เส้นทึบ) — ApexCharts
- panel รายละเอียด: คำนิยาม / สูตร / หน่วย / ทิศทาง / หน่วยงานรับผิดชอบ (+ HA Part เมื่อทำเฟส 5)
- กล่องสรุปผลประเมิน (บรรลุ/ไม่บรรลุ) + ปุ่มพิมพ์ (browser print)

**มาตรฐานดีไซน์ที่ยึด:** DESIGN.md (ref=inventoryV2), Bootstrap Icons, page-nav pill (`pm/views/_menu.php`), การ์ด shadow-sm, responsive มือถือ, ApexCharts

---

## 6. การจัดการข้อมูล (Admin CRUD)

จัดการ KPI นอกแผน (กลุ่ม 2-5) — **pill "KPI โรงพยาบาล" ในโมดูล pm** (`/pm/kpi/*`)
(ตัวชี้วัดยุทธศาสตร์กลุ่ม 1 จัดการที่ `strategy-catalog` เดิมไม่เปลี่ยน)
- CRUD `pm_kpi_indicator` (ฟอร์ม: กลุ่ม/หน่วยงาน/นิยาม/สูตร/หน่วย/ทิศทาง/ความถี่/ผู้รับผิดชอบ)
- กรอกค่ารายปี (เป้า+ผลจริง) — ตาราง inline หลายปี + คัดลอกข้ามปี
- (จัดการกลุ่ม `pm_kpi_group` — seed 5 กลุ่มไว้แล้ว ปกติไม่ต้องเพิ่ม แต่เผื่อแก้ชื่อ/สี/ลำดับ)
- นำเข้า Excel (เฟสท้ายสุด)

> กลุ่ม 5 (หน่วยงาน): เลือก `org_unit_id` = หน่วยเดียวกับ owner ของ Service Profile (ไม่ฝังในเอกสาร SP)

---

## 7. สิทธิ์ & route gate

- **ดู ภาพรวม/KPI Dashboard:** ผู้ใช้โมดูล pm (`@` / `pmStrategyView`) — pm/default เดิมเปิดให้ `@`
- **จัดการ KPI (CRUD กลุ่ม 2-5):** `kpiManage` (สร้างแล้วในเฟส 0 ผูก pm_planner/pm/admin)
- อย่าลืมเพิ่ม route ใหม่ (`/pm/kpi/*`) ใน `config/web.php` allowActions ถ้า RBAC ยังไม่ครอบ (memory: global-route-access-gate)
- executive: **พักไว้** — ทำ pm ให้เสร็จก่อน ค่อยเพิ่มการ์ด/ลิงก์ทีหลัง

---

## 8. จุดตัดสินใจ

### เคาะแล้ว ✅
- **Data model:** ต่อยอดในโมดูล pm — คงตารางยุทธศาสตร์เดิม + เพิ่ม `pm_kpi_*` สำหรับกลุ่ม 2-5 (Approach Y), dashboard union ผ่าน adapter/DTO
- **ตัวชี้วัดยุทธศาสตร์:** ใช้ของเดิม ไม่คีย์ซ้ำ — dashboard ดึงมาแสดงผ่าน adapter
- **กลุ่ม:** 5 กลุ่มตามข้อ 2 (2 ชั้น group→kpi, ไม่มี category)
- **Admin CRUD:** อยู่ในโมดูล pm (ควบคู่ strategy-catalog)
- **กลุ่มหน่วยงาน:** เชื่อม Service Profile ผ่าน owner_id

### เคาะเพิ่ม (2026-09-21) ✅
- **HA Part:** ทำเป็นทะเบียน `pm_kpi_ha_part` (ตอน + หัวข้อย่อย) — **ต้องศึกษา HA ฉบับ 6 เพื่อ seed** (ผู้ใช้จะส่งโครงสร้างให้)
- **Excel import:** เลื่อนไปหลังระบบหลักเสร็จ (เฟสท้ายสุด)
- **กลุ่ม 5:** ใช้ org_unit เฉย ๆ คีย์ในหน้าตัวชี้วัด — **ไม่ฝังในหน้า Service Profile**
- **KPI ↔ HA:** many-to-many (KPI ตัวหนึ่งตอบได้หลาย Part) ผ่าน `pm_kpi_ha_map` — ไม่ใช่ field เดี่ยว
- **ลำดับงาน:** กำหนด/คีย์ตัวชี้วัด + dashboard ให้เสร็จก่อน → **HA Part + mapping + มุมมองตาม Part เป็นเฟส 5** (หลังตัวชี้วัดพร้อม)

### เคาะเพิ่ม (2026-09-21 รอบ 2) ✅
- **ตำแหน่ง:** ย้ายจาก executive → **page-nav ของโมดูล pm**; ภาพรวม = KPI Dashboard; เพิ่ม pill **"KPI โรงพยาบาล"** (กลุ่ม 2-5)
- **ข้อมูลโรงพยาบาล:** ย้ายเป็น pill หลัก (default แผนปัจจุบัน) เอาปุ่มเดิมออกจากหน้าแผน
- **ภาพรวมโครงการ/งบเดิม:** ย้ายไปใต้ pill แผนงาน/โครงการ
- **executive:** พักไว้ก่อน (ทำ pm ให้เสร็จ)
- **สิทธิ์ดู:** ผู้ใช้โมดูล pm (`@`/`pmStrategyView`)

### ค้างรอเนื้อหา 📌
- โครงสร้าง **HA มาตรฐานฉบับที่ 6** (ตอน I-IV + หัวข้อย่อย) เพื่อ seed `pm_kpi_ha_part` — ใช้ตอนเฟส 5 (ไม่บล็อกเฟส 0-4)

---

## 9. แผนพัฒนาเป็นเฟส (เสนอ)

> **ลำดับงาน (ผู้ใช้กำหนด):** คีย์ตัวชี้วัดให้เสร็จก่อน → dashboard → **แล้วค่อยทำ HA Part + map + มุมมองตาม Part** (เฟส 5)

| เฟส | งาน | สถานะ |
|---|---|---|
| **0** | migration `pm_kpi_*` + models + `KpiStatus` + `KpiRow` + `KpiRegistry` (adapter union) + seed 5 กลุ่ม | ✅ **เสร็จ+ตรวจ backend ผ่าน 2026-09-21** |
| **1** | pill **"KPI โรงพยาบาล"** (`/pm/kpi/*`): CRUD ตัวชี้วัดนอกแผน + กรอกค่ารายปี + ตาราง/ตัวกรอง/สถานะ | ← เริ่มต่อไป |
| **2** | pill **ภาพรวม = KPI Dashboard** (สรุป+ตัวกรอง+ตาราง union+pie) + จัด `_menu.php` ใหม่ + ย้ายภาพรวมโครงการไปใต้ pill โครงการ + ย้าย ข้อมูลรพ. เป็น pill | |
| **3** | หน้า detail รายตัว + กราฟเทรนด์ (ApexCharts) | |
| **4** | คัดลอกค่าข้ามปี + พิมพ์รายงาน | |
| **5** | **HA Part**: migration `pm_kpi_ha_part` + `pm_kpi_ha_map` + เมนูจัดการ Part + map + **มุมมอง "ตาม HA Part"** | seed จาก HA ฉบับ 6 |
| **6** | **นำเข้า Excel** (หลังระบบหลักเสร็จ) | เลื่อนท้ายสุด |
| **7** | (ออปชัน) เชื่อม executive — การ์ด/ลิงก์มาที่ pm dashboard | พักไว้ |

---

## 10. มาตรฐาน ERP ที่ยึด (checklist)

- [ ] สาขา `decha_dev` เท่านั้น, commit เฉพาะไฟล์งานนี้ (ห้าม `git add -A`)
- [ ] migration วาง top-level path, รันแยกเฉพาะ kpi (มี migration ทีมอื่น pending)
- [ ] ไฟล์ไทย UTF-8 แก้ด้วย Edit/Write tool (ห้าม PowerShell Get/Set-Content)
- [ ] DESIGN.md: Bootstrap Icons, page-nav pill, การ์ด inventoryV2, DatepickerThai (พ.ศ.)
- [ ] ApexCharts (self-host เหมือน swot) สำหรับกราฟ
- [ ] ไม่แตะอาณาเขต Codex (attendance/payroll/finance-payable/dms/exit-interview)
- [ ] ทดสอบ backend ก่อน แล้วให้ผู้ใช้ตรวจผ่านเว็บ ก่อน push
