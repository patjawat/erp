-- M22-2: recount qty -1 @380 -> 20 units / 12,130 = accounting
INSERT INTO stock_order (order_no,order_type,source_type,order_date,main_warehouse_id,sub_warehouse_id,contact_id,status,ref,data_json,created_by,updated_by,created_at,updated_at)
VALUES ('RECON-M22-20260630-M22-2','ADJUST','RECON','2026-06-30 23:30:00',7,NULL,NULL,'CONFIRMED','RECON',
  JSON_OBJECT('recon',JSON_OBJECT('reason','clean recount M22-2: 21->20 units, value->12130 (=accounting). replaces tangled July adjusts 3446/3447/3448','value_delta',-380)),2,2,NOW(),NOW());
SET @o1=LAST_INSERT_ID();
INSERT INTO stock_detail (stock_order_id,item_code,qty,remain_qty,unit_price,lot_number,expiry_date,ref,data_json,created_by,updated_by,created_at,updated_at)
VALUES (@o1,'M22-2',-1.00,0.00,380.000000,'RECON-M22-20260630-M22-2',NULL,'RECON',JSON_OBJECT('recount',1,'note','remove 1 unit @380 to match accounting 20u/12130'),2,2,NOW(),NOW());
-- 22-00234: value-only -225 -> 7,500 = accounting
INSERT INTO stock_order (order_no,order_type,source_type,order_date,main_warehouse_id,sub_warehouse_id,contact_id,status,ref,data_json,created_by,updated_by,created_at,updated_at)
VALUES ('RECON-M22-20260630-22-00234','ADJUST','RECON','2026-06-30 23:30:00',7,NULL,NULL,'CONFIRMED','RECON',
  JSON_OBJECT('recon',JSON_OBJECT('reason','value-only -225 for 22-00234 -> 7500 (=accounting). replaces dropped double-encoded July adjust','value_delta',-225)),2,2,NOW(),NOW());
SET @o2=LAST_INSERT_ID();
INSERT INTO stock_detail (stock_order_id,item_code,qty,remain_qty,unit_price,lot_number,expiry_date,ref,data_json,created_by,updated_by,created_at,updated_at)
VALUES (@o2,'22-00234',0.00,0.00,0.000000,'RECON-M22-20260630-22-00234',NULL,'RECON',JSON_OBJECT('adjust_value_only',1,'value_delta',-225),2,2,NOW(),NOW());
SELECT @o1 AS m222_order, @o2 AS m00234_order;
