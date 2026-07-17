# Checklist: รายงานแผนจัดซื้อวัสดุ

## Scope
- [x] ได้รับอนุมัติให้เริ่มงาน
- [x] ทำเฉพาะหน้า "ดูรายงาน + export Excel"
- [x] ไม่เพิ่ม CRUD และไม่เพิ่ม schema
- [x] ปรับ theme v4 ให้รองรับ block `page-action` และ `sub-title` โดยยัง fallback กับ block `action` เดิม
- [x] เพิ่ม tooltip อธิบาย `ประมาณการใช้รวม`, `ประมาณการซื้อรวม`, `มูลค่าจัดซื้อรวม` ใน KPI และหัวตาราง
- [x] ตัดคอลัมน์ `ปีงบประมาณ` ออกจากรายงาน/Excel และย้าย `ลำดับ` มาเป็นคอลัมน์แรก
- [x] ตัดการแสดงคอลัมน์ `ประมาณการมูลค่าจัดซื้อรายไตรมาส 1-4` ออกจากรายงาน/Excel
- [x] แก้ cell แรกของตารางให้แสดง `ลำดับ` แทน `ปีงบประมาณ`
- [x] ปรับปุ่ม `ส่งออก Excel` ให้ใช้ SweetAlert ยืนยันและ fetch/blob download ตาม `PRODUCT.md`
- [x] ตัดคอลัมน์ค่าคงที่ออกจากหน้า preview และ export Excel
- [x] ปีงบประมาณเป็น filter อิสระ
- [x] ปริมาณการใช้ย้อนหลังคำนวณจาก `StockMonthlyReport`
- [x] เพิ่มตัวเลือกแหล่งข้อมูล: ปิดเดือนแล้ว / รวมเดือนที่ยังไม่ปิด
- [x] เพิ่ม filter ประเภทพัสดุจาก `asset_type` เฉพาะรายการ `group_id = MATER`

## Template Mapping
- [x] อ่าน template `/Users/patjawat/Downloads/template_แผนจัดซื้อวัสดุ.xlsx`
- [x] ระบุชีตหลัก `nonDrug`
- [x] ตัดคอลัมน์ค่าคงที่: จังหวัด, รหัสหน่วยบริการ 5 หลัก, ชื่อโรงพยาบาล, ผู้จัดทำแผน, เบอร์โทร, Line ID
- [x] map คอลัมน์ที่เหลือกับข้อมูลในระบบ
- [x] ตรวจ format export ให้ใกล้ template ที่ปรับแล้ว

## Implementation
- [x] เพิ่ม helper/query สำหรับรายงานแผนจัดซื้อวัสดุ
- [x] เพิ่ม action ดูรายงาน
- [x] เพิ่ม action export Excel
- [x] เพิ่ม view report ใหม่
- [x] ปรับ UX/UI ตาม PRODUCT.md โดยใช้ Bootstrap classes เท่านั้น: ย้ายปุ่มส่งออกเข้า action ของรายงาน, เพิ่ม active filter badges และถอด custom CSS/class เฉพาะหน้าออก
- [x] เพิ่ม menu/entry point
- [x] ย้ายหัวหน้ารายงานเข้า block `page-title`/`sub-title` และย้าย `_menu_main` เข้า block `page-action`
- [x] เพิ่ม logic คำนวณเดือนที่ยังไม่ปิดด้วย `computeMonthlyRows()`
- [x] ส่ง filter ประเภทพัสดุไปทั้งหน้า preview และ export Excel

## Verification
- [x] ตรวจ PHP syntax
- [x] ตรวจ export header/format ด้วย script
- [x] ตรวจ `git diff` ไม่แตะ unrelated changes

## Notes For Next Handoff
- รายงานนี้ใช้ข้อมูลย้อนหลังจาก `StockMonthlyReport.total_out_qty`
- โหมด "ปิดเดือนแล้ว" ใช้เฉพาะข้อมูล snapshot ใน `StockMonthlyReport`
- โหมด "รวมเดือนที่ยังไม่ปิด" ใช้ `StockMonthlyReport` สำหรับเดือนที่มี snapshot และคำนวณเดือนที่ยังไม่มี snapshot จาก `StockOrder` + `StockDetail` ผ่าน `computeMonthlyRows()`
- ตัวเลือกประเภทพัสดุถูกจำกัดจาก `Categorise asset_type` ที่มีรายการ `StockItem` active ใน `group_id = MATER`
- ปีงบประมาณไทยแปลงเป็นปี ค.ศ. โดยลบ 543
- ช่วงปีงบประมาณคือ ต.ค. ปีก่อนหน้า ถึง ก.ย. ปีที่เลือก
- หากไม่มีราคาเฉลี่ยจากประวัติรับเข้า จะใช้ `0` เพื่อไม่เดาราคา
- ประมาณการปริมาณใช้ในปีที่เลือก = ค่าเฉลี่ยปริมาณใช้ย้อนหลัง 3 ปีงบประมาณ
- ประมาณการปริมาณซื้อ = `max(ประมาณการใช้ - ปริมาณคงคลังยกมา, 0)`
- ราคา/หน่วยนับใช้ราคาเฉลี่ยถ่วงน้ำหนักจากยอดรับเข้าใน 3 ปีงบย้อนหลัง ถ้าไม่มีจะ fallback เป็นมูลค่าจ่ายออก/ปริมาณจ่ายออก
- มูลค่ารายไตรมาสแบ่งจากมูลค่าจัดซื้อทั้งปีเป็น 4 ส่วน โดยไตรมาส 4 รับส่วนต่างจากการปัดเศษ
