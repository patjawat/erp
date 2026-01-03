<?php

use yii\web\View;
use yii\helpers\Url;

$this->title = 'สรุปรายงานวัสดุคงคลัง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu_dashbroad',['active' => 'report']) ?>
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
$sumPriceBegin = 0; //สินค้าคงเหลือ
$sumPriceIn = 0; //ซื้อระหว่างเดือน
$sumPriceBranch = 0; //จ่ายส่วนของ รพ.สต.
$sumPriceSub = 0; //จ่ายส่วนของโรงพยาบาล
$sumPriceSubBranch = 0; //สินค้าที่ใช้ไป
$sumPriceEnd = 0; //ยอดยกไป

// ฟังก์ชันตัดทศนิยม 2 ตำแหน่ง
function trunc2($v) {
    return floor($v * 100) / 100;
}

foreach ($querys as $item):

    $begin   = $item['begin_price'] ?? 0;
    $in      = $item['price_in'] ?? 0;
    $totalPriceBegin = $item['total_price_begin'];
    $branch  = $item['branch_price_out'] ?? 0;
    $sub     = $item['price_out'] ?? 0;
    $totalPriceOut = $item['total_price_out'];
    $end     = $item['end_price'] ?? 0;

    // รวม OUT (แก้ลำดับเครื่องหมาย)

?>

<tr>
    <td class="text-center"><?= $num++; ?></td>

    <td>(<?= $item['asset_type_code'] ?>)<?= $item['asset_type_name'] ?></td>

    <td class="text-end fw-bolder"><?= number_format($begin, 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($in, 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($totalPriceBegin, 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($branch, 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sub, 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($totalPriceOut, 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($end, 5) ?></td>
</tr>

<?php endforeach; ?>

<tr class="table-warning">
    <td></td>
    <td class="text-center">รวม</td>

    <td class="text-end fw-bolder"><?= number_format($sum['begin_price'], 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sum['price_in'], 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sum['total_price_begin'], 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sum['branch_price_out'], 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sum['price_out'], 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sum['total_price_out'], 5) ?></td>

    <td class="text-end fw-bolder"><?= number_format($sum['end_price'], 5) ?></td>
</tr>

</tbody>


        </table>


    </div>
    <div class="card-footer d-flex justify-content-end">
      
    </div>
</div>


<?php
$url = Url::to(array_merge(
    ['/inventory/report/export-excel'],
    Yii::$app->request->queryParams
));
$js = <<< JS
    \$("body").on("click", "#download-button", function (e) {
         e.preventDefault();
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