<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax
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

// ถ้ามีการเลือกคลัง
if ($searchModel->warehouse_id) {
    $sql = "SELECT x.*,
    ((x.sum_last + x.sum_po) - (x.sum_branch + x.sum_sub)) as total FROM(SELECT *,
                SUM(CASE 
                        WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND  warehouse_id = :warehouse_id AND receive_date < LAST_DAY(
        CONCAT(
            (CASE 
                WHEN order_month > 9 THEN thai_year - 1 
                ELSE thai_year 
            END - 543), 
            '-', 
            LPAD(order_month, 2, '0'), 
            '-01'
        )
    )
    ) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END) 
                - SUM(CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND  warehouse_id = :warehouse_id AND receive_date < LAST_DAY(
            CONCAT(
                (CASE 
                    WHEN order_month > 9 THEN thai_year - 1 
                    ELSE thai_year 
                END - 543), 
                '-', 
                LPAD(order_month, 2, '0'), 
                '-01'
            )
        )
    ) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END)  AS sum_last,

                SUM(
                CASE 
                    WHEN (po_number IS NOT NULL AND  warehouse_id = :warehouse_id AND order_month = :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_po,
            
            SUM(
                CASE 
                    WHEN (transaction_type = 'OUT' AND from_warehouse_type = 'BRANCH' AND order_status = 'success' AND  warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_branch,
            
            SUM(
                CASE 
                    WHEN (transaction_type = 'OUT' AND from_warehouse_type = 'SUB' AND order_status = 'success' AND  warehouse_id = :warehouse_id AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_sub

        FROM view_stock_transaction GROUP BY category_id) as x ";

    $querys = Yii::$app->db->createCommand($sql, [
        ':warehouse_id' => $searchModel->warehouse_id,
        ':receive_month' => $searchModel->receive_month,
        ':thai_year' => $searchModel->thai_year,
    ])->queryAll();
} else {
    // ถ้าไม่เลือกคลังให้แสดงทั้งหมด
    $sql = "SELECT x.*,((x.sum_last + x.sum_po) - (x.sum_branch + x.sum_sub)) as total FROM(SELECT *,
                SUM(CASE 
                        WHEN (transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND receive_date <= LAST_DAY(
        CONCAT(
            (CASE 
                WHEN order_month > 9 THEN thai_year - 1 
                ELSE thai_year 
            END - 543), 
            '-', 
            LPAD(order_month, 2, '0'), 
            '-01'
        )
    )
    ) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END) 
                - SUM(CASE 
                        WHEN (transaction_type = 'OUT' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success'  AND receive_date <= LAST_DAY(
        CONCAT(
            (CASE 
                WHEN order_month > 9 THEN thai_year - 1 
                ELSE thai_year 
            END - 543), 
            '-', 
            LPAD(order_month, 2, '0'), 
            '-01'
        )
    )
    ) 
                        THEN (qty * unit_price) 
                        ELSE 0 
                    END)  AS sum_last,

                SUM(
                CASE 
                    WHEN (po_number IS NOT NULL AND order_month = :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_po,
            
            SUM(
                CASE 
                    WHEN (transaction_type = 'OUT' AND warehouse_type = 'BRANCH' AND order_status = 'success' AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_branch,
            
            SUM(
                CASE 
                    WHEN (transaction_type = 'OUT' AND warehouse_type = 'SUB' AND order_status = 'success'  AND MONTH(created_at) = :receive_month AND thai_year = :thai_year) 
                    THEN (qty * unit_price) 
                    ELSE 0 
                END
            ) AS sum_sub

        FROM view_stock_transaction GROUP BY category_id) as x ";

    $querys = Yii::$app->db->createCommand($sql, [
        ':receive_month' => $searchModel->receive_month,
        ':thai_year' => $searchModel->thai_year,
    ])->queryAll();
}

?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h6><i class="fa-solid fa-chart-pie"></i> สรุปงานวัสดุคงคลัง</h6>
            <?= $this->render('_search', ['model' => $searchModel]) ?>
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
                    <?php ?>
                    <?php
                    $sum_last = 0;
                    $sum_po = 0;
                    $sum_last_total = 0;
                    $sum_branch = 0;
                    $sum_sub = 0;
                    $sum_out = 0;
                    $sum_total = 0;
                    ?>
                    
                    <?php $num = 1;
                    foreach ($querys as $item): ?>
                        <?php
                        $sum_last += ($item['sum_last']);
                        $sum_po += $item['sum_po'];
                        $sum_last_total += ($item['sum_po'] + $item['sum_last']);
                        $sum_sub += $item['sum_sub'];
                        $sum_branch += $item['sum_branch'];
                        $sum_out += ($item['sum_branch'] + $item['sum_sub']);
                        $sum_total += $item['total'];
                        ?>
                    <tr>
                        <td class="text-center"><?= $num++; ?></td>
                        <td><?= $item['asset_type'] ?></td>
                        <td class="text-end fw-bolder"><?= number_format($item['sum_last'], 2) ?></td>
                        <td class="text-end fw-bolder"><?= number_format($item['sum_po'], 2) ?></td>
                        <td class="text-end fw-bolder"><?= number_format(($item['sum_po'] + $item['sum_last']), 2) ?></td>
                        <td class="text-end fw-bolder"><?= number_format($item['sum_branch'], 2) ?></td>
                        <td class="text-end fw-bolder"><?= number_format($item['sum_sub'], 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format(($item['sum_branch'] + $item['sum_sub']), 2) ?></td>
                        <td class="text-end fw-bolder"><?= number_format($item['total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
           
                    <tr>
                        <td class="text-center"></td>
                        <td>รวม</td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_last, 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_po, 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_last_total, 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_branch, 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_sub, 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_out, 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($sum_total, 2) ?></td>
                    </tr>
                </tbody>
            </table>


        </div>
        <div class="card-footer d-flex justify-content-end">
        <button id="download-button" class="btn btn-primary shadow">ดาวน์โหลดรายงาน</button>
    </div>
</div>
<?php Pjax::end(); ?>

<?php
$url = Url::to(['/inventory/report/export-excel', 'warehouse_id' => $searchModel->warehouse_id, 'receive_month' => $searchModel->receive_month, 'thai_year' => $searchModel->thai_year]);
$js = <<< JS
    \$("body").on("click", "#download-button", function (e) {
            var monthName = \$('#stockeventsearch-receive_month').find(':selected').text();
            var year = \$('#stockeventsearch-thai_year').find(':selected').text();
            \$.ajax({
                url: '$url', // Adjust to match your controller and action URL
                method: 'GET',
                xhrFields: {
                    responseType: 'blob' // Important for handling binary data
                },
                beforeSend: function(){
                    beforLoadModal();
                },
                success: function(data) {
                    \$("#main-modal").modal("toggle");
                    var monthName = \$('#stockeventsearch-receive_month').find(':selected').text();
                    var filename = 'รายงานสรุปวัสดุคงคลังประจำเดือน ' + monthName + ' ปี ' + year + '.xlsx'; // Adjust the filename as needed
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                },
                error: function() {
                    alert('File could not be downloaded.');
                }
            });
        });


    JS;
$this->registerJS($js, View::POS_END);
?>

