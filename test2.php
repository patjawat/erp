WITH x AS (
                        SELECT 
                            a.code,
                            a.title,
                            a.category_id,
                            t.title AS asset_type_name
                        FROM categorise a
                        LEFT JOIN categorise t 
                            ON t.code = a.category_id 
                            AND t.name = 'asset_type'
                        WHERE a.group_id = 4 
                        AND a.name = 'asset_item'
                    ),
                    summary AS (
                        SELECT 
                            i.asset_item,

                            -- ✅ ยอดยกมา (ก่อน '2025-09_01')
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'IN'  
                                        AND i.order_status = 'success'  
                                        AND e.movement_date < '2025-09_01' 
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty 
                                    ELSE 0 
                                END
                            ) AS begin_qty_in,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'IN'  
                                        AND i.order_status = 'success'  
                                        AND e.movement_date < '2025-09_01' 
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty * i.unit_price 
                                    ELSE 0 
                                END
                            ) AS begin_price_in,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success'  
                                        AND e.movement_date < '2025-09_01' 
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty 
                                    ELSE 0 
                                END
                            ) AS begin_qty_out,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success'  
                                        AND e.movement_date < '2025-09_01' 
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty * i.unit_price 
                                    ELSE 0 
                                END
                            ) AS begin_price_out,

                            -- ✅ ยอดเดือนนั้น ('2025-09_01' ถึง '2025-09_30')
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'IN'  
                                        AND i.order_status = 'success' 
                                        AND e.movement_date BETWEEN '2025-09_01' AND '2025-09_30' 
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty 
                                    ELSE 0 
                                END
                            ) AS month_qty_in,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'IN'  
                                        AND i.order_status = 'success' 
                                        AND e.movement_date BETWEEN '2025-09_01' AND '2025-09_30'  
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty * i.unit_price 
                                    ELSE 0 
                                END
                            ) AS month_price_in,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success' 
                                        AND e.movement_date BETWEEN '2025-09_01' AND '2025-09_30'  
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty 
                                    ELSE 0 
                                END
                            ) AS month_qty_out,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success' 
                                        AND e.movement_date BETWEEN '2025-09_01' AND '2025-09_30'  
                                        AND wi.warehouse_type = 'MAIN' 
                                    THEN i.qty * i.unit_price 
                                    ELSE 0 
                                END
                            ) AS month_price_out,

                            -- 🔹 ยอดจ่ายแยกตามประเภทคลังต้นทาง
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success' 
                                        AND wo.warehouse_type = 'SUB' 
                                    THEN i.qty 
                                    ELSE 0 
                                END
                            ) AS qty_out_main,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success' 
                                        AND wo.warehouse_type = 'SUB' 
                                    THEN i.qty * i.unit_price 
                                    ELSE 0 
                                END
                            ) AS price_out_main,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success' 
                                        AND wo.warehouse_type = 'BRANCH' 
                                    THEN i.qty 
                                    ELSE 0 
                                END
                            ) AS qty_out_branch,
                            SUM(
                                CASE 
                                    WHEN i.transaction_type = 'OUT' 
                                        AND i.order_status = 'success' 
                                        AND wo.warehouse_type = 'BRANCH' 
                                    THEN i.qty * i.unit_price 
                                    ELSE 0 
                                END
                            ) AS price_out_branch

                        FROM stock_events i
                        LEFT JOIN stock_events e 
                            ON e.id = i.category_id
                        LEFT JOIN warehouses wo 
                            ON wo.id = e.from_warehouse_id
                        LEFT JOIN warehouses wi 
                            ON wi.id = e.warehouse_id
                        GROUP BY i.asset_item
                    )

                    SELECT 
                        x.code,
                        x.title,
                        x.asset_type_name,

                        -- 🔹 ยอดยกมา
                        COALESCE(s.begin_qty_in - s.begin_qty_out, 0) AS begin_qty,
                        COALESCE(s.begin_price_in - s.begin_price_out, 0) AS begin_price,

                        -- 🔹 ยอดเดือนนั้น
                        COALESCE(s.month_qty_in, 0) AS qty_in,
                        COALESCE(s.month_price_in, 0) AS price_in,
                        COALESCE(s.month_qty_out, 0) AS qty_out,
                        COALESCE(s.month_price_out, 0) AS price_out,
                        
                        -- 🔹 ยอดจ่ายตามประเภทคลังต้นทาง
                        COALESCE(s.qty_out_main, 0) AS qty_out_main,
                        COALESCE(s.price_out_main, 0) AS price_out_main,
                        COALESCE(s.qty_out_branch, 0) AS qty_out_branch,
                        COALESCE(s.price_out_branch, 0) AS price_out_branch,
                        
                        -- 🔹 คงเหลือ
                        (COALESCE(s.begin_qty_in, 0) - COALESCE(s.begin_qty_out, 0)) +
                        (COALESCE(s.month_qty_in, 0) - COALESCE(s.month_qty_out, 0)) AS balance_qty,
                        
                        (COALESCE(s.begin_price_in, 0) - COALESCE(s.begin_price_out, 0)) +
                        (COALESCE(s.month_price_in, 0) - COALESCE(s.month_price_out, 0)) AS balance_price

                    FROM x
                    LEFT JOIN summary s 
                        ON s.asset_item = x.code
                    ORDER BY x.code