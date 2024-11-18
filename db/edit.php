SELECT x.*,
    ((x.sum_last + x.sum_po) - (x.sum_branch + x.sum_sub)) as total FROM(SELECT *,
                SUM(CASE 
                        WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND  warehouse_id = 7 AND order_month < 10 AND thai_year = (:thai_year -1)) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END) 
                - SUM(CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND  warehouse_id = 7  AND order_month < 10 AND thai_year = :thai_year) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END)  AS sum_last,

                SUM(
                CASE 
                    WHEN (po_number IS NOT NULL AND  warehouse_id = 7 AND order_month = 10 AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_po,
            
            SUM(
                CASE 
                    WHEN (transaction_type = 'OUT' AND from_warehouse_type = 'BRANCH' AND order_status = 'success' AND  warehouse_id = 7 AND MONTH(created_at) = 10 AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_branch,
            
            SUM(
                CASE 
                    WHEN (transaction_type = 'OUT' AND from_warehouse_type = 'SUB' AND order_status = 'success' AND  warehouse_id = 7 AND MONTH(created_at) = 10 AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_sub

        FROM view_stock_transaction GROUP BY category_id) as x