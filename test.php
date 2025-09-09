SELECT 
    i.title,
    i.category_id,
     SUM(CASE WHEN e.transaction_type = 'IN'  THEN e.qty ELSE 0 END) AS qty_in,
    SUM(CASE WHEN e.transaction_type = 'OUT' THEN e.qty ELSE 0 END) AS qty_out,
    SUM(CASE WHEN e.transaction_type = 'IN'  THEN e.qty * e.unit_price ELSE 0 END) AS total_price_in,
    SUM(CASE WHEN e.transaction_type = 'OUT' THEN e.qty * e.unit_price ELSE 0 END) AS total_price_out,
    (
        SUM(CASE WHEN e.transaction_type = 'IN'  THEN e.qty ELSE 0 END) -
        SUM(CASE WHEN e.transaction_type = 'OUT' THEN e.qty ELSE 0 END)
    ) AS result_qty,
        (
        SUM(CASE WHEN e.transaction_type = 'IN'  THEN e.qty * e.unit_price ELSE 0 END) -
        SUM(CASE WHEN e.transaction_type = 'OUT' THEN e.qty * e.unit_price ELSE 0 END)
    ) AS result_price
FROM stock_events e
LEFT JOIN categorise i 
    ON i.code = e.asset_item 
   AND i.name = 'asset_item'
WHERE e.warehouse_id = 7
  AND DATE_FORMAT(e.movement_date, '%Y-%m-%d') BETWEEN '2025-01-01' AND '2025-08-31'
GROUP BY i.title;