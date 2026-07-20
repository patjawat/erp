# เงื่อนไขการทำงาน: หน้า /inventory-v2/receive/view

หน้ารายละเอียดใบรับเข้าวัสดุ (`ReceiveController::actionView` → `views/receive/view.php`)
Model หลัก: `StockOrder` (`order_type = 'IN'`) พร้อม `stockDetails` (`stock_detail`, FK `stock_order_id` ON DELETE CASCADE)

---

## 1. สถานะเอกสาร (StockOrder::status)

| สถานะ | ความหมาย | ผลต่อสต็อก |
|---|---|---|
| `DRAFT` | ฉบับร่าง ยังไม่ยืนยันรับเข้า | ไม่กระทบ `stock_balance` / `stock_detail.remain_qty` เลย |
| `CONFIRMED` | ยืนยันรับเข้าแล้ว | ตอนสร้าง: `stock_balance.balance_qty` เพิ่มขึ้น, `stock_detail.remain_qty = qty` |
| `CANCELLED` | ยกเลิกแล้ว | ดู §4.2 (มีบั๊ก การคืนสต็อกไม่แม่นยำ) |

DRAFT ที่ยังไม่ยืนยัน: หน้าแสดง banner เตือน "ยังไม่อัปเดตยอดคลัง" พร้อมปุ่มลัดไปหน้าแก้ไข ([view.php:15-29](../views/receive/view.php))

---

## 2. เงื่อนไขการแสดงปุ่ม (Action bar)

| ปุ่ม | แสดงเมื่อ | Route | Method |
|---|---|---|---|
| ย้อนกลับ | เสมอ | `index` | GET |
| ส่งออก Excel | เสมอ (ไม่เช็คสถานะ) | `export-excel` | GET (target=_blank) |
| แก้ไข | `status !== CANCELLED` | `update` | GET/POST |
| ยกเลิกใบรับเข้า | `status !== CANCELLED` (**ไม่เช็คว่าถูกเบิกไปแล้วหรือยัง**) | `cancel` | POST + confirm |
| ลบใบรับเข้า | `$model->canDelete()` เป็น `true` — ถ้า `false` ปุ่มยัง**แสดง**แต่ `disabled` พร้อม tooltip เหตุผล (`getUndeletableReason()`) | `delete` | POST + confirm |

---

## 3. เงื่อนไขปุ่ม "แก้ไข" (Update)

- เงื่อนไขแสดง: `status !== CANCELLED` เท่านั้น (`ReceiveController::actionUpdate`)
- ถ้าเดิม `status === CONFIRMED`: ก่อนบันทึกค่าใหม่ ระบบจะย้อนสต็อกเดิมออกก่อน (คล้ายแนวทาง cancel) แล้วค่อยรับเข้าใหม่ตามข้อมูลที่แก้ไข — ใช้ pattern เดียวกับ §4.2 จึงมีความเสี่ยงเดียวกันถ้าของถูกเบิกไปแล้วบางส่วนก่อนแก้ไข

---

## 4. เงื่อนไขปุ่ม "ยกเลิกใบรับเข้า" (Cancel)

**เงื่อนไขแสดง**: `StockOrder::canCancel()` → `return $this->status !== self::STATUS_CANCELLED;` — เช็คแค่สถานะ **ไม่เช็คว่าวัสดุถูกเบิกไปแล้วหรือยัง** ([StockOrder.php:720](../models/StockOrder.php))

**สิ่งที่เกิดขึ้นเมื่อกด** (`ReceiveController::actionCancel`):

1. เปลี่ยนสถานะเป็น `CANCELLED`
2. เรียก `InventoryService::moveStock($item, $mainWarehouseId, $detail->qty, 'OUT', ...)` ต่อทุกรายการ — ใช้ **qty เต็มจำนวนตอนรับเข้า** ไม่ใช่ `remain_qty` ปัจจุบัน
3. ภายในจะเรียก `processFIFO()` ซึ่งไล่หัก `remain_qty` จาก **lot ใดก็ได้ของ item นั้นในคลังนั้นที่ยังมีของเหลือ** เรียงวันที่เก่าสุดก่อน (ไม่จำเพาะเจาะจงว่าต้องเป็น lot ของใบที่กำลังยกเลิก)

> ⚠️ **ข้อควรระวัง (บั๊กที่ยังไม่ได้แก้)**: ถ้าวัสดุของใบนี้ถูกเบิกใช้ไปแล้วบางส่วน การกดยกเลิกจะเกิด 1 ใน 2 กรณี:
> - **ของในคลังไม่พอ** (รวมทุก lot ของ item นั้น) → โยน exception "พัสดุรหัส ... ในคลังมีไม่พอจ่าย" → rollback → ใบยังเป็น CONFIRMED เหมือนเดิม (ปลอดภัยแต่ผู้ใช้จะงง)
> - **ของในคลังยังพอ** (มี lot อื่น/ใบรับเข้าอื่นชดเชยได้) → ยกเลิก "สำเร็จ" แต่:
>   - อาจไปหัก `remain_qty`/`stock_balance` จาก **ใบรับเข้าอื่นที่ไม่เกี่ยวข้อง** แทน
>   - `remain_qty` ของใบที่ถูกยกเลิกเองจะถูกเซ็ตกลับเป็นเท่ากับ `qty` เต็ม (ลบล้างประวัติว่าเคยถูกเบิกไปแล้วบางส่วน ทั้งที่ของจริงจ่ายออกไปแล้วเอาคืนไม่ได้)
>
> ยังไม่ได้แก้ในรอบงานนี้ — ถ้าต้องการให้ปลอดภัยเท่าปุ่มลบ ต้องเพิ่มเงื่อนไขใน `canCancel()` แบบเดียวกับ `canDelete()` (เช็ค pool ของ item+lot+คลัง) ก่อนอนุญาตให้ยกเลิก

---

## 5. เงื่อนไขปุ่ม "ลบใบรับเข้า" (Delete)

**เงื่อนไขแสดง/กดได้**: `StockOrder::canDelete()` ([StockOrder.php:735](../models/StockOrder.php))

```
canDelete() = true  เมื่อ:
  status !== CANCELLED
  AND (ถ้า status === CONFIRMED)
      ทุก stock_detail ของใบนี้ ต้องอยู่ใน "pool" (item_code + lot_number + main_warehouse_id)
      ที่ SUM(qty) เท่ากับ SUM(remain_qty) พอดี (ยังไม่มีการเบิกออกจาก lot นี้เลย แม้จะเป็นจากใบรับเข้าใบอื่นที่ใช้ lot เดียวกันก็ตาม)
```

> หมายเหตุสำคัญ: เช็คระดับ **pool** ไม่ใช่แค่แถวของใบนี้เอง เพราะหลายรายการไม่มีการควบคุม lot จริง (ใช้ `lot_number = '-'` ร่วมกันหลายสิบใบรับเข้า) การตัดสต็อกแบบ FIFO จะไล่ตัดจากใบเก่าสุดก่อน ถ้าเช็คแค่แถวตัวเองจะพลาดกรณีที่ lot ถูกเบิกไปแล้วผ่านใบพี่น้อง (sibling) ในพูลเดียวกัน — ดูรายละเอียดใน commit ที่แก้ปัญหานี้

**สิ่งที่เกิดขึ้นเมื่อกด** (`ReceiveController::actionDelete`):

1. เช็ค `canDelete()` — ถ้า `false` แสดง flash error พร้อมเหตุผล ไม่ลบ
2. เปิด transaction
3. ถ้า `status === CONFIRMED`: หักคืน `stock_balance` ต่อทุกรายการด้วย `InventoryService::updateBalance(item, mainWarehouseId, qty, 'OUT', lot)` แล้วถ้ายอดเหลือ ~0 จะ**ลบแถว `stock_balance` นั้นทิ้ง**
4. ถ้า `status === DRAFT`: ข้ามขั้นตอนหักคืน (ไม่เคยแตะ balance ตั้งแต่แรก)
5. ลบ `stock_order` → `stock_detail` ถูกลบอัตโนมัติผ่าน FK `ON DELETE CASCADE`
6. Commit + flash success / Rollback + flash error

**ขอบเขตที่ไม่รองรับ**: ใบที่ `status === CANCELLED` ลบไม่ได้ผ่านปุ่มนี้ (ปุ่มจะ disabled พร้อมเหตุผล "ไม่สามารถลบใบที่ยกเลิกแล้วได้")

---

## 6. สรุปความสัมพันธ์ตาราง

```
stock_order (IN, ใบรับเข้า)
   └─ stock_detail (FK stock_order_id, ON DELETE CASCADE)
         - qty        = จำนวนที่รับเข้าตอนแรก
         - remain_qty = จำนวนที่ "ยังไม่ถูกเบิกออก" จาก lot นี้ (ลดลงเมื่อมีการจ่ายของ FIFO)

stock_balance (ไม่มี FK ผูกกับ stock_order/stock_detail)
   - key: (item_code, warehouse_id, lot_number)
   - เป็นยอดรวม (pool) อาจถูกสมทบจากหลายใบรับเข้าที่ item+lot+คลังเดียวกัน
```

การลบ/ยกเลิกใบรับเข้าที่ปลอดภัย ต้องเช็คที่ระดับ **pool** (item+lot+คลัง) เสมอ ไม่ใช่แค่แถวของใบนั้นเอง เพราะ `stock_balance` เป็นยอดรวมข้ามใบรับเข้า และการตัดสต็อกแบบ FIFO ก็ไม่ยึดติดกับใบรับเข้าใบใดใบหนึ่งโดยเฉพาะ
