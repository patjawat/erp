SELECT x3.*,
ROUND(IF(x3.days = 0,0,(x3.year_price/12)),2) as price_month,
IF((x3.price - total_price) < 1,1,ROUND((x3.price - total_price),2)) as total
FROM(
SELECT x2.*,
 IF(x2.count_days > 15, x2.count_days,0) as days,
 (x2.price / x2.service_life) as year_price,
  IF(x2.count_days > 15, ROUND(x2.date_number * ((x2.price / x2.service_life)/12),2),0) as total_price
FROM(
SELECT x1.*,
IF(x1.date_number = 1, DATEDIFF(x1.end_date,receive_date),x1.days_of_month) as count_days

FROM(
select 
(TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = 1519) ,LAST_DAY(m1))+1)  as date_number,
    (SELECT receive_date FROM asset WHERE id = 1519) as receive_date,
    DATE_FORMAT(m1, '%Y-%m-%d') as start_date,
    LAST_DAY(m1) as end_date,
     DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,

DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = 1519) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = 1519) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d') as begin_date,
    (SELECT price FROM asset where id =1519) as price,
    (SELECT data_json->'$.service_life' FROM asset WHERE id = 1519) as service_life,
    (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = 1519) as dep
    

from
(
select ((SELECT receive_date FROM asset WHERE id = 1519) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = 1519))-1 DAY) + INTERVAL m MONTH as m1
from
(
select @rownum:=@rownum+1 as m from
(select 1 union select 2 union select 3 union select 4) t1,
(select 1 union select 2 union select 3 union select 4) t2,
(select 1 union select 2 union select 3 union select 4) t3,
(select 1 union select 2 union select 3 union select 4) t4,
(select @rownum:=-1) t0
) d1
) d2 
where m1<=DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = 1519) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = 1519) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
order by m1) as x1) as x2) as x3;