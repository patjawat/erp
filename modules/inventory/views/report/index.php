<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
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

    $date = $searchModel->thai_year.'-'.$searchModel->receive_month.'-01';
    // $receive_date = AppHelper::ThaiToGregorian($date);
    
    // $dateStart =  AppHelper::convertToGregorian($searchModel->date_start);
    // $dateEnd =  AppHelper::convertToGregorian($searchModel->date_end);

// ถ้ามีการเลือกคลัง
// i

if($searchModel->warehouse_id && $searchModel->warehouse_id !==''){
       
    $sql = "SELECT x.category_id,x.warehouse_id,x.asset_type, 
    SUM(x.qty*x.unit_price) as total,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.category_id = x.category_id AND x1.order_status = 'success'
    AND transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
    AND warehouse_id = :warehouse_id)  as last_stock_in,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.category_id = x.category_id AND x1.order_status = 'success'
    AND transaction_type = 'IN' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
    AND warehouse_id = :warehouse_id)  as last_stock_out,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.category_id = x.category_id AND x1.order_status = 'success'
    AND x1.transaction_type = 'IN' AND warehouse_type = 'MAIN' AND receive_date BETWEEN :date_start AND :date_end
    AND warehouse_id = :warehouse_id)  as sum_month,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.category_id = x.category_id AND order_status = 'success'
    AND from_warehouse_type = 'BRANCH' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
    AND warehouse_id = :warehouse_id)  as sum_branch,

    (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
    WHERE x1.category_id = x.category_id AND order_status = 'success'
    AND from_warehouse_type = 'SUB' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
    AND warehouse_id = :warehouse_id)  as sum_sub

    FROM `view_stock_transaction` x
    GROUP BY x.category_id";

$querys = Yii::$app->db->createCommand($sql, [
':date_start' => $dateStart,
':date_end' => $dateEnd,
':warehouse_id' => $searchModel->warehouse_id,
])->queryAll();

}else{
    // ถ้าไม่เลือกคลังให้แสดงทั้งหมด
    $sql = "SELECT x.category_id,x.warehouse_id,x.asset_type, 
                SUM(x.qty*x.unit_price) as total,

                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.category_id = x.category_id AND x1.order_status = 'success'
                AND transaction_type = 'IN' AND warehouse_type = 'MAIN' AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
                )  as last_stock_in,

                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.category_id = x.category_id AND x1.order_status = 'success'
                AND transaction_type = 'IN' AND warehouse_type IN ('SUB', 'BRANCH') AND order_status = 'success' AND receive_date <= LAST_DAY(DATE_SUB(:date_start, INTERVAL 1 MONTH)) 
                )  as last_stock_out,

                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.category_id = x.category_id AND x1.order_status = 'success'
                AND x1.transaction_type = 'IN' AND warehouse_type = 'MAIN' AND receive_date BETWEEN :date_start AND :date_end
                )  as sum_month,

                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.category_id = x.category_id AND order_status = 'success'
                AND warehouse_type = 'BRANCH' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
                )  as sum_branch,

                (select IFNULL(sum(x1.unit_price * x1.qty),0) FROM view_stock_transaction x1 
                WHERE x1.category_id = x.category_id AND order_status = 'success'
                AND warehouse_type = 'SUB' AND x1.transaction_type = 'OUT' AND DATE_FORMAT(x1.created_at,'%Y-%m-%d') BETWEEN :date_start AND :date_end
                )  as sum_sub

                FROM `view_stock_transaction` x
                GROUP BY x.category_id";

    $querys = Yii::$app->db->createCommand($sql, [
       ':date_start' => $dateStart,
       ':date_end' => $dateEnd,
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
                        <th rowspan="2">ยอดยกไป</th>
                    </tr>
                    <tr>
                        <th class="text-center">จ่ายส่วนของ รพ.สต.</th>
                        <th class="text-center">จ่ายส่วนของโรงพยาบาล</th>
                        <th class="text-center">รวม</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
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
                        // $sum_last += ($item['sum_last']);
                        // $sum_po += $item['sum_po'];
                        // $sum_last_total += ($item['sum_po'] + $item['sum_last']);
                        // $sum_sub += $item['sum_sub'];
                        // $sum_branch += $item['sum_branch'];
                        // $sum_out += ($item['sum_branch'] + $item['sum_sub']);
                        $total =  ($item['sum_month'] + ($item['last_stock_in']-$item['last_stock_out'])) - ($item['sum_branch'] + $item['sum_sub']);
                         $sum_total += $total;
                        ?>
                    <tr>
                        <td class="text-center"><?= $num++; ?></td>
                        <td><?= $item['asset_type'] ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format(($item['last_stock_in']-$item['last_stock_out']), 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($item['sum_month'], 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format(($item['sum_month'] + ($item['last_stock_in']-$item['last_stock_out'])), 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($item['sum_branch'], 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($item['sum_sub'], 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format(($item['sum_branch'] + $item['sum_sub']), 2) ?></td>
                        <td class="text-end fw-bolder"><?php echo number_format($total, 2) ?></td>
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

