# โมดูล KPI ประจำปีรายบุคคล (kpi)

จัดการตัวชี้วัดผลการปฏิบัติงาน (KPI) รายบุคคลต่อปีงบประมาณ โดยใช้ KPI จาก JD เป็นตั้งต้น
บันทึกผลงานรายงวด และสรุปคะแนนถ่วงน้ำหนักทุกรอบ 6 เดือน

## ปีงบประมาณ
- `fiscal_year` เก็บเป็น พ.ศ. เช่น 2569 = **ต.ค. 2025 – ก.ย. 2026**
- เดือนงบประมาณเรียง ต.ค. = 1 … ก.ย. = 12 (`KpiEntry::fiscalMonthToCalendar()` แปลงเป็นเดือนปฏิทิน)
- รอบสรุป: **H1 = ต.ค.–มี.ค.**, **H2 = เม.ย.–ก.ย.**, **FULL = ทั้งปี**

## Flow หลัก
1. **สร้างชุด** (`kpi_cycle`) — seed KPI จาก JD revision ปัจจุบัน (`source_type='jd'`, `source_jd_section_id`) + หัวหน้า/HR เพิ่มเองได้ (`source_type='manual'`)
2. **หัวหน้า/HR ยืนยันความเหมาะสม + อนุมัติชุด** → `kpi_cycle.status = active` ถึงจะเริ่มบันทึกได้
3. **เจ้าของ KPI กรอกผลรายงวด** (`kpi_entry`) ตาม `frequency` (monthly/quarterly/yearly)
4. **รอบสรุป 6 เดือน** — หัวหน้ายืนยันคะแนน (`kpi_item_score.status = confirmed`) เป็นขั้นสุดท้าย

## เพิ่ม / ลด / แก้ KPI กลางปี
- **เพิ่ม**: insert `kpi_item` ใหม่ (`source_type='manual'`)
- **ลด**: soft remove — `kpi_item.status='removed'` + `removed_by/at/reason` (ผลรายงวดเดิมคงอยู่ ไม่ลบ)
- **แก้เป้า/น้ำหนัก**: overwrite ได้เลย **ไม่เก็บ audit ระหว่างทาง**
- **freeze เฉพาะตอนสรุป**: ณ รอบ H1/H2/FULL ค่าจะถูก snapshot ลง `kpi_item_score`
  (`indicator_snapshot`, `target_snapshot`, `weight_snapshot`, `result_snapshot`) เมื่อ `confirmed` แล้ว
  จะไม่กระทบจากการแก้ `kpi_item` ภายหลัง

## การให้คะแนน
- แต่ละ KPI มี `weight` (%) รวมทั้งชุด = 100
- `kpi_item_score.achievement_pct` = ร้อยละบรรลุเป้า (numeric คำนวณจากผล/เป้า, qualitative หัวหน้าให้)
- `score` = `achievement_pct × weight ÷ 100`
- คะแนนรวมของรอบ = ผลรวม `score` ของทุก item ที่ `active` ในรอบนั้น

## สิทธิ์การเข้าถึง (3 ระดับ)
| บทบาท | ขอบเขต | ทำอะไรได้ |
|---|---|---|
| **เจ้าหน้าที่ (เจ้าของ)** | KPI ของตนเอง | กรอกผลรายงวด, สรุปผลตนเอง |
| **หัวหน้าหน่วยงาน** | ผู้ใต้บังคับบัญชาใน org subtree ของตน | เพิ่ม/ลด/ยืนยัน KPI, ยืนยันผล, ยืนยันคะแนน + ดูภาพรวมหน่วยงาน |
| **HR / admin** | ทุกคน | ทุกอย่าง + ภาพรวมทั้งองค์กร |

**การระบุหัวหน้า:** ใช้ `Organization.data_json['leader1']` ที่มีอยู่แล้ว (ดู `Employees::isOrgLeader()` และ `orgUnits()`)
หัวหน้าระดับกลุ่มงาน (lvl 1) เห็นทุกหน่วยงานลูก (lvl 2) ใต้ตน — resolve ผ่าน nested-set subtree ของ `Organization`

## หน้าจอที่วางแผน (เฟส UI)
- **ภาพรวม (หัวหน้า/HR)**: ตารางรายบุคคลต่อปีงบ — สถานะชุด, คะแนนรวมต่อรอบ, ความคืบหน้าการกรอก → คลิกเข้ารายบุคคล
- **รายบุคคล**: ชุด KPI + กริดผลรายเดือน (ต.ค.–ก.ย.) + สรุป H1/H2
- **ของฉัน (เจ้าหน้าที่)**: กรอกผลรายงวดของตนเอง

## ตาราง
- `kpi_cycle` — ชุด KPI ต่อพนักงาน/ปีงบ (unique emp_id+fiscal_year)
- `kpi_item` — KPI แต่ละตัว
- `kpi_entry` — ผลงานรายงวด (unique item+period_type+period_index)
- `kpi_item_score` — คะแนน + snapshot ต่อรอบ (unique item+round)

## การรัน Migration
```bash
php yii migrate --migrationPath=@app/migrations
```
