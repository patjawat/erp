<?php
$sql = "SELECT x1.*,
(select sum(o.qty * o.price) 
     FROM orders o 
      	INNER JOIN categorise oi ON oi.code = o.asset_item AND oi.name ='asset_item'
		INNER JOIN categorise ot ON ot.code = oi.category_id AND ot.name='asset_type'
     where o.name = 'order_item' AND ot.code = x1.code AND MONTH(STR_TO_DATE(o.data_json->>'$.po_date', '%Y-%m-%d')) = 9
    ) as purchase
FROM(SELECT 
	t.code,
	t.title,sum(s.qty*s.unit_price) as total
FROM stock s 
INNER JOIN categorise i ON i.code = s.asset_item AND i.name ='asset_item'
INNER JOIN categorise t ON t.code = i.category_id AND t.name='asset_type'
GROUP BY t.code) as x1;";
?>