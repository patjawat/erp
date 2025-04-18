INSERT INTO `documents` (`id`, `ref`, `doc_number`, `topic`, `document_group`, `document_type`, `document_org`, `thai_year`, `doc_regis_number`, `doc_speed`, `secret`, `doc_date`, `doc_expire`, `doc_transactions_date`, `req_approve`, `doc_time`, `status`, `data_json`, `tags_department`, `tags_employee`, `view_json`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(49342, 'MJv3BJcit5xgrLTT24p17o', '-', 'แต่งตั้งเจ้้าหน้าที่ปฏิบัติงานนอกเวลาราชการ', 'appointment', 'DT9', '0', '2568', 9, 'ปกติ', 'ปกติ', NULL, NULL, '16/04/2568', 1, '21:39', NULL, '{\"send_line\": \"0\"}', NULL, NULL, NULL, '2025-04-16 21:39:46', NULL, 2, 2, NULL, NULL);


SELECT s.id,s.code,fw.warehouse_name,w.warehouse_name,w.warehouse_type,s.transaction_type FROM `stock_events` s
LEFT JOIN warehouses w ON w.id = s.warehouse_id
LEFT JOIN warehouses fw ON fw.id = s.from_warehouse_id
WHERE s.name = 'order' AND s.transaction_type = 'OUT' AND  w.warehouse_type = 'MAIN';


SELECT s.id,i.category_id,s.code,fw.warehouse_name,w.warehouse_name,w.warehouse_type,s.transaction_type FROM `stock_events` s
LEFT JOIN warehouses w ON w.id = s.warehouse_id
LEFT JOIN warehouses fw ON fw.id = s.from_warehouse_id
LEFT JOIN stock_events i ON i.category_id = s.id
WHERE  s.transaction_type = 'OUT' AND  w.warehouse_type = 'MAIN';


DELETE FROM stock_events 
WHERE id IN (
  SELECT id FROM (
    SELECT s.id 
    FROM stock_events s
    JOIN warehouses w ON w.id = s.warehouse_id
    LEFT JOIN warehouses fw ON fw.id = s.from_warehouse_id
    WHERE s.transaction_type = 'OUT'
      AND w.warehouse_type = 'MAIN'
  ) AS temp_ids
);

เทียบคลังรับเข้ากับ stock
select w.warehouse_type,w.warehouse_name,s.lot_number,s.asset_item,e.qty as erder_qty,e.unit_price as order_unit_price,s.qty,s.unit_price FROM stock s
left JOIN stock_events e ON e.asset_item = s.asset_item AND e.lot_number = s.lot_number
JOIN warehouses w ON w.id = s.warehouse_id AND  w.warehouse_type = 'MAIN';


รับเข้าฝั้งคลังย่อย
SELECT w.warehouse_name,w.warehouse_type,s.transaction_type FROM `stock_events` s
LEFT JOIN warehouses w ON w.id = s.warehouse_id
WHERE w.warehouse_type = 'SUB' AND s.transaction_type = 'IN';


clear คลัง ปรับคลั้งให้เริ่มต้นใหม่
ลบรายการฝั่งรับเข้าคลังย่อย
delete s FROM `stock_events` s LEFT JOIN warehouses w ON w.id = s.warehouse_id WHERE w.warehouse_type = 'SUB' AND s.transaction_type = 'IN';
update สถานะเป็น pending
UPDATE stock_events s
JOIN warehouses w ON w.id = s.warehouse_id
LEFT JOIN warehouses fw ON fw.id = s.from_warehouse_id
SET s.order_status = 'pending'
WHERE s.transaction_type = 'OUT'
  AND w.warehouse_type = 'MAIN';

-- update คลังหลัก
UPDATE stock s
LEFT JOIN stock_events e ON e.asset_item = s.asset_item AND e.lot_number = s.lot_number
JOIN warehouses w ON w.id = s.warehouse_id
SET s.qty = e.qty
WHERE w.warehouse_type = 'MAIN';

DELETE s FROM stock s
LEFT JOIN stock_events e ON e.asset_item = s.asset_item AND e.lot_number = s.lot_number
JOIN warehouses w ON w.id = s.warehouse_id
WHERE w.warehouse_type = 'SUB';

