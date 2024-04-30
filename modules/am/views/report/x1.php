SELECT x5.*,
                                               SUM(IF(x5.date_number > (x5.service_life * 12),1,(x5.x_total + x5.month_price))) as price_last_month,
                                               SUM(x5.x_total) as total
                                               FROM(
                                               SELECT x4.*,
                                               IF((x4.price - total_price) < 1,1,ROUND((x4.price - total_price),2)) as x_total
                                               FROM (
                                               SELECT x3.*,
                                               IF(x3.count_days > 15, ROUND(x3.date_number * ((x3.price / x3.service_life)/12),2),0) as total_price,
                                                      (x3.days_x2 * price_days) as total_price2
                                                  FROM (
                                                      SELECT x2.*,

                                                  ROUND(x2.price  /  (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL x2.service_life YEAR,'%Y-%m-%d'),receive_date)),2) as price_days,
                                                  DATEDIFF(x2.date,x2.receive_date) as days_x2,
                                                  IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as count_days

                                                  FROM (select x1.*,

                                                   DAYOFMONTH(LAST_DAY(DATE_FORMAT(date, '%Y-%m-%d'))) as days_of_month,
                                                                          ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(date))+1)) as date_number
                                                                          FROM (
                                                                          SELECT
                                                                          a.id,
                                                                          i.title,
                                                                          a.code,
                                                                          asset_type.title as type_name,
                                                                          asset_type.code as type_code,
                                                                          a.data_json->'$.service_life' as service_life,
                                                                          CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                                                                          asset_group,
                                                                          receive_date,
                                                                          ('2024-04-30') as date,
                                                                          price,
                                                  asset_status,
                                                                          (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
                                                                          (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                                                                          ROUND((price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12),2) as month_price

                                                                          FROM asset a
                                                                          LEFT JOIN categorise i ON i.code = a.asset_item
                                                                          LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                                                                          ) as x1) as x2) as x3 WHERE   x3.receive_date <= x3.date AND x3.receive_date <= x3.date AND x3.asset_status = 1) as x4) as x5 GROUP BY x5.type_code
