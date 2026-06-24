-- รันคำสั่งนี้ใน DB ถ้ายังไม่ได้รัน migration (เพิ่ม ADJUST ใน order_type)
-- ถ้าใช้ table prefix ให้เปลี่ยน stock_order เป็น ชื่อตารางจริง เช่น tbl_stock_order

ALTER TABLE `stock_order`
MODIFY COLUMN `order_type` ENUM('IN', 'OUT', 'TRANSFER', 'ADJUST') NOT NULL
COMMENT 'ทิศทาง: รับ, จ่าย, โอน, ปรับยอด';
