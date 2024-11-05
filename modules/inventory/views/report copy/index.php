<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu_dashbroad'); ?>
<?php $this->endBlock(); ?>

<?php
// ... (SQL queries remain unchanged)


$sql1 = "with t as (
SELECT
    x.type_code,
    x.title,
    x.transaction_type,
    x.se_code,
    x.asset_item,
    SUM(x.qty) AS total_qty,
    x.unit_price,
    x.po_number,
    SUM(IF(x.po_number IS NOT NULL, ROUND(x.qty * x.unit_price), 0)) AS po_total
FROM
   (SELECT
        t.code AS type_code,
        t.title,
        se.transaction_type,
        se.code AS se_code,
        se.asset_item,
        se.qty,
        se.unit_price,
        se.po_number
    FROM
        stock_events se
    INNER JOIN
        categorise i ON i.code = se.asset_item AND i.name = 'asset_item'
    INNER JOIN
        categorise t ON t.code = i.category_id AND t.name = 'asset_type'
    WHERE
        se.name = 'order_item'
    ORDER BY
        se.po_number DESC
    ) AS x
GROUP BY
    x.type_code, x.title, x.transaction_type, x.se_code, x.asset_item, x.unit_price, x.po_number
ORDER BY x.po_number DESC
    )
select *,sum(t.po_total) from t GROUP BY t.type_code;";



$sql_old = "SELECT x1.*,
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

$sql = "WITH t AS (
SELECT 
        si.id,
        so.warehouse_id,
        w.warehouse_name,
        w.warehouse_type,
        so.data_json->>'$.receive_date' AS receive_date,
        MONTH(so.data_json->>'$.receive_date') AS receive_month,
        IF(
            MONTH(so.data_json->>'$.receive_date') > 9, 
            YEAR(so.data_json->>'$.receive_date') + 1, 
            YEAR(so.data_json->>'$.receive_date')
        ) + 543 AS receive_year,
        t.code AS type_code,
        t.title,
        so.transaction_type,
        so.code AS se_code,
        si.asset_item,
        si.qty,
        si.unit_price,
        SUM(IF(si.po_number IS NOT NULL, ROUND(si.qty * si.unit_price), 0)) AS po_price
    FROM 
        stock_events so
    INNER JOIN 
        stock_events si ON si.category_id = so.id AND si.name = 'order_item'
    INNER JOIN 
        categorise i ON i.code = si.asset_item AND i.name = 'asset_item'
    INNER JOIN 
        categorise t ON t.code = i.category_id AND t.name = 'asset_type'
    INNER JOIN warehouses w ON w.id = so.warehouse_id
    GROUP BY
        t.code, 
        t.title, 
        si.transaction_type, 
        si.code, 
        si.asset_item, 
        si.unit_price, 
        si.po_number
    ORDER BY 
        po_price
    )

SELECT 
    *,
     SUM(t.po_price) AS total_po_price,
    SUM(IF(t.receive_month = 9, ROUND(t.qty * t.unit_price), 0)) AS total_month,
    SUM(IF(t.receive_month = 10, ROUND(t.qty * t.unit_price), 0)) AS last_total_month
FROM 
    t
GROUP BY t.type_code";
$querys = Yii::$app->db->createCommand($sql_old)->queryAll();

?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h6><i class="fa-solid fa-chart-pie"></i> สรุปงานวัสดุคงคลัง คลังวัสดุ</h6>
            <?=$this->render('_search', ['model' => $searchModel])?>
        </div>
            <table class="table table-bordered table-striped mt-3">
                <thead class="align-middle text-center">
                    <tr>
                        <th rowspan="2">ที่</th>
                        <th rowspan="2">รายการ</th>
                        <th rowspan="2"><span>สินค้าคงเหลือ</span></th>
                        <th rowspan="2">ซื้อระหว่างเดือน</th>
                        <th rowspan="2">รวม</th>
                        <th colspan="3">สินค้าที่ใช้ไป</th>
                        <th rowspan="2">สินค้าคงเหลือ</th>
                    </tr>
                    <tr>
                        <th class="text-center">จ่ายส่วนของ รพ.สต.</th>
                        <th class="text-center">จ่ายส่วนของโรงพยาบาล</th>
                        <th class="text-center">รวม</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php $num = 1;foreach($querys as $item):?>
                    <tr>
                        <td><?=$num++;?></td>
                        <td><?=$item['title']?></td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder"><?=number_format($item['total'],2)?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>


    </div>
</div>
