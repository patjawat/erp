# รีวิวการออกแบบระบบคลังสินค้า (inventoryV2)

เอกสารนี้สรุปจุดแข็ง จุดที่ควรปรับปรุง และข้อเสนอแนะของการออกแบบโมดูล inventoryV2

---

## 1. โครงสร้างโดยรวม

### 1.1 โมเดลหลักและความสัมพันธ์

| ตาราง | หน้าที่ | ความสัมพันธ์ |
|-------|---------|----------------|
| **stock_order** | หัวเอกสาร รับ/จ่าย/โอน | hasMany StockDetail, hasOne Warehouse (main/sub) |
| **stock_detail** | รายการบรรทัด (รหัสสินค้า, จำนวน, Lot, remain_qty สำหรับ FIFO) | belongsTo StockOrder, StockItem |
| **stock_balance** | ยอดคงเหลือต่อ (item, warehouse, lot) | อ้างอิง item_code, warehouse_id (ไม่มี FK ไป warehouses) |
| **stock_item** |  master พัสดุ | getStockBalance(warehouse_id) รวมจาก stock_balance |

- **จุดแข็ง:** แยกหัวเอกสารกับรายการชัดเจน รองรับ Lot และ FIFO ผ่าน `remain_qty` + `stock_balance`
- **ข้อควรระวัง:** `stock_balance.warehouse_id` ไม่มี FK ไป `warehouses` (comment ใน migration ระบุว่า "จาก warehouses.id") — ถ้าต้องการ integrity ควรเพิ่ม FK หรือตรวจสอบที่ application

### 1.2 สถานะเอกสาร (stock_order.status)

- **DRAFT** → **PENDING** → **APPROVED** → **CONFIRMED** และ **CANCELLED**
- การตัดสต็อกเกิดเฉพาะเมื่อสถานะเป็น CONFIRMED (รับเข้าเมื่อ confirm รับ, จ่ายเมื่อคลังกดจ่ายใน Issue process)
- **จุดแข็ง:** Flow Requisition → อนุมัติ (ไม่ตัดสต็อก) → คลังจ่าย → ตัดสต็อก ตรงกับความต้องการและมีเอกสารอ้างอิงใน `docs/flow-requisition-issue.md`

---

## 2. บริการหลัก: InventoryService

- **moveStock(type IN/OUT):** รับเข้าใช้ `processReceive` (อัปเดต balance + ตั้ง remain_qty ที่รายการรับเข้า), จ่ายออกใช้ `processFIFO` (ตัดจาก IN details ตาม FIFO แล้วอัปเดต balance)
- **updateBalance:** สร้าง/อัปเดตแถวใน `stock_balance` ต่อ (item, warehouse, lot) และกันไม่ให้ balance_qty ติดลบ

**จุดแข็ง:**
- รวม logic การขยับสต็อกที่เดียว
- ใช้ transaction ใน processReceive/processFIFO
- FIFO อัตโนมัติสำหรับ OUT ที่เรียกผ่าน moveStock

**ข้อควรระวัง:**
- **IssueController::actionProcess** ไม่ได้เรียก `processFIFO` แต่เขียน loop หัก `remain_qty` เองแล้วเรียกเฉพาะ `updateBalance` (เพราะให้ผู้ใช้เลือก Lot เอง) — ถ้าอนาคตมีจุด OUT อื่นที่เลือก Lot เอง ควรพิจารณาแยก helper “หักตาม Lot ที่เลือก” ใน Service เพื่อไม่ให้ logic ซ้ำและลดโอกาสบั๊ก

---

## 3. Flow หลัก

### 3.1 รับเข้า (Receive)

- ReceiveController / MainStockController สร้าง/อัปเดต StockOrder (order_type=IN)
- เมื่อ confirm: เรียก `InventoryService::moveStock(..., 'IN', ...)` → processReceive อัปเดต balance และตั้ง remain_qty ที่ stock_detail
- **จุดแข็ง:** ชัดเจน รับเข้าแล้วถึงตัดสต็อก

### 3.2 ใบขอเบิก (Requisition) และจ่าย (Issue)

- สร้าง Requisition: StockOrder (OUT, REQUEST, DRAFT) + StockDetail
- อนุมัติ: เปลี่ยนเป็น APPROVED **ไม่ตัดสต็อก**
- คลังจ่าย: เมนู "รายการจ่ายพัสดุ" → เลือกใบ APPROVED → หน้า process กรอก Lot/จำนวนที่จ่าย → กดบันทึก → ตัดสต็อก + status = CONFIRMED
- **จุดแข็ง:** แยกบทบาทหัวหน้าอนุมัติกับคลังจ่ายได้ และมี canEdit/canCancel ช่วยจำกัดการแก้ไข/ยกเลิก

### 3.3 ยกเลิกใบเบิก (Requisition Cancel)

- ถ้าเดิมเป็น CONFIRMED: เรียก `InventoryService::moveStock(..., 'IN', ...)` เพื่อคืนสต็อก แล้วเปลี่ยนเป็น CANCELLED
- **จุดแข็ง:** ใช้ Service เดียวกัน ทำให้ยอดคงเหลือสอดคล้อง

---

## 4. จุดที่ควรปรับปรุง / ความเสี่ยง

### 4.1 IssueController::actionProcess — การตรวจสอบยอดก่อนหัก

- ตอนนี้: หักจาก `sourceLots` (IN details ที่มี remain_qty > 0 ตาม item + lot ที่เลือก) แล้วเรียก `updateBalance(..., qtyToProcess, 'OUT', ...)`
- **ความเสี่ยง:** ถ้ารวม `remain_qty` ของ sourceLots น้อยกว่า `qtyToProcess` (ผู้ใช้กรอกเกินหรือยอดเปลี่ยนระหว่างโหลดหน้า) หลัง loop จะยังมี `tempQty > 0` แต่ยังคงหัก balance ไปเต็ม `qtyToProcess` → อาจทำให้ balance ติดลบหรือข้อมูลไม่ตรงกับที่หักจริง
- **ข้อเสนอ:** หลัง loop ที่หัก sourceLots ให้เช็ค `if ($tempQty > 0)` แล้ว throw exception (ของไม่พอใน Lot ที่เลือก) และเรียก `updateBalance` เฉพาะจำนวนที่หักได้จริง `(qtyToProcess - $tempQty)` ไม่ใช้ `qtyToProcess` เต็มจำนวน

### 4.2 คลังหลัก/คลังย่อย (main/sub) ใน Requisition

- ใน `_form.php` คลังหลักถูกจำด้วย hardcode: `$mainWarehouseIds = [1,2,3,4,5,6,7]` แล้วแยก mainWarehouses / subWarehouses ตาม id
- **ความเสี่ยง:** ถ้าเพิ่ม/ลบหรือเปลี่ยนบทบาทคลังต้องแก้โค้ด
- **ข้อเสนอ:** ย้ายไปอยู่ที่ config หรือตาราง (เช่น warehouse.type = 'main'/'sub') แล้วดึงจาก DB

### 4.3 การใช้ InventoryService ในที่อื่น

- MainStockController มีการเรียก moveStock หลายจุด (receive, cancel, ฯลฯ) — แนะนำให้ตรวจสอบว่า flow ยกเลิก/คืนของสอดคล้องกับ RequisitionController (เช่น คืนเป็น IN แล้ว status เป็น CANCELLED) และไม่มีการหัก/คืนซ้ำ

### 4.4 StockBalance และ Warehouse

- ตาราง `stock_balance` ไม่มี FK ไป `warehouses` — ถ้ามีการลบหรือเปลี่ยน id คลังอาจเกิดข้อมูลค้าง
- **ข้อเสนอ:** ถ้านโยบาย DB อนุญาต ให้เพิ่ม FK จาก `stock_balance.warehouse_id` ไป `warehouses.id` (หรือตารางคลังที่ใช้จริง) เพื่อความสอดคล้องของข้อมูล

### 4.5 stock_monthly_report

- มีการสร้างตารางใน migration แต่ไม่มี ActiveRecord model ในโมดูล — ถ้ามีการใช้รายงานประจำเดือนควรเพิ่ม model และ logic อัปเดต; ถ้าไม่ใช้แล้วอาจปล่อยเป็น phase หลังหรือลบออกจาก migration ครั้งถัดไป

---

## 5. สรุปและข้อเสนอแนะลำดับความสำคัญ

| ลำดับ | รายการ | ประเภท |
|--------|--------|--------|
| 1 | แก้ไข IssueController process: ตรวจสอบยอดพอใน Lot ที่เลือก และหัก balance เฉพาะจำนวนที่หักได้จริง | แก้บั๊ก/ความเสี่ยงข้อมูล |
| 2 | ย้าย mainWarehouseIds ออกจาก hardcode ไป config หรือ master คลัง | รองรับการเปลี่ยนโครงสร้างคลัง |
| 3 | พิจารณาเพิ่ม FK stock_balance → warehouses (ถ้าเหมาะสมกับนโยบาย DB) | ความสอดคล้องข้อมูล |
| 4 | รวม/ใช้ helper ใน InventoryService สำหรับกรณี “จ่ายตาม Lot ที่เลือก” เพื่อไม่ให้ logic ซ้ำกับ IssueController | รักษาและ reuse logic |
| 5 | กำหนดใช้หรือไม่ใช้ stock_monthly_report และเพิ่ม model/อัปเดตถ้าต้องการ | ความสมบูรณ์ของฟีเจอร์ |

โดยรวมการออกแบบโมดูล inventoryV2 ชัดเจน แยกบทบาท Requisition/Issue ได้ดี และใช้ InventoryService เป็นจุดกลางสำหรับการขยับสต็อกและ balance — การแก้จุดเสี่ยงด้านยอดจ่ายใน Issue process และการย้าย config คลังจะทำให้ระบบมั่นคงและดูแลต่อได้ง่ายขึ้น
