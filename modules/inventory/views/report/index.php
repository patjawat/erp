<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;

$this->title = 'สรุปรายงานวัสดุคงคลัง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu_dashbroad'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>



<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white"><i class="fa-solid fa-chart-pie"></i> สรุปงานวัสดุคงคลัง</h6>
    </div>
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center">

        </div>
        <table class="table table-bordered table-striped">
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
            <tbody class="align-middle table-group-divider">
                <?php
                // helper function กัน null
                function nf($value, $decimals = 2)
                {
                    return number_format(floatval($value ?? 0), $decimals);
                }

                $sum_last = 0;
                $sum_month = 0;
                $sum_last_total = 0;
                $sum_branch = 0;
                $sum_sub = 0;
                $sum_out = 0;
                $sum_total = 0;

                $num = 1;
                foreach ($querys as $item):
                    $last_stock = floatval($item['last_stock_in'] ?? 0) - floatval($item['last_stock_out'] ?? 0);
                    $month = floatval($item['sum_month'] ?? 0);
                    $branch = floatval($item['sum_branch'] ?? 0);
                    $sub = floatval($item['sum_sub'] ?? 0);
                    $total = floatval($item['total'] ?? 0);

                    $sum_last       += $last_stock;
                    $sum_month      += $month;
                    $sum_last_total += ($month + $last_stock);
                    $sum_branch     += $branch;
                    $sum_sub        += $sub;
                    $sum_out        += ($branch + $sub);
                    $sum_total      += $total;
                ?>
                    <tr>
                        <!-- ที่ -->
                        <td class="text-center"><?= $num++; ?></td>
                        <!-- รายการ -->
                        <td><?= $item['asset_type'] ?></td>
                        <!-- สินค้าคงเหลือ -->
                        <td class="text-end fw-bolder"><?= nf($last_stock) ?></td>
                        <!-- ซื้อระหว่างเดือน -->
                        <td class="text-end fw-bolder"><?= nf($month) ?></td>
                        <!-- รวม -->
                        <td class="text-end fw-bolder"><?= nf($month + $last_stock) ?></td>
                        <!-- จ่ายส่วนของ รพ.สต. -->
                        <td class="text-end fw-bolder"><?= nf($branch) ?></td>
                        <!-- จ่ายส่วนของโรงพยาบาล -->
                        <td class="text-end fw-bolder"><?= nf($sub) ?></td>
                        <!-- รวม -->
                        <td class="text-end fw-bolder"><?= nf($branch + $sub) ?></td>
                        <!-- ยอดยกไป -->
                        <td class="text-end fw-bolder"><?= nf($total) ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td class="text-center"></td>
                    <td>รวม</td>
                    <td class="text-end fw-bolder"><?= nf($sum_last) ?></td>
                    <td class="text-end fw-bolder"><?= nf($sum_month) ?></td>
                    <td class="text-end fw-bolder"><?= nf($sum_last_total) ?></td>
                    <td class="text-end fw-bolder"><?= nf($sum_branch) ?></td>
                    <td class="text-end fw-bolder"><?= nf($sum_sub) ?></td>
                    <td class="text-end fw-bolder"><?= nf($sum_out) ?></td>
                    <td class="text-end fw-bolder"><?= nf($sum_total) ?></td>
                </tr>
            </tbody>

        </table>


    </div>
    <div class="card-footer d-flex justify-content-end">
        <button id="download-button" class="btn btn-primary shadow">ดาวน์โหลดรายงาน</button>
    </div>
</div>


<?php
$url = Url::to(['/inventory/report/export-excel', 'warehouse_id' => $searchModel->warehouse_id, 'date_start' => $dateStart, 'date_end' => $dateEnd]);
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