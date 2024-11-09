<?php
$sql ="SELECT *,
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND warehouse_id = 7 AND stock_month < 11 AND thai_year = (2568 -1)) 
                            THEN (qty) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND warehouse_id = 7 AND stock_month < 11 AND thai_year = 2568) 
                            THEN (qty) 
                            ELSE 0 
                        END)  AS qty_last,

                         (
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND warehouse_id = 7 AND stock_month < 11 AND thai_year = (2568 -1)) 
                            THEN (unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND warehouse_id = 7 AND stock_month < 11 AND thai_year = 2568) 
                            THEN (unit_price) 
                            ELSE 0 
                        END)
                ) AS unit_price_last,

                         (
                    SUM(CASE 
                            WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND warehouse_id = 7 AND stock_month < 11 AND thai_year = (2568 -1)) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END) 
                    - SUM(CASE 
                            WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND warehouse_id = 7 AND stock_month < 11 AND thai_year = 2568) 
                            THEN (qty * unit_price) 
                            ELSE 0 
                        END)
                ) AS sum_last,

                    SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND warehouse_id = 7 AND stock_month = 11 AND thai_year = 2568) 
                        THEN (qty) 
                        ELSE 0 
                    END
                ) AS qty_po,
                          SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND warehouse_id = 7 AND stock_month = 11 AND thai_year = 2568) 
                        THEN (unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_po,
                          SUM(
                    CASE 
                        WHEN (po_number IS NOT NULL AND warehouse_id = 7 AND stock_month = 11 AND thai_year = 2568) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_po,
                
                -- Calculate total for 'IN' transactions in branch warehouse
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'BRANCH' AND order_status = 'success'  AND warehouse_id = 7 AND MONTH(created_at) = 11 AND thai_year = 2568) 
                        THEN (qty) 
                        ELSE 0 
                    END
                ) AS qty_branch,
                 SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'BRANCH' AND order_status = 'success'  AND warehouse_id = 7 AND MONTH(created_at) = 11 AND thai_year = 2568) 
                        THEN (unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_branch,
                 SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'BRANCH' AND order_status = 'success'  AND warehouse_id = 7 AND MONTH(created_at) = 11 AND thai_year = 2568) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_branch,
                
                SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success' AND warehouse_id = 7 AND MONTH(created_at) = 11 AND thai_year = 2568) 
                        THEN (qty) 
                        ELSE 0 
                    END
                ) AS qty_sub,
                          SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success' AND warehouse_id = 7 AND MONTH(created_at) = 11 AND thai_year = 2568) 
                        THEN (unit_price) 
                        ELSE 0 
                    END
                ) AS unit_price_sub,
                          SUM(
                    CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success' AND warehouse_id = 7 AND MONTH(created_at) = 11 AND thai_year = 2568) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END
                ) AS sum_sub

            FROM view_stock_transaction GROUP BY asset_item";
?>