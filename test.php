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





WITH
    t as (
        select
            t.title AS asset_type,
            i.category_id AS category_id,
            i.code AS asset_item,
            i.title AS asset_name,
            json_unquote(
                json_extract(i.data_json, '$.unit')
            ) AS unit,
            so.code AS code,
            si.po_number AS po_number,
            wf.warehouse_type AS from_warehouse_type,
            wf.warehouse_name AS from_warehouse_name,
            w.warehouse_type AS warehouse_type,
            w.warehouse_name AS warehouse_name,
            si.transaction_type AS transaction_type,
            so.order_status AS order_status,
            so.warehouse_id AS warehouse_id,
            si.qty AS qty,
            si.unit_price AS unit_price,
            json_unquote(
                json_extract(
                    so.data_json,
                    '$.receive_date'
                )
            ) AS receive_date,
            so.created_at AS created_at,
            so.thai_year AS thai_year,
            si.total_price AS total_price
        from (
                (
                    (
                        (
                            (
                                stock_events so
                                left join stock_events si on (
                                    (
                                        (
                                            si.category_id = so.id
                                        )
                                        and (si.name = 'order_item')
                                    )
                                )
                            )
                            left join categorise i on (
                                (
                                    (
                                        i.code = si.asset_item
                                    )
                                    and (i.name = 'asset_item')
                                )
                            )
                        )
                        left join categorise t on (
                            (
                                (
                                    t.code = i.category_id
                                )
                                and (t.name = 'asset_type')
                            )
                        )
                    )
                    left join warehouses w on (
                        (
                            w.id = si.warehouse_id
                        )
                    )
                )
                left join warehouses wf on (
                    (
                        wf.id = si.from_warehouse_id
                    )
                )
            )
        where (i.category_id <> '')
    )
select
    t.asset_type AS asset_type,
    t.category_id AS category_id,
    t.asset_item AS asset_item,
    t.asset_name AS asset_name,
    t.unit AS unit,
    t.code AS code,
    t.po_number AS po_number,
    t.from_warehouse_type AS from_warehouse_type,
    t.from_warehouse_name AS from_warehouse_name,
    t.warehouse_type AS warehouse_type,
    t.warehouse_name AS warehouse_name,
    t.transaction_type AS transaction_type,
    t.order_status AS order_status,
    t.warehouse_id AS warehouse_id,
    t.qty AS qty,
    t.unit_price AS unit_price,
    t.receive_date AS receive_date,
    t.created_at AS created_at,
    t.thai_year AS thai_year,
    t.total_price AS total_price,
    (
        case
            when (t.transaction_type = 'IN') then month(t.receive_date)
            else month(t.created_at)
        end
    ) AS order_month
from t