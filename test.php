SELECT 
    thai_year,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 THEN qty * unit_price ELSE 0 END), 2) AS in10,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 10 THEN qty * unit_price ELSE 0 END), 2) AS out10,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 THEN qty * unit_price ELSE 0 END), 2) AS in11,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 11 THEN qty * unit_price ELSE 0 END), 2) AS out11,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 12 THEN qty * unit_price ELSE 0 END), 2) AS in12,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 12 THEN qty * unit_price ELSE 0 END), 2) AS out12,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 THEN qty * unit_price ELSE 0 END), 2) AS in1,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 1 THEN qty * unit_price ELSE 0 END), 2) AS out1,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 THEN qty * unit_price ELSE 0 END), 2) AS in2,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 2 THEN qty * unit_price ELSE 0 END), 2) AS out2,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 THEN qty * unit_price ELSE 0 END), 2) AS in3,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 3 THEN qty * unit_price ELSE 0 END), 2) AS out3,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 THEN qty * unit_price ELSE 0 END), 2) AS in4,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 4 THEN qty * unit_price ELSE 0 END), 2) AS out4,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 THEN qty * unit_price ELSE 0 END), 2) AS in5,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 5 THEN qty * unit_price ELSE 0 END), 2) AS out5,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 THEN qty * unit_price ELSE 0 END), 2) AS in6,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 6 THEN qty * unit_price ELSE 0 END), 2) AS out6,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 THEN qty * unit_price ELSE 0 END), 2) AS in7,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 7 THEN qty * unit_price ELSE 0 END), 2) AS out7,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 THEN qty * unit_price ELSE 0 END), 2) AS in8,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 8 THEN qty * unit_price ELSE 0 END), 2) AS out8,
    ROUND(SUM(CASE WHEN transaction_type = 'IN'  AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 THEN qty * unit_price ELSE 0 END), 2) AS in9,
    ROUND(SUM(CASE WHEN transaction_type = 'OUT' AND warehouse_id = :warehouse_id AND MONTH(created_at) = 9 THEN qty * unit_price ELSE 0 END), 2) AS out9
FROM stock_events
WHERE warehouse_id = :warehouse_id 
AND order_status = 'success' 
AND thai_year = 2568
GROUP BY thai_year;
