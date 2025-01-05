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
$sql1 = "with t as (SELECT  
t.title,
    i.category_id,
    so.code,
    w.warehouse_type,
    si.transaction_type,
    si.qty,
    si.unit_price,
    
    -- Extract month from the receive_date in JSON and convert it to integer for stock month
    MONTH(so.data_json->>'$.receive_date') AS stock_month,
    
    -- Calculate Thai year with adjustment based on receive_date month
    (IF(MONTH(so.data_json->>'$.receive_date') > 9, 
        YEAR(so.data_json->>'$.receive_date') + 1, 
        YEAR(so.data_json->>'$.receive_date')
    ) + 543) AS thai_year,
    
    -- Calculate sum for main warehouse 'IN' transactions minus 'OUT' transactions for sub/branch warehouses
    (
        SUM(CASE 
                WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'$.receive_date') < :receive_month AND so.thai_year = (:thai_year -1)) 
                THEN (si.qty * si.unit_price) 
                ELSE 0 
            END) 
        - SUM(CASE 
                WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'$.receive_date') < :receive_month AND so.thai_year = :thai_year) 
                THEN (si.qty * si.unit_price) 
                ELSE 0 
            END)
    ) AS sum_last,
    
    -- Sum of Purchase Orders (PO) where PO number is not NULL
        SUM(
        CASE 
            WHEN (si.po_number IS NOT NULL AND so.warehouse_id = :warehouse_id AND MONTH(so.data_json->>'$.receive_date') = :receive_month AND so.thai_year = :thai_year) 
            THEN (si.qty * si.unit_price) 
            ELSE 0 
        END
    ) AS sum_po,
    
    -- Calculate total for 'IN' transactions in branch warehouse
    SUM(
        CASE 
            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
            THEN (si.qty * si.unit_price) 
            ELSE 0 
        END
    ) AS sum_branch,
    
    -- Calculate total for 'IN' transactions in sub-warehouse
    SUM(
        CASE 
            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
            THEN (si.qty * si.unit_price) 
            ELSE 0 
        END
    ) AS sum_sub,
    
    -- Calculate overall total (main warehouse 'IN' minus sub/branch warehouse 'OUT' transactions)
    // (
    //     SUM(CASE 
    //             WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN'AND so.warehouse_id = :warehouse_id  AND MONTH(so.data_json->>'$.receive_date') <= :receive_month AND so.thai_year = :thai_year) 
    //             THEN (si.qty * si.unit_price) 
    //             ELSE 0 
    //         END) 
    //     - SUM(CASE 
    //             WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success' AND so.warehouse_id = :warehouse_id AND MONTH(so.created_at) = :receive_month AND so.thai_year = :thai_year) 
    //             THEN (si.qty * si.unit_price) 
    //             ELSE 0 
    //         END)
    // ) AS total

FROM 
    stock_events so
    LEFT OUTER JOIN stock_events si 
        ON si.category_id = so.id AND si.name = 'order_item'
    LEFT OUTER JOIN categorise i 
        ON i.code = si.asset_item AND i.name = 'asset_item'
    LEFT OUTER JOIN categorise t 
    	ON t.code = i.category_id AND t.name='asset_type'
    LEFT OUTER JOIN warehouses w 
        ON w.id = si.warehouse_id
 WHERE i.category_id <> ''

-- Group results by category ID
GROUP BY 
    i.category_id  
-- Order results by category ID in ascending order
ORDER BY 
    i.category_id ASC) select *,(t.sum_last) as total from t;";
    
    $qerys = Yii::$app->db->createCommand($sql1,
    [
        ':warehouse_id' => $searchModel->warehouse_id,
        ':receive_month' =>$searchModel->receive_month,
        ':thai_year' => $searchModel->thai_year,
    ])->queryAll();
    // $qerys = Yii::$app->db->createCommand($sql1)->queryAll();

    $sqlSummary = "SELECT  
t.title,
    i.category_id,
    so.code,
    w.warehouse_type,
    si.transaction_type,
    si.qty,
    si.unit_price,
    
    -- Extract month from the receive_date in JSON and convert it to integer for stock month
    MONTH(so.data_json->>'$.receive_date') AS stock_month,
    
    -- Calculate Thai year with adjustment based on receive_date month
    (IF(MONTH(so.data_json->>'$.receive_date') > 9, 
        YEAR(so.data_json->>'$.receive_date') + 1, 
        YEAR(so.data_json->>'$.receive_date')
    ) + 543) AS thai_year,
    
    -- Calculate sum for main warehouse 'IN' transactions minus 'OUT' transactions for sub/branch warehouses
    (
        SUM(CASE 
                WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN'  AND MONTH(so.data_json->>'$.receive_date') < :receive_month) 
                THEN (si.qty * si.unit_price) 
                ELSE 0 
            END) 
        - SUM(CASE 
                WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH')  AND MONTH(so.data_json->>'$.receive_date') < :receive_month) 
                THEN (si.qty * si.unit_price) 
                ELSE 0 
            END)
    ) AS sum_last,
    
    -- Sum of Purchase Orders (PO) where PO number is not NULL
        SUM(
        CASE 
            WHEN (si.po_number IS NOT NULL AND MONTH(so.data_json->>'$.receive_date') = :receive_month) 
            THEN (si.qty * si.unit_price) 
            ELSE 0 
        END
    ) AS sum_po,
    
    -- Calculate total for 'IN' transactions in branch warehouse
    SUM(
        CASE 
            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'BRANCH' AND so.order_status = 'success'  AND MONTH(so.created_at) = :receive_month) 
            THEN (si.qty * si.unit_price) 
            ELSE 0 
        END
    ) AS sum_branch,
    
    -- Calculate total for 'IN' transactions in sub-warehouse
    SUM(
        CASE 
            WHEN (si.transaction_type = 'OUT' AND w.warehouse_type = 'SUB' AND so.order_status = 'success' AND MONTH(so.created_at) = :receive_month) 
            THEN (si.qty * si.unit_price) 
            ELSE 0 
        END
    ) AS sum_sub,
    
    -- Calculate overall total (main warehouse 'IN' minus sub/branch warehouse 'OUT' transactions)
    (
        SUM(CASE 
                WHEN (si.transaction_type = 'IN' AND w.warehouse_type = 'MAIN' AND MONTH(so.data_json->>'$.receive_date') <= :receive_month) 
                THEN (si.qty * si.unit_price) 
                ELSE 0 
            END) 
        - SUM(CASE 
                WHEN (si.transaction_type = 'OUT' AND w.warehouse_type IN ('SUB', 'BRANCH') AND so.order_status = 'success'AND MONTH(so.created_at) = :receive_month) 
                THEN (si.qty * si.unit_price) 
                ELSE 0 
            END)
    ) AS total

FROM 
    stock_events so
    LEFT OUTER JOIN stock_events si 
        ON si.category_id = so.id AND si.name = 'order_item'
    LEFT OUTER JOIN categorise i 
        ON i.code = si.asset_item AND i.name = 'asset_item'
    LEFT OUTER JOIN categorise t 
    	ON t.code = i.category_id AND t.name='asset_type'
    LEFT OUTER JOIN warehouses w 
        ON w.id = si.warehouse_id
 WHERE i.category_id <> ''";
$summary = Yii::$app->db->createCommand($sqlSummary, [':receive_month' =>$searchModel->receive_month])->queryOne();
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
                <tbody class="align-middle table-group-divider">
                    <?php $num = 1;foreach($qerys as $item):?>
                    <tr>
                        <td class="text-center"><?=$num++;?></td>
                        <td><?=$item['title']?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['sum_last'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['sum_po'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($item['sum_po']+$item['sum_last']),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['sum_branch'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['sum_sub'],2)?></td>
                        <td class="text-end fw-bolder"><?php echo number_format(($item['sum_branch']+$item['sum_sub']),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($item['total'],2)?></td>
                    </tr>
                    <?php endforeach;?>
                    <tr>
                        <td class="text-center"></td>
                        <td>รวม</td>
                        <td class="text-end fw-bolder"><?=number_format($summary['sum_last'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($summary['sum_po'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($summary['sum_po']+$item['sum_last']),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($summary['sum_branch'],2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($summary['sum_sub'],2)?></td>
                        <td class="text-end fw-bolder"><?php echo number_format(($summary['sum_branch']+$summary['sum_sub']),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($summary['total'],2)?></td>
                    </tr>
                </tbody>
            </table>


    </div>
</div>
<?php Pjax::end(); ?>

