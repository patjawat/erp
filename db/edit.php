SELECT x.category_id,x.warehouse_id,x.asset_type, 
    SUM(x.qty*x.unit_price) as total,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.asset_item= x.asset_item AND x1.order_status = 'success'
    AND transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
    )  as last_stock_in,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.asset_item= x.asset_item AND x1.order_status = 'success'
    AND transaction_type = 'IN' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
    )  as last_stock_out,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.asset_item= x.asset_item AND x1.order_status = 'success'
    AND x1.transaction_type = 'IN' AND warehouse_type = 'MAIN' AND receive_date BETWEEN '2024-11-01' AND '2024-11-30'
    )  as sum_month,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.asset_item= x.asset_item AND order_status = 'success'
    AND from_warehouse_type = 'BRANCH' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30'
    )  as sum_branch,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.asset_item= x.asset_item AND order_status = 'success'
    AND from_warehouse_type = 'SUB' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30'
    )  as sum_sub

    FROM `view_stock_transaction` x
    GROUP BY x.asset_item