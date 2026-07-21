INSERT INTO stock_order
 (order_no, order_type, source_type, order_date, main_warehouse_id, sub_warehouse_id,
  contact_id, status, ref, data_json, created_by, updated_by, created_at, updated_at)
VALUES
 ('ADJ-RECON-20260531-07-00412','ADJUST','RECON','2026-05-31 23:00:00',1,NULL,
  NULL,'CONFIRMED','RECON',
  JSON_OBJECT('recon',JSON_OBJECT(
    'reason','recount fix opening: 07-00412 HIV booked -1 at May-end (issue 27พค before receipt 9มิย). accounting physical=0. add +1 to zero the negative opening',
    'target','M7 June opening = accounting; do not touch IN column',
    'created','2026-07-22')),
  2,2,NOW(),NOW());
SET @oid=LAST_INSERT_ID();
INSERT INTO stock_detail
 (stock_order_id, item_code, qty, remain_qty, unit_price, lot_number, expiry_date,
  ref, data_json, created_by, updated_by, created_at, updated_at)
VALUES
 (@oid,'07-00412',1.00,1.00,8500.000000,'RECON-20260531-07-00412',NULL,
  'RECON',JSON_OBJECT('recon',JSON_OBJECT('reason','recount +1 to clear negative opening')),
  2,2,NOW(),NOW());
SELECT @oid AS new_order_id, LAST_INSERT_ID() AS new_detail_id;
