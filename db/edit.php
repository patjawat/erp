WITH x2 AS (
SELECT x.category_id,x.warehouse_id,x.asset_type,x.asset_item,x.asset_name,
                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND x1.order_status = 'success'
                AND transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
                )  as last_stock_in,
                (select IFNULL(sum(x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND x1.order_status = 'success'
                AND transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND receive_date <= 								LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
                )  as last_stock_in_qty,

                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND x1.order_status = 'success'
                AND transaction_type = 'IN' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
                )  as last_stock_out,
                    (select IFNULL(sum(x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND x1.order_status = 'success'
                AND transaction_type = 'IN' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
                )  as last_stock_out_qty,


                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND x1.order_status = 'success'
                AND x1.transaction_type = 'IN' AND warehouse_type = 'MAIN' AND receive_date BETWEEN :date_start AND :date_end
                )  as sum_month,
     (select IFNULL(sum(x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND x1.order_status = 'success'
                AND x1.transaction_type = 'IN' AND warehouse_type = 'MAIN' AND receive_date BETWEEN :date_start AND :date_end
                )  as sum_month_qty,


                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND order_status = 'success'
                AND warehouse_type = 'BRANCH' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
                )  as sum_branch,

                    (select IFNULL(sum(x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND order_status = 'success'
                AND warehouse_type = 'BRANCH' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
                )  as sum_branch_qty,
    
                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND order_status = 'success'
                AND warehouse_type = 'SUB' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
                )  as sum_sub,
                    (select IFNULL(sum(x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.asset_item = x.asset_item AND order_status = 'success'
                AND warehouse_type = 'SUB' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
                )  as sum_sub_qty
    
                FROM `view_stock_transaction` x
                GROUP BY x.asset_item) 
                
                SELECT *,((last_stock_in - last_stock_out) + sum_month -(sum_branch + sum_sub) ) as total FROM x2