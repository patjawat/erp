WITH stock_summary AS (
                            SELECT 
                                
                                asset_type.code AS asset_type_code,
                                asset_type.title AS asset_type_name,

                                -- ยอดยกมา (ก่อนเดือน)
                                SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'IN'  
                                        THEN i.qty * i.unit_price ELSE 0 END) 
                                -
                                SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS balance_before,

                                -- รับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_in_month,

                                -- จ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_out_month

                            FROM stock_events o
                            LEFT JOIN stock_events i ON i.category_id = o.id
                            LEFT JOIN categorise asset_type ON asset_type.code = o.asset_type_id
                            LEFT JOIN warehouses w ON w.id = o.warehouse_id
                            WHERE o.name = 'order'
                             AND o.order_status = 'success'
                            AND i.name = 'order_item'
                            AND w.warehouse_type = 'MAIN'
                            AND o.warehouse_id = 7
                            AND asset_type.category_id = 4
                            AND asset_type.name = 'asset_type'
                            GROUP BY asset_type.code, asset_type.title
                        )

                        SELECT 
                            asset_type_code,
                            asset_type_name,
                            balance_before,                     -- 1. ยอดยกมา
                            total_in_month,                     -- 2. รับเข้าระหว่างเดือน
                            (balance_before + total_in_month) AS total_before_out,   -- 3. รวม
                            total_out_month,                    -- 4. จ่ายไประหว่างเดือน
                            (balance_before + total_in_month - total_out_month) AS balance_after  -- 5. ยอดยกไป
                        FROM stock_summary
                        ORDER BY CAST(SUBSTRING(asset_type_code, 2) AS UNSIGNED)







                        WITH stock_summary AS (
                            SELECT 
                                a.title  as asset_name,
							    a.code AS asset_code,
                                asset_type.code AS asset_type_code,
                                asset_type.title AS asset_type_name,

                                -- ยอดยกมา (ก่อนเดือน)
                                SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'IN'  
                                        THEN i.qty * i.unit_price ELSE 0 END) 
                                -
                                SUM(CASE WHEN o.movement_date < '2025-08-01' AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS balance_before,

                                -- รับเข้าระหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'IN' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_in_month,

                                -- จ่ายไประหว่างเดือน
                                SUM(CASE WHEN o.movement_date BETWEEN '2025-08-01' AND '2025-08-31' 
                                        AND o.transaction_type = 'OUT' 
                                        THEN i.qty * i.unit_price ELSE 0 END) AS total_out_month

                            FROM stock_events o
                            LEFT JOIN stock_events i ON i.category_id = o.id
    						LEFT JOIN categorise a ON a.code = i.asset_item
                            LEFT JOIN categorise asset_type ON asset_type.code = o.asset_type_id
                            LEFT JOIN warehouses w ON w.id = o.warehouse_id
                            WHERE o.name = 'order'
                             AND o.order_status = 'success'
                            AND i.name = 'order_item'
                            AND w.warehouse_type = 'MAIN'
                            AND o.warehouse_id = 7
                            AND asset_type.category_id = 4
                            AND asset_type.name = 'asset_type'
                            GROUP BY a.code
                        )

                        SELECT 
                        asset_code,
                        asset_name,
                            asset_type_code,
                            asset_type_name,
                            balance_before,                     -- 1. ยอดยกมา
                            total_in_month,                     -- 2. รับเข้าระหว่างเดือน
                            (balance_before + total_in_month) AS total_before_out,   -- 3. รวม
                            total_out_month,                    -- 4. จ่ายไประหว่างเดือน
                            (balance_before + total_in_month - total_out_month) AS balance_after  -- 5. ยอดยกไป
                        FROM stock_summary
                        GROUP BY asset_code
                        ORDER BY asset_code;