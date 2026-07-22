-- Apply: เพิ่มใบ OUT กระทบยอด M19 (Face shield 19-00069 x1 @700, คลัง3 -> sub34, 8 มิ.ย.2569)
-- ref='V1' บนทั้ง order+detail เพื่อให้ loadLedgerValues ฝั่ง sub (คลัง34) ข้ามใบนี้ (ไม่ทำยอดคลัง34 เพี้ยน)
INSERT INTO stock_order
  (order_no, order_type, source_type, order_date, main_warehouse_id, sub_warehouse_id,
   contact_id, status, ref, data_json, created_by, updated_by, created_at, updated_at)
VALUES
  ('RECON-M19-20260608-19-00069', 'OUT', 'RECON', '2026-06-08 15:37:15', 3, 34,
   NULL, 'CONFIRMED', 'V1',
   JSON_OBJECT('recon', JSON_OBJECT(
     'reason', 'M19 close-month recon: missing June issue of face shield 19-00069 (physically transferred to wh34 via IN-691811/3311)',
     'target', 'M19 closing = 498106.87 match accounting',
     'created', '2026-07-21')),
   2, 2, NOW(), NOW());

SET @oid = LAST_INSERT_ID();

INSERT INTO stock_detail
  (stock_order_id, item_code, qty, remain_qty, unit_price, lot_number, expiry_date,
   ref, data_json, created_by, updated_by, created_at, updated_at)
VALUES
  (@oid, '19-00069', 1.00, 0.00, 700.000000, 'RECON-M19-20260608', NULL,
   'V1',
   JSON_OBJECT('recon', JSON_OBJECT('reason', 'missing June issue face shield x1 @700', 'source_order', 'IN-691811')),
   2, 2, NOW(), NOW());

SELECT @oid AS new_order_id, LAST_INSERT_ID() AS new_detail_id;
