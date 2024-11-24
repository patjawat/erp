WITH x2 AS (
    SELECT 
        x.category_id,
        x.warehouse_id,
        x.asset_type,
        x.asset_item,
        x.asset_name,
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type = 'MAIN' 
                 AND x.order_status = 'success' 
                 AND x.receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS last_stock_in,
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type = 'MAIN' 
                 AND x.order_status = 'success' 
                 AND x.receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
            THEN x.qty 
            ELSE 0 
        END) AS last_stock_in_qty,
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type IN ('SUB', 'BRANCH') 
                 AND x.order_status = 'success' 
                 AND x.receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS last_stock_out,
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type IN ('SUB', 'BRANCH') 
                 AND x.order_status = 'success' 
                 AND x.receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
            THEN x.qty 
            ELSE 0 
        END) AS last_stock_out_qty,
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type = 'MAIN' 
                 AND x.receive_date BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS sum_month,
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type = 'MAIN' 
                 AND x.receive_date BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.qty 
            ELSE 0 
        END) AS sum_month_qty,
        SUM(CASE 
            WHEN x.transaction_type = 'OUT' 
                 AND x.warehouse_type = 'BRANCH' 
                 AND DATE_FORMAT(x.created_at, '%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS sum_branch,
        SUM(CASE 
            WHEN x.transaction_type = 'OUT' 
                 AND x.warehouse_type = 'BRANCH' 
                 AND DATE_FORMAT(x.created_at, '%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.qty 
            ELSE 0 
        END) AS sum_branch_qty,
        SUM(CASE 
            WHEN x.transaction_type = 'OUT' 
                 AND x.warehouse_type = 'SUB' 
                 AND DATE_FORMAT(x.created_at, '%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS sum_sub,
        SUM(CASE 
            WHEN x.transaction_type = 'OUT' 
                 AND x.warehouse_type = 'SUB' 
                 AND DATE_FORMAT(x.created_at, '%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.qty 
            ELSE 0 
        END) AS sum_sub_qty
    FROM view_stock_transaction x
    WHERE x.order_status = 'success'
    GROUP BY x.category_id, x.warehouse_id, x.asset_type, x.asset_item, x.asset_name
)
SELECT 
    *,
    ((last_stock_in - last_stock_out) + sum_month - (sum_branch + sum_sub)) AS total 
FROM x2;