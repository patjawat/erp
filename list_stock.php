SELECT 
    i.asset_item,
    SUM(CASE WHEN i.transaction_type = 'IN'  THEN i.qty ELSE 0 END) AS _in,
    SUM(CASE WHEN i.transaction_type = 'OUT' THEN i.qty ELSE 0 END) AS _out,
    SUM(CASE WHEN i.transaction_type = 'IN'  THEN i.qty * i.unit_price ELSE 0 END) AS _sum_in,
    SUM(CASE WHEN i.transaction_type = 'OUT' THEN i.qty * i.unit_price ELSE 0 END) AS _sum_out
FROM stock_events e
LEFT JOIN stock_events i 
    ON i.category_id = e.id 
    AND i.name = 'order_item'
WHERE e.name = 'order' AND e.movement_date BETWEEN '2025-08-01' AND '2025-08-31'
GROUP BY i.asset_item
ORDER BY i.asset_item
LIMIT 100;