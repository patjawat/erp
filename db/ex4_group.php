WITH stock_data AS (
    SELECT 
        x.category_id, 
        x.asset_type,
        x.warehouse_id,
        
        -- คำนวณ stock_in ใน MAIN warehouse ก่อนเดือนนี้
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type = 'MAIN' 
                 AND x.receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS last_stock_in,

        -- คำนวณ stock_out ใน SUB และ BRANCH warehouse ก่อนเดือนนี้
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type IN ('SUB', 'BRANCH') 
                 AND x.receive_date <= LAST_DAY(DATE_SUB('2024-11-01', INTERVAL 1 MONTH)) 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS last_stock_out,

        -- คำนวณ stock_in ใน MAIN warehouse สำหรับเดือนนี้
        SUM(CASE 
            WHEN x.transaction_type = 'IN' 
                 AND x.warehouse_type = 'MAIN' 
                 AND x.receive_date BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS sum_month,

        -- คำนวณ stock_out ใน BRANCH warehouse สำหรับเดือนนี้
        SUM(CASE 
            WHEN x.transaction_type = 'OUT' 
                 AND x.warehouse_type = 'BRANCH' 
                 AND DATE_FORMAT(x.created_at, '%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS sum_branch,

        -- คำนวณ stock_out ใน SUB warehouse สำหรับเดือนนี้
        SUM(CASE 
            WHEN x.transaction_type = 'OUT' 
                 AND x.warehouse_type = 'SUB' 
                 AND DATE_FORMAT(x.created_at, '%Y-%m-%d') BETWEEN '2024-11-01' AND '2024-11-30' 
            THEN x.unit_price * x.qty 
            ELSE 0 
        END) AS sum_sub
    FROM view_stock_transaction x
    WHERE x.order_status = 'success'
    GROUP BY x.category_id, x.asset_type, x.warehouse_id
)
SELECT *,
    ((last_stock_in - last_stock_out) + sum_month - (sum_branch + sum_sub)) AS total
FROM stock_data;
