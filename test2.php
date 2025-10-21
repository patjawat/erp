SELECT i.transaction_type,e.transaction_type,e.movement_date,wo.warehouse_type,wi.warehouse_type,COALESCE(wo.warehouse_type, wi.warehouse_type) as w_type FROM `stock_events` i
LEFT JOIN `stock_events` e ON e.id = i.category_id
LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
WHERE e.movement_date BETWEEN '2025-09-01' AND '2025-09-30';

SELECT 
    t.code,
    t.title,
    e.id AS e_id,
    e.order_status AS e_status,
    i.order_status AS i_status,
    i.id AS i_id,
    e.thai_year,
    e.transaction_type,
    e.movement_date,
    COALESCE(wo.warehouse_type, wi.warehouse_type) AS warehouse_type,
    -- 🔹 ยอดยกมา (ก่อน 1 ก.ย. 2025)
    SUM(
        CASE 
            WHEN e.movement_date < '2025-09-01' 
                 AND i.transaction_type = 'IN' 
                 AND i.order_status = 'success'
                 AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
            THEN i.qty

            WHEN e.movement_date < '2025-09-01' 
                 AND i.transaction_type = 'OUT' 
                 AND i.order_status = 'success'
                 AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH')
            THEN -i.qty

            ELSE 0 
        END
    ) AS begin_qty,
        -- 🔹 ยอดรับเข้า (1–30 ก.ย. 2025)
    SUM(CASE 
        WHEN e.movement_date BETWEEN '2025-09-01' AND '2025-09-30'
             AND i.transaction_type = 'IN' 
             AND i.order_status = 'success'
        	AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
        THEN i.qty ELSE 0 END
    ) AS qty_in,
    ROUND(SUM(CASE 
        WHEN e.movement_date BETWEEN '2025-09-01' AND '2025-09-30'
             AND i.transaction_type = 'IN' 
             AND i.order_status = 'success'
             AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
        THEN i.qty * i.unit_price ELSE 0 END
    ),2) AS price_in,
    -- 🔹 ยอดเบิกออก รพ. (1–30 ก.ย. 2025)
    SUM(CASE 
        WHEN e.movement_date BETWEEN '2025-09-01' AND '2025-09-30'
             AND i.transaction_type = 'OUT'
             AND i.order_status = 'success'
        	AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB')
        THEN i.qty ELSE 0 END
    ) AS qty_out,
    ROUND(SUM(CASE 
        WHEN e.movement_date BETWEEN '2025-09-01' AND '2025-09-30'
             AND i.transaction_type = 'OUT'
             AND i.order_status = 'success'
              AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB')
        THEN i.qty * i.unit_price ELSE 0 END
    ),2) AS price_out,
        -- 🔹 ยอดเบิกออก รพ.สต. (1–30 ก.ย. 2025)
    SUM(CASE 
        WHEN e.movement_date BETWEEN '2025-09-01' AND '2025-09-30'
             AND i.transaction_type = 'OUT'
             AND i.order_status = 'success'
        	AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('BRANCH')
        THEN i.qty ELSE 0 END
    ) AS branch_qty_out,
    ROUND(SUM(CASE 
        WHEN e.movement_date BETWEEN '2025-09-01' AND '2025-09-30'
             AND i.transaction_type = 'OUT'
             AND i.order_status = 'success'
              AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('ฺBRANCH')
        THEN i.qty * i.unit_price ELSE 0 END
    ),2) AS branch_price_out,
        -- 🔹 ยอดคงเหลือสิ้นงวด (ถึง 30 ก.ย. 2025)
    SUM(CASE 
        WHEN e.movement_date <= '2025-09-30' 
             AND i.transaction_type = 'IN'
             AND i.order_status = 'success'
        	AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
        THEN i.qty
        WHEN e.movement_date <= '2025-09-30' 
             AND i.transaction_type = 'OUT'
             AND i.order_status = 'success'
        	AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','ฺBRANCH')
        THEN -i.qty
        ELSE 0 END
    ) AS end_qty,
    
    ROUND(SUM(CASE 
        WHEN e.movement_date <= '2025-09-30' 
             AND i.transaction_type = 'IN'
             AND i.order_status = 'success'
             AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
        THEN i.qty * i.unit_price
        WHEN e.movement_date <= '2025-09-30' 
             AND i.transaction_type = 'OUT'
             AND i.order_status = 'success'
             AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','ฺBRANCH')
        THEN -i.qty * i.unit_price
        ELSE 0 END
    ),2) AS end_price
    
FROM `stock_events` i
-- เชื่อมโยงรายการหลัก
LEFT JOIN `stock_events` e ON e.id = i.category_id
-- เชื่อมกับคลังต้นทาง
LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
-- เชื่อมกับคลังปลายทาง
LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
-- เชื่อมกับหมวดหมู่วัสดุ
LEFT JOIN categorise a ON a.code = i.asset_item AND a.name = 'asset_item'
-- เชื่อมกับหมวดหมู่ประเภทสินทรัพย์
LEFT JOIN categorise t  ON t.code = a.category_id AND t.name = 'asset_type'
WHERE e.thai_year = 2568
GROUP BY a.code
ORDER BY 
CAST(SUBSTRING_INDEX(a.code, '-', 1) AS UNSIGNED),
CAST(SUBSTRING_INDEX(a.code, '-', -1) AS UNSIGNED),
CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED)