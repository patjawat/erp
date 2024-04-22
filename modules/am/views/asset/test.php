SELECT *,
 (x3.total_days * price_days) as total_price,
    (x3.days_x2 * price_days) as total_price2,
    ROUND(x3.price-(x3.days_x2 * price_days),2) as total
FROM (SELECT *,
ROUND(x2.price  /  (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL life YEAR,'%Y-%m-%d'),receive_date)),2) as price_days,
DATEDIFF(x2.date,x2.receive_date) as days_x2,
IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as total_days

FROM (select *,

 DAYOFMONTH(LAST_DAY(DATE_FORMAT(date, '%Y-%m-%d'))) as days_of_month,
                        ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(date))+1)) as date_number
                        FROM (
                        SELECT 
                        i.title,
                        a.code,
                        asset_type.title as type_name,
                        asset_type.code as type_code,
                        a.data_json->'$.service_life' as life,
                        CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                        asset_group,
                        receive_date,
                         ('2017-11-30') as date,
                        (price-1) as price,

                        (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
                        (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                        (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
                        FROM asset a
                        LEFT JOIN categorise i ON i.code = a.asset_item
                        LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                        ) as xx) as x2) as x3 where x3.type_code = 10 AND x3.code = '3750-002-0002/61.01';