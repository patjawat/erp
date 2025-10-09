WITH stock_summary AS (
                            SELECT 
                                
                                asset_type.code AS asset_type_code,
                                asset_type.title AS asset_type_name,
                                p.title as asset_name,
                                p.code as asset_item,
                                p.data_json->>'$.unit' AS unit,
                                w.warehouse_name as warehouse_name,
	-- จำนวนคงเหลือ (ก่อนเดือน)
            SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'IN'  
                 THEN i.qty ELSE 0 END) 
        -
        SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'OUT' 
                 THEN i.qty  ELSE 0 END) AS balance_before_qty,
                                -- ยอดยกมา (ก่อนเดือน)
                                SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'IN'  
                                        THEN i.qty * i.unit_price ELSE 0 END) 
                                -
                                SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS balance_before,

                               -- จำนวนรับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty ELSE 0 END) AS total_in_month_qty,

                                -- รับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_in_month,
 -- จำนวนจ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty  ELSE 0 END) AS total_out_month_qty,

                                -- จ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_out_month

                            FROM stock_events o
                            LEFT JOIN stock_events i ON i.category_id = o.id
                            LEFT JOIN categorise asset_type ON asset_type.code = o.asset_type_id
                            LEFT JOIN warehouses w ON w.id = o.warehouse_id
                            LEFT JOIN categorise p ON p.code = i.asset_item
                            WHERE o.name = 'order'
                            AND o.order_status = 'success'
                            AND i.name = 'order_item'
                            AND w.warehouse_type = 'MAIN'
                            AND asset_type.category_id = 4
                            AND asset_type.name = 'asset_type'
                            AND p.name = 'asset_item'
                            GROUP BY p.code
                            ORDER BY p.code
                        )

                        SELECT 
                            warehouse_name,
                            asset_item,
                            asset_name,
                            unit,
                            asset_type_code,
                            asset_type_name,
                            balance_before_qty,
                            balance_before,                     -- 1. ยอดยกมา
                            total_in_month,                     -- 2. รับเข้าระหว่างเดือน
                            total_in_month_qty,                     -- 2. รับเข้าระหว่างเดือน
                            (balance_before + total_in_month) AS total_before_out,   -- 3. รวม
                            total_out_month,                    -- 4. จ่ายไประหว่างเดือน
                            total_out_month_qty,                    -- 4. จ่ายไประหว่างเดือน
                            (balance_before + total_in_month - total_out_month) AS balance_after  -- 5. ยอดยกไป
                        FROM stock_summary
                        ORDER BY CAST(SUBSTRING(asset_type_code, 2) AS UNSIGNED);