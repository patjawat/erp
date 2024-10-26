SELECT 
    t.*, 
    o.category_id AS category_code, 
    w.warehouse_name, 
    o.code, 
    o.data_json, 
    @running_total := IF(t.transaction_type = "IN", 
                         @running_total + t.qty, 
                         @running_total - t.qty) AS total, 
    (t.unit_price * t.qty) AS total_price 
FROM 
    stock_events t 
LEFT JOIN 
    warehouses w ON w.id = t.from_warehouse_id 
LEFT JOIN 
    stock_events o ON o.id = t.category_id 
                   AND o.name = "order" 
JOIN 
    (SELECT @running_total := 0) r 
WHERE 
    (t.asset_item = '02-00172') 
    AND (t.name = 'order_item') 
    AND (t.warehouse_id = 24) 
    AND (t.order_status = 'success') 
    AND (o.order_status = 'success') 
ORDER BY 
    t.created_at, 
    t.id, 
    created_at DESC;
