SELECT
                -- 🔹 ส่วนการจัดกลุ่ม (Item & Type Info)
                t.code AS asset_type_code,
                t.title AS asset_type_name,
                a.code AS asset_item,
                a.title AS asset_name,
                a.data_json->>'$.unit' AS unit,

                -- 🔹 ยอดยกมา (ก่อน '2026-01-01')
                SUM(CASE
                    WHEN e.movement_date < '2026-01-01' AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN CAST(i.qty AS DECIMAL(18,5))
                    WHEN e.movement_date < '2026-01-01' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN -CAST(i.qty AS DECIMAL(18,5))
                    ELSE 0
                END) AS begin_qty,

                CAST(SUM(CASE
                    WHEN e.movement_date < '2026-01-01' AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    WHEN e.movement_date < '2026-01-01' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN -CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    ELSE 0
                END) AS DECIMAL(18,5)) AS begin_price,

                -- 🔹 ยอดรับเข้า (In Period)
                SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN CAST(i.qty AS DECIMAL(18,5))
                    ELSE 0 
                END) AS qty_in,

                CAST(SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    ELSE 0 
                END) AS DECIMAL(18,5)) AS price_in,

                -- 🔹 ยอดเบิกออก รพ. (SUB)
                SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'SUB' THEN CAST(i.qty AS DECIMAL(18,5))
                    ELSE 0 
                END) AS qty_out,

                CAST(SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'SUB' THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    ELSE 0 
                END) AS DECIMAL(18,5)) AS price_out,

                -- 🔹 ยอดเบิกออก รพ.สต. (BRANCH)
                SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'BRANCH' THEN CAST(i.qty AS DECIMAL(18,5))
                    ELSE 0 
                END) AS branch_qty_out,

                CAST(SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'BRANCH' THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    ELSE 0 
                END) AS DECIMAL(18,5)) AS branch_price_out,

                -- 🔹 ยอดเบิกออก รวม (SUB + BRANCH)
                SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN CAST(i.qty AS DECIMAL(18,5))
                    ELSE 0 
                END) AS total_qty_out,

                CAST(SUM(CASE 
                    WHEN e.movement_date BETWEEN '2026-01-01' AND '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB', 'BRANCH') THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    ELSE 0 
                END) AS DECIMAL(18,5)) AS total_price_out,

                -- 🔹 ยอดคงเหลือสิ้นงวด (End Balance)
                SUM(CASE 
                    WHEN e.movement_date <= '2026-01-31' AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN CAST(i.qty AS DECIMAL(18,5))
                    WHEN e.movement_date <= '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH') THEN -CAST(i.qty AS DECIMAL(18,5))
                    ELSE 0 
                END) AS end_qty,

                CAST(SUM(CASE 
                    WHEN e.movement_date <= '2026-01-31' AND i.transaction_type = 'IN' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) = 'MAIN' THEN CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    WHEN e.movement_date <= '2026-01-31' AND i.transaction_type = 'OUT' AND i.order_status = 'success' AND COALESCE(wo.warehouse_type, wi.warehouse_type) IN ('SUB','BRANCH') THEN -CAST(i.qty AS DECIMAL(18,5)) * CAST(i.unit_price AS DECIMAL(18,5))
                    ELSE 0 
                END) AS DECIMAL(18,5)) AS end_price

            FROM categorise a
            LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
            LEFT JOIN stock_events i ON i.asset_item = a.code AND i.name = 'order_item'
            LEFT JOIN stock_events e ON e.id = i.category_id AND e.name = 'order'
            LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
            LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
            LEFT JOIN (
                SELECT code, title, ROW_NUMBER() OVER(PARTITION BY code ORDER BY code) AS rn
                FROM categorise
                WHERE name = 'vendor'
            ) v ON v.code = e.vendor_id AND v.rn = 1

            WHERE a.name = 'asset_item'
            -- AND a.group_id = 'MATER'

            GROUP BY
                a.code,
                a.title,
                t.code,
                t.title,
                a.data_json->>'$.unit'

            ORDER BY
                CAST(SUBSTRING_INDEX(a.code, '-', 1) AS UNSIGNED),
                CAST(SUBSTRING_INDEX(a.code, '-', -1) AS UNSIGNED),
                CAST(SUBSTRING(a.category_id, 2) AS UNSIGNED);