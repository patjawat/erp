-- clear คลัง ปรับคลั้งให้เริ่มต้นใหม่
-- ลบรายการฝั่งรับเข้าคลังย่อย
delete s FROM `stock_events` s LEFT JOIN warehouses w ON w.id = s.warehouse_id WHERE w.warehouse_type = 'SUB' AND s.transaction_type = 'IN';
-- update สถานะเป็น pending
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

delete se FROM `stock_events` `se` LEFT JOIN `warehouses` `w` ON `se`.`warehouse_id` = `w`.`id` WHERE ((`se`.`thai_year`=2568) AND (`se`.`transaction_type`='OUT') AND (`order_status`='success')) AND (`w`.`warehouse_type`='SUB');
