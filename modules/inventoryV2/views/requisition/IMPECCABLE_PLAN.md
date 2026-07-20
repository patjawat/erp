# แผนปรับปรุง `_form.php` (ใบขอเบิกวัสดุ) — impeccable

ไฟล์เป้าหมาย: [`modules/inventoryV2/views/requisition/_form.php`](_form.php)
มาตรฐานอ้างอิง: `modules/inventoryV2/views/sub-stock/issue.php` + `PRODUCT.md` (Design Tokens / Component Vocabulary)
ผลตรวจก่อนแก้: **11/20 (Acceptable)** — P1×3 · P2×5 · P3×3

> กติกา: แตะเฉพาะ UI/CSS/มาร์กอัป **ห้ามแตะ** business logic, route, AJAX behavior, schema (Design Principle #10)
> selector ที่นับจำนวนแถว (`#item-table tbody tr`) ยังคงถูกต้องเพราะ empty state เป็น `<div>` นอก `<tbody>` ไม่ใช่ `<tr>`

---

## Checklist

### P1 — ต้องแก้ก่อน release

- [x] **1. Empty state ของตารางรายการ** — เพิ่ม `#item-empty-state` (icon + "ยังไม่มีรายการ" + hint), ถอด `min-height:400px` ตายตัว, เพิ่ม JS `refreshItemEmptyState()` เรียกหลัง add/remove/clear/prefill/init; ซ่อน `<thead>` ตอนว่าง
- [x] **2. เลิกใช้ `bg-primary-gradient` บน card-header** — เปลี่ยนเป็น `--surface-2` + title `--ink-1`; ปุ่ม `เพิ่มวัสดุ` = `btn-primary`, `เติมตามเกณฑ์` = `btn-outline-secondary`
- [x] **3. Responsive ตารางกรอกของ** — ครอบ `<table id="item-table">` ด้วย `.table-responsive`

### P2 — แก้ก่อน release

- [x] **4. `<label>` ผูก input + ย้ายปุ่มออกนอก label** — `for="issue_reason"` และ `for="approver_emp_id_select"`; ย้าย dropdown "ตัวเลือก" ออกเป็น sibling ของ label (ห้าม `<button>` ซ้อนใน `<label>`)
- [x] **5. aria-label ปุ่มลบให้ครบทุก template** — เพิ่มใน `#row-template` (บรรทัด ~232)
- [x] **6. แทนสีฮาร์ดโค้ดใน CSS ด้วย token** — ประกาศ token block scope `.requisition-form`; แปลง `#fff`, `rgba(0,0,0,.15)`, `#f8f9fa`, `rgba(0,0,0,.1)` เป็น `--surface`/`--line`/`--surface-3`
- [x] **7. Status badge ใช้ semantic token** — แทน `bg-success-subtle text-success` ฯลฯ ด้วยสี token (`#15803d`/`#b45309`/`#b91c1c`)
- [x] **8. ข้อความ muted เล็กเกิน** — `font-size:11px` → `12px` + `--ink-3` ในตารางสรุปยอด

### P3 — ขัดเงา

- [x] **9. ถอด `me-2` เกิน** ที่ปุ่ม submit (container เป็น `gap-2` แล้ว)
- [x] **10. `loading="lazy"`** บน thumbnail ทุกจุด (prefill markup + JS builder + option render)
- [x] **11. เครื่องหมาย `*` ใช้ `--danger`** แทน Bootstrap `text-danger`
- [x] **12. Motion (animate)** — enter/exit ของแถวตอนเพิ่ม-ลบวัสดุ (transform/opacity, ~180ms `--ease`), เคารพ `prefers-reduced-motion`

---

## หมายเหตุการทดสอบ

- ตรวจ syntax: `docker exec dansai php -l modules/inventoryV2/views/requisition/_form.php`
- ทดสอบด้วยตา: หน้าใหม่ (empty state), เพิ่มวัสดุ, ลบวัสดุ, เปลี่ยนคลัง (clear), หน้าแก้ไข (มีรายการเดิม)

## สถานะ

- เริ่ม: 2026-07-16
- ผู้ทำ: (Claude) — อัปเดต checkbox เมื่อเสร็จแต่ละข้อ
- **เสร็จครบ 12/12 ข้อ (2026-07-16)** — `php -l` ผ่าน ไม่มี syntax error
- ค้าง: ยังไม่ได้ทดสอบด้วยตาบน browser จริง (empty state / เพิ่ม-ลบแถว / เปลี่ยนคลัง / หน้าแก้ไข) — แนะนำให้ผู้ทดสอบเปิดหน้า `requisition/create` และ `requisition/update` ยืนยัน flow ก่อน merge
