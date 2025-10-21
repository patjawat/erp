<?php

use yii\web\View;
use yii\helpers\Url;

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
                $num = 1;
                $sumPriceOut = 0; //รวมสินค้าที่ใช้ไป
                $totalPriceBegin = 0; //สินค้าคงเหลือ
                $totalPriceIn = 0; //ซื้อระหว่างเดือน
                $totalPriceBranch = 0; //จ่ายส่วนของ รพ.สต.
                $totalPriceSub = 0; //จ่ายส่วนของโรงพยาบาล
                $totalPriceSubBranch = 0; //สินค้าที่ใช้ไป
                $totalPriceEnd = 0; //ยอดยกไป
                foreach ($querys as $item):
                $sumPriceOut = ($item['branch_price_out']+$item['price_out'] ?? 0);
                
                $totalPriceBegin+=$item['begin_price'];
                $totalPriceIn+=$item['price_in'];
                $totalPriceBranch+=$item['branch_price_out'];
                $totalPriceSub+=$item['price_out'];
                $totalPriceSubBranch+=($item['branch_price_out']+$item['price_out']);
                $totalPriceEnd+=$item['end_price'];
                ?>
                    <tr>
                        <!-- ที่ -->
                        <td class="text-center"><?= $num++; ?></td>
                        <!-- รายการ -->
                        <td>(<?= $item['asset_type_code'] ?>)<?= $item['asset_type_name'] ?></td>
                        <!-- สินค้าคงเหลือ -->
                        <td class="text-end fw-bolder"><?= number_format(($item['begin_price'] ?? 0),2)?></td>
                        <!-- ซื้อระหว่างเดือน -->
                        <td class="text-end fw-bolder">
                            <?= number_format(($item['price_in'] ?? 0),2)?>
                        </td>
                        <!-- รวม -->
                        <td class="text-end fw-bolder"><?= number_format(($item['begin_price']+$item['price_in'] ?? 0),2) ?></td>
                        <!-- จ่ายส่วนของ รพ.สต. -->
                        <td class="text-end fw-bolder"> <?= number_format(($item['branch_price_out'] ?? 0),2)?></td>
                        <!-- จ่ายส่วนของโรงพยาบาล -->
                        <td class="text-end fw-bolder"><?= number_format(($item['price_out'] ?? 0),2) ?></td>
                        <!-- รวม -->
                        <td class="text-end fw-bolder"><?=number_format($sumPriceOut,2)?></td>
                        <!-- ยอดยกไป -->
                        <td class="text-end fw-bolder"><?= number_format(($item['end_price'] ?? 0),2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-warning">
                        <td></td>
                        <td class="text-center">รวม</td>
                        <td class="text-end fw-bolder"><?=number_format($totalPriceBegin,2)?></td>
                        <td class="text-end fw-bolder"><?=number_format($totalPriceIn,2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($totalPriceBegin+$totalPriceIn),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($totalPriceBranch),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($totalPriceSub),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($totalPriceSubBranch),2)?></td>
                        <td class="text-end fw-bolder"><?=number_format(($totalPriceEnd),2)?></td>
                    </tr>

            </tbody>

        </table>


    </div>
    <div class="card-footer d-flex justify-content-end">
        <button id="download-button" class="btn btn-primary shadow">ดาวน์โหลดรายงาน</button>
    </div>
</div>


<?php
$url = Url::to(array_merge(
    ['/inventory/report/export-excel'],
    Yii::$app->request->queryParams
));
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