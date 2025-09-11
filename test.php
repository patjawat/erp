WITH item_movements AS (
    SELECT 
        i.id AS item_id,
        i.asset_item,
        i.qty,
        i.unit_price,
        (i.qty * i.unit_price) AS amount,
        o.asset_type_id,
        asset_type.code AS asset_type_code,
        asset_type.title AS asset_type_name,
        o.transaction_type,
        o.movement_date
    FROM stock_events o
    LEFT JOIN stock_events i 
           ON i.category_id = o.id
    LEFT JOIN categorise asset_type 
           ON asset_type.code = o.asset_type_id
    LEFT JOIN warehouses w 
           ON w.id = o.warehouse_id
    WHERE o.name = 'order'
      AND i.name = 'order_item'
      AND w.warehouse_type = 'MAIN'
      AND o.warehouse_id = 7
      AND asset_type.category_id = 4
      AND asset_type.name = 'asset_type'
)

SELECT 
    asset_type_code,
    asset_type_name,
    asset_item,

    -- qty รวมก่อนเดือน
    SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'IN'  
             THEN qty ELSE 0 END)
    -
    SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'OUT' 
             THEN qty ELSE 0 END) AS qty_balance_before,

    -- qty รับเข้าระหว่างเดือน
    SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
              AND transaction_type = 'IN' 
             THEN qty ELSE 0 END) AS qty_in_month,

    -- qty จ่ายออกระหว่างเดือน
    SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
              AND transaction_type = 'OUT' 
             THEN qty ELSE 0 END) AS qty_out_month,

    -- qty ยกไป
    (
        SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'IN'  
                 THEN qty ELSE 0 END)
        -
        SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'OUT' 
                 THEN qty ELSE 0 END)
        +
        SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
                  AND transaction_type = 'IN' 
                 THEN qty ELSE 0 END)
        -
        SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
                  AND transaction_type = 'OUT' 
                 THEN qty ELSE 0 END)
    ) AS qty_balance_after,

    -- มูลค่า (amount)
    SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'IN'  
             THEN amount ELSE 0 END)
    -
    SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'OUT' 
             THEN amount ELSE 0 END) AS balance_before,

    SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
              AND transaction_type = 'IN' 
             THEN amount ELSE 0 END) AS total_in_month,

    SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
              AND transaction_type = 'OUT' 
             THEN amount ELSE 0 END) AS total_out_month,

    (
        SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'IN'  
                 THEN amount ELSE 0 END)
        -
        SUM(CASE WHEN movement_date < '2025-09-01' AND transaction_type = 'OUT' 
                 THEN amount ELSE 0 END)
        +
        SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
                  AND transaction_type = 'IN' 
                 THEN amount ELSE 0 END)
        -
        SUM(CASE WHEN movement_date BETWEEN '2025-09-01' AND '2025-09-30' 
                  AND transaction_type = 'OUT' 
                 THEN amount ELSE 0 END)
    ) AS balance_after

FROM item_movements
GROUP BY asset_type_code, asset_type_name, asset_item
ORDER BY CAST(SUBSTRING(asset_type_code, 2) AS UNSIGNED), asset_item;