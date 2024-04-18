select *,
        ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL life YEAR,'%Y-%m-%d'),date)),2) as price_days,
         ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL life YEAR,'%Y-%m-%d'),date))*30) * date_number),2)  as price_days_X_datenum,
                (date_number * month_price) as sum_price_month,
                ROUND((xx.month_price * date_number),2) as total_month_price,
                ROUND(IF(xx.price -(xx.month_price * date_number) <= 1,1,(xx.price -(xx.month_price * date_number))),2) as total,
                DATE_FORMAT(xx.date,'%d') as days
                FROM (
                SELECT 
                    a.id,
                i.title,
                a.code,
                asset_type.title as type_name,
                asset_type.code as type_code,
                a.data_json->'$.service_life' as life,
                CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                asset_group,
                receive_date as date,
                price,
                ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY('2023-01-30'))+1)) as date_number,
                (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as day_number,
                (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
                FROM asset a
                LEFT JOIN categorise i ON i.code = a.asset_item
                LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                ) as xx WHERE (price - (date_number * month_price)) > 0 AND (xx.date_number >=1)