<?php
use yii\widgets\Pjax;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu_dashbroad'); ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['timeout' => 88888888]); ?>
<?php
$sql1 = "SELECT 
    x.*, 
    COALESCE(sum_last, 0) AS sum_last,
    COALESCE(sum_po, 0) AS sum_po,
    COALESCE(total, 0) AS total
FROM 
    (SELECT 
        t.code, 
        t.title
    FROM 
        categorise t
    WHERE 
        t.name = 'asset_type'
        AND t.category_id = 4
    ) AS x
LEFT JOIN 
    (SELECT 
        c.category_id AS code, 
        SUM(IF(si.po_number IS NOT NULL, ROUND(si.qty * si.unit_price), 0)) AS sum_last
    FROM 
        stock_events si
    INNER JOIN 
        categorise c ON c.code = si.asset_item AND c.name = 'asset_item'
    INNER JOIN 
        warehouses w ON w.id = si.warehouse_id
    INNER JOIN 
        stock_events so ON si.category_id = so.id
    WHERE 
        MONTH(so.data_json->>'$.receive_date') < :receive_month
        AND (IF(MONTH(so.data_json->>'$.receive_date') > 9, YEAR(so.data_json->>'$.receive_date') + 1, YEAR(so.data_json->>'$.receive_date')) + 543) = :thai_year
    GROUP BY 
        c.category_id
    ) AS s ON x.code = s.code
LEFT JOIN 
    (SELECT 
        c.category_id AS code, 
        SUM(IF(si.po_number IS NOT NULL, ROUND(si.qty * si.unit_price), 0)) AS sum_po
    FROM 
        stock_events si
    INNER JOIN 
        categorise c ON c.code = si.asset_item AND c.name = 'asset_item'
    INNER JOIN 
        warehouses w ON w.id = si.warehouse_id
    INNER JOIN 
        stock_events so ON si.category_id = so.id
    WHERE 
        MONTH(so.data_json->>'$.receive_date') = :receive_month
        AND (IF(MONTH(so.data_json->>'$.receive_date') > 9, YEAR(so.data_json->>'$.receive_date') + 1, YEAR(so.data_json->>'$.receive_date')) + 543) = :thai_year
    GROUP BY 
        c.category_id
    ) AS p ON x.code = p.code
LEFT JOIN 
    (SELECT 
        c.category_id AS code,
        SUM(CASE WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN') THEN (si.qty * si.unit_price) ELSE 0 END) - 
        SUM(CASE WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB') THEN (si.qty * si.unit_price) ELSE 0 END) AS total
    FROM 
        stock_events si
    INNER JOIN 
        categorise c ON c.code = si.asset_item AND c.name = 'asset_item'
    INNER JOIN 
        warehouses w ON w.id = si.warehouse_id
    INNER JOIN 
        stock_events so ON si.category_id = so.id
    GROUP BY 
        c.category_id
    ) AS t ON x.code = t.code;";
    $qerys = Yii::$app->db->createCommand($sql1,
    [':receive_month' =>$searchModel->receive_month,
    ':thai_year' => $searchModel->thai_year,
    ])->queryAll();
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
                    <?php $num = 1;foreach($qerys as $item):?>
                    <tr>
                        <td class="text-center"><?=$num++;?></td>
                        <td><?=$item['title']?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['sum_last'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['sum_po'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($item['sum_po']+$item['sum_last']),2)?></td>
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
<?php Pjax::end(); ?>

