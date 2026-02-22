# Flow ระบบคลังมาตรฐาน: Requisition → อนุมัติ → Issue → ตัดสต็อก

## 1. Flow ที่ถูกต้อง (ตามที่ต้องการ)

```
[1] ผู้ใช้สร้าง Requisition (ใบขอเบิก)
         ↓
[2] หัวหน้ากดอนุมัติ (ยังไม่ตัดสต็อก)
         ↓
[3] คลังสร้าง Issue จาก Requisition (หรือ "ดำเนินการจ่าย" ตามใบที่อนุมัติแล้ว)
         ↓
[4] ระบบตัดสต็อก (ตอนยืนยันจ่าย / confirm Issue)
```

---

## 2. สถานะเอกสาร (Status) ที่แนะนำ

| สถานะ      | ความหมาย | ตัดสต็อก |
|-----------|----------|----------|
| DRAFT     | ฉบับร่าง / ยังไม่ส่ง | ไม่ |
| PENDING   | รอหัวหน้าอนุมัติ     | ไม่ |
| APPROVED  | หัวหน้าอนุมัติแล้ว รอคลังจ่าย | ไม่ |
| CONFIRMED | คลังจ่ายของแล้ว      | **ใช่** (ตัดตอนเปลี่ยนเป็นสถานะนี้) |
| CANCELLED | ยกเลิก               | ไม่ |

- ขั้นตอนที่ 2 (หัวหน้าอนุมัติ): เปลี่ยน Requisition จาก PENDING → **APPROVED** โดย**ไม่เรียก** moveStock
- ขั้นตอนที่ 4 (ระบบตัดสต็อก): เกิดขึ้นเมื่อคลังกด "จ่ายของ" / "ยืนยัน Issue" แล้วเปลี่ยนสถานะเป็น **CONFIRMED** และเรียก moveStock/processFIFO

---

## 3. ออกแบบสองแบบ (ไม่ต้องเพิ่มตารางใหม่)

### แบบ A: ใช้เอกสารใบเดียว (Requisition = ใบที่คลังจ่ายด้วย)

- **stock_order** หนึ่ง record ใช้ทั้งเป็นใบขอเบิกและใบจ่าย
- **Status flow:** DRAFT → PENDING → APPROVED → (คลังกดจ่าย) → CONFIRMED
- "คลังสร้าง Issue" = **ไม่สร้างเอกสารใหม่** แต่หมายถึงคลังเข้าเมนู "ดำเนินการจ่าย" เลือกใบที่ status = APPROVED แล้วกรอก Lot/จำนวนที่จ่าย แล้วกดยืนยัน → ระบบตัดสต็อก + เปลี่ยน status เป็น CONFIRMED
- **ตารางเดิมใช้ได้ทั้งหมด** ไม่ต้องเพิ่มฟิลด์ ไม่ต้องเพิ่มตาราง

### แบบ B: แยกเอกสาร Requisition กับ Issue

- **Requisition** = 1 record ใน `stock_order` (order_type=OUT, source_type=REQUEST, status ถึง APPROVED)
- **Issue** = อีก 1 record ใน `stock_order` (order_type=OUT, source_type=REQUEST หรือ ISSUE) ที่อ้างอิงใบขอเบิก
- การอ้างอิงใช้ฟิลด์ **ref** ใน `stock_order` เก็บเลขที่ใบขอเบิก (เช่น REQ-20250219-0001) หรือถ้าต้องการ FK ชัดเจนจึงค่อยเพิ่ม **requisition_id** (FK ไป stock_order.id ของใบ Requisition)
- คลัง "สร้าง Issue จาก Requisition" = สร้าง StockOrder ใบใหม่ + คัดลอก stock_detail จาก Requisition แล้วบันทึก ref = order_no ของ Requisition
- ตอน "ยืนยันจ่าย" Issue = เปลี่ยน status Issue เป็น CONFIRMED + เรียก moveStock/processFIFO ตามรายการใน Issue
- **ตารางเดิม:** ใช้ได้ ไม่บังคับเพิ่มคอลัมน์ (ใช้ ref ก่อนได้) ถ้าต้องการให้ชัดเจนจึงเพิ่ม `requisition_id` ใน `stock_order`

---

## 4. ต้องปรับเปลี่ยนตารางเดิมไหม?

**ไม่จำเป็นต้องเพิ่มตารางใหม่**

- **stock_order** – ใช้ได้ตามเดิม มี `order_type`, `source_type`, `status`, `ref` อยู่แล้ว
- **stock_detail** – ใช้ได้ตามเดิม
- **stock_balance** – ใช้ได้ตามเดิม

**การปรับที่อาจทำ (ตามแบบที่เลือก):**

| แบบ | การปรับตาราง | หมายเหตุ |
|-----|----------------|----------|
| A   | **ไม่ต้องเพิ่มคอลัมน์** | เพียงเพิ่ม status PENDING, APPROVED ใน logic/ENUM ถ้า DB อนุญาต หรือใช้ค่าที่มีอยู่แล้ว |
| B   | **ไม่บังคับ** | ใช้ `ref` เก็บเลขที่ใบ Requisition ได้เลย ถ้าต้องการ FK จริงค่อยเพิ่ม `requisition_id` ใน `stock_order` |

---

## 5. สิ่งที่ต้องแก้ในโค้ด (สรุป)

### ถ้าใช้แบบ A (แนะนำ ถ้าไม่ต้องการเอกสาร Issue แยก)

1. **RequisitionController::actionApprove**
   - ห้ามเรียก `InventoryService::moveStock` ในขั้นอนุมัติ
   - เปลี่ยน status เป็น **APPROVED** (ไม่ใช่ CONFIRMED)

2. **StockOrder**
   - รองรับ status **APPROVED** (เพิ่มใน rules/optsStatus หรือ ENUM ถ้า DB ใช้ ENUM)

3. **IssueController (หรือเมนู "ดำเนินการจ่าย")**
   - แสดงเฉพาะใบที่ `status = APPROVED` (และ order_type=OUT, source_type=REQUEST)
   - หน้า Process = ให้คลังเลือก Lot/จำนวน แล้วกดยืนยันจ่าย
   - ตอนยืนยันจ่าย: เรียก logic ตัดสต็อก (moveStock / processFIFO หรือแบบเลือก Lot ใน IssueController ที่มีอยู่) แล้วเปลี่ยน status เป็น **CONFIRMED**

4. **Requisition index/view**
   - แสดงปุ่ม "อนุมัติ" เฉพาะเมื่อ status = PENDING (หรือ DRAFT แล้วแต่นิยาม)
   - ไม่แสดงปุ่ม "จ่ายของ" ใน Requisition; ให้ไปจ่ายจากเมนู Issue (รายการที่ APPROVED)

### ถ้าใช้แบบ B

- เพิ่ม flow "สร้าง Issue จาก Requisition" = สร้าง StockOrder ใหม่ + คัดลอก stock_detail + set ref (หรือ requisition_id)
- หน้า "รายการ Issue" กรองหรือแสดงจาก ref/requisition_id
- ตอน confirm Issue ใช้ logic ตัดสต็อกจาก **Issue** (StockOrder ใบใหม่) ไม่ใช่จาก Requisition

---

## 6. สรุปคำตอบคำถาม

| คำถาม | คำตอบ |
|-------|--------|
| Flow ที่ถูกต้อง | ผู้ใช้สร้าง Requisition → หัวหน้าอนุมัติ (ไม่ตัดสต็อก) → คลังดำเนินการจ่าย/สร้าง Issue จาก Requisition → ระบบตัดสต็อกตอนยืนยันจ่าย |
| ต้องปรับเปลี่ยนตารางเดิมไหม? | **ไม่ต้อง** ใช้ stock_order, stock_detail, stock_balance ได้ตามเดิม อาจเพิ่มเฉพาะ status (APPROVED) ใน logic/ENUM หรือคอลัมน์ ref/requisition_id ถ้าใช้แบบ B |
