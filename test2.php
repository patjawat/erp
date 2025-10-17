SELECT 
    i.thai_year,

    -- ตุลาคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 10  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in10,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 10 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out10,

    -- พฤศจิกายน
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 11  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in11,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 11 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out11,

    -- ธันวาคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 12  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in12,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 12 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out12,

    -- มกราคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 1  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in1,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 1 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out1,

    -- กุมภาพันธ์
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 2  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in2,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 2 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out2,

    -- มีนาคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 3  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in3,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 3 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out3,

    -- เมษายน
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 4  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in4,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 4 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out4,

    -- พฤษภาคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 5  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in5,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 5 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out5,

    -- มิถุนายน
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 6  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in6,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 6 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out6,

    -- กรกฎาคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 7  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in7,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 7 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out7,

    -- สิงหาคม
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 8  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in8,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 8 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out8,

    -- กันยายน
    SUM(CASE 
        WHEN i.transaction_type = 'IN'  
             AND MONTH(e.movement_date) = 9  
             AND w.warehouse_type = 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS in9,
    SUM(CASE 
        WHEN i.transaction_type = 'OUT' 
             AND MONTH(e.movement_date) = 9 
             AND w.warehouse_type != 'MAIN' 
        THEN i.qty * i.unit_price 
        ELSE 0 
    END) AS out9

FROM stock_events AS i
LEFT JOIN warehouses AS w ON w.id = i.warehouse_id
LEFT JOIN stock_events AS e ON e.id = i.category_id
WHERE 
    i.thai_year = '2569'
    AND i.order_status = 'success'
GROUP BY 
    i.thai_year;
