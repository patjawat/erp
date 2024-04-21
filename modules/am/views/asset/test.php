select x1.*
    from (select 
    LAST_DAY(m1) as date,
    IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
    (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = 63) ,LAST_DAY(m1))+1)  as date_number,
    DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
    DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = 63) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
    (select price FROM asset where id =63) as price,
    (SELECT receive_date FROM asset WHERE id = 63) as receive_date,
    (SELECT data_json->'$.service_life' FROM asset WHERE id = 63) as service_life,
    (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = 63) as month_price,
    (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = 63) as depreciation
    from
    (
    select ((SELECT receive_date FROM asset WHERE id = 63) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = 63))-1 DAY) 
    +INTERVAL m MONTH as m1
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
    where m1<= DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = 63) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = 63) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
    order by m1)as x1;