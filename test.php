WITH t AS (SELECT 
                v.code as vendor_id,
                v.title as vendor_name,
                wi.warehouse_name,
                 wi.warehouse_type AS main_warehouse_type,
                i.asset_item,
                a.title as asset_name,
                t.code as asset_type_code,
                t.title  as asset_type_name,
                e.id AS e_id,
                e.order_status AS e_status,
                i.order_status AS i_status,
                i.id AS i_id,
                a.data_json->>'$.unit' AS unit,
                i.qty AS item_qty,
                i.unit_price,
                e.thai_year,
                e.transaction_type,
                e.movement_date,
                COALESCE(wo.warehouse_type, wi.warehouse_type) AS warehouse_type,
                

                -- 🔹 ยอดยกมา (ก่อนเริ่มงวด)
                SUM(
                    CASE 
                        WHEN e.movement_date < '2025-12-01' 
                            AND i.transaction_type = 'IN' 
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                        THEN i.qty
                        WHEN e.movement_date < '2025-12-01' 
                            AND i.transaction_type = 'OUT' 
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH')
                        THEN -i.qty
                        ELSE 0 
                    END
                ) AS begin_qty,

                -- 🔹 ยอดยกมาราคา (แก้ floating-point ด้วย DECIMAL)
                SUM(
                    CAST(
                        CASE
                            WHEN e.movement_date < '2025-12-01'
                                AND i.transaction_type = 'IN'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                            THEN (i.qty * i.unit_price)
                            WHEN e.movement_date < '2025-12-01'
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH')
                            THEN - (i.qty * i.unit_price)
                            ELSE 0
                        END AS DECIMAL(18,5)
                    )
                ) AS begin_price,

                -- 🔹 ยอดรับเข้า
                SUM(
                    CASE 
                        WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                            AND i.transaction_type = 'IN' 
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                        THEN i.qty 
                        ELSE 0 
                    END
                ) AS qty_in,

                SUM(
                    CAST(
                        CASE 
                            WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                                AND i.transaction_type = 'IN' 
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                            THEN i.qty * i.unit_price 
                            ELSE 0 
                        END AS DECIMAL(18,5)
                    )
                ) AS price_in,

                -- 🔹 ยอดเบิกออก รพ.
                SUM(
                    CASE 
                        WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                            AND i.transaction_type = 'OUT'
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB')
                        THEN i.qty 
                        ELSE 0 
                    END
                ) AS qty_out,

                SUM(
                    CAST(
                        CASE 
                            WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB')
                            THEN i.qty * i.unit_price 
                            ELSE 0 
                        END AS DECIMAL(18,5)
                    )
                ) AS price_out,

                -- 🔹 ยอดเบิกออก รพ.สต.
                SUM(
                    CASE 
                        WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                            AND i.transaction_type = 'OUT'
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('BRANCH')
                        THEN i.qty 
                        ELSE 0 
                    END
                ) AS branch_qty_out,

                SUM(
                    CAST(
                        CASE 
                            WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('BRANCH')
                            THEN i.qty * i.unit_price 
                            ELSE 0 
                        END AS DECIMAL(18,5)
                    )
                ) AS branch_price_out,

                -- 🔹 ยอดเบิกออก รวม
                SUM(
                    CASE 
                        WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                            AND i.transaction_type = 'OUT'
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH')
                        THEN i.qty 
                        ELSE 0 
                    END
                ) AS total_qty_out,

                SUM(
                    CAST(
                        CASE 
                            WHEN e.movement_date BETWEEN '2025-12-01' AND '2025-12-31'
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH')
                            THEN i.qty * i.unit_price 
                            ELSE 0 
                        END AS DECIMAL(18,5)
                    )
                ) AS total_price_out,

                -- 🔹 ยอดคงเหลือสิ้นงวด
                SUM(
                    CASE 
                        WHEN e.movement_date <= '2025-12-31' 
                            AND i.transaction_type = 'IN'
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                        THEN CAST(i.qty AS DECIMAL(18,5))
                        WHEN e.movement_date <= '2025-12-31' 
                            AND i.transaction_type = 'OUT'
                            AND i.order_status = 'success'
                            AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                        THEN -CAST(i.qty AS DECIMAL(18,5))
                        ELSE 0 
                    END
                ) AS end_qty,

                SUM(
                    CAST(
                        CASE 
                            WHEN e.movement_date <= '2025-12-31' 
                                AND i.transaction_type = 'IN'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                            THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                            WHEN e.movement_date <= '2025-12-31' 
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                            THEN - CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                            ELSE 0
                        END AS DECIMAL(18,5)
                    )
                ) AS end_price

            FROM stock_events i
            LEFT JOIN stock_events e ON e.id = i.category_id
            LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
            LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
            LEFT JOIN categorise a ON a.code = i.asset_item AND a.name = 'asset_item'
            LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
            LEFT JOIN (
                SELECT code, title
                FROM (
                    SELECT *,
                        ROW_NUMBER() OVER(PARTITION BY code ORDER BY code) AS rn
                    FROM categorise
                    WHERE name = 'vendor'
                ) t
                WHERE rn = 1
            ) v ON v.code = e.vendor_id
            WHERE e.name = 'order' AND i.asset_item IS NOT NULL
                    GROUP BY t.code
                    ORDER BY CAST(SUBSTRING_INDEX(a.code, '-', 1) AS UNSIGNED), CAST(SUBSTRING_INDEX(a.code, '-', -1) AS UNSIGNED), CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED)
            )
            SELECT 
                *,
                -- 🔹 เพิ่มผลรวมแบบไม่ปัดเศษ
               (begin_price+price_in) as total_price_begin,
               (price_out+branch_price_out) as total_price_out
            FROM t;







                        SUM(
                    CAST(
                        CASE
                            WHEN e.movement_date < '2025-12-01'
                                AND i.transaction_type = 'IN'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                            THEN (i.qty * i.unit_price)
                            WHEN e.movement_date < '2025-12-01'
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH')
                            THEN - (i.qty * i.unit_price)
                            ELSE 0
                        END AS DECIMAL(18,5)
                    )
                ) AS begin_price,

                 SUM(
                    CAST(
                        CASE 
                            WHEN e.movement_date <= '2025-12-31' 
                                AND i.transaction_type = 'IN'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN'
                            THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                            WHEN e.movement_date <= '2025-12-31' 
                                AND i.transaction_type = 'OUT'
                                AND i.order_status = 'success'
                                AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH')
                            THEN - CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                            ELSE 0
                        END AS DECIMAL(18,5)
                    )
                ) AS end_price