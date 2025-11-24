<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use app\components\AppHelper;

$this->title = 'สรุปรายงานวัสดุคงคลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('list_by_order_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทั้งหมด
                <span class="badge text-bg-light">
                    <?= number_format(count($querys)) ?></span> รายการ
            </h6>
            <button id="download-button" class="btn btn-success shadow">Excel</button>
<?php //  Html::a('Excel', ['/inventory/report/list-by-order', 'export' => 1] + Yii::$app->request->queryParams, ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="card-body p-0"> <!-- ลบ padding เพื่อให้ scroll เต็มพื้นที่ -->
        <!-- ✅ เพิ่มส่วนนี้ -->
        <div class="table-responsive" style="max-height: 600px;max-height: 600px; overflow: auto;">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-primary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th scope="col" class="text-center fw-bold">ลำดับ</th>
                        <th scope="col" class="text-start fw-bold">ชื่อคลัง</th>
                        <th scope="col" class="text-start fw-bold">ประเภทคลัง</th>
                        <th scope="col" class="text-start fw-bold">ประเภทวัสดุ</th>
                        <th scope="col" class="text-start fw-bold">
                            <?php if ($searchModel->transaction_type == 'IN'): ?>
                                ผู้ขาย
                            <?php elseif ($searchModel->transaction_type == 'OUT'): ?>
                                คลังที่ขอเบิก
                            <?php else: ?>
                                ผู้ขาย/คลังที่ขอเบิก
                            <?php endif; ?>
                        </th>
                        <th scope="col" class="text-center fw-bold">วันที่</th>
                        <th scope="col" class="text-start fw-bold">เลขที่</th>
                        <th scope="col" class="text-center fw-bold">ความเคลื่อนไหว</th>
                        <th scope="col" class="text-start fw-bold">รหัสวัสดุ</th>
                        <th scope="col" class="text-start fw-bold">ชื่อวัสดุ</th>
                        <th scope="col" class="text-center fw-bold">หน่วย</th>
                        <th scope="col" class="text-center fw-bold">จำนวน</th>
                        <th scope="col" class="text-end fw-bold">ราคาต่อหน่วย</th>
                        <th scope="col" class="text-end fw-bold">รวมราคา</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalPrice = 0;
                    $n = 1; ?>
                    <?php foreach ($querys as $item): ?>
                        <tr>
                            <td class="text-center"><?= $n++ ?></td>
                            <td><?= $item['warehouse_name'] ?></td>
                            <td>
                                <?php
                                if ($item['warehouse_type'] == 'MAIN') echo 'คลังหลัก';
                                elseif ($item['warehouse_type'] == 'SUB') echo 'คลังย่อย';
                                elseif ($item['warehouse_type'] == 'BRANCH') echo 'คลังรพ.สต.';
                                else echo '-';
                                ?>
                            </td>
                            <td><?= $item['asset_type_name'] ?></td>
                            <td><?= $item['transaction_type'] == 'IN' ? $item['vendor_name'] : $item['form_warehouse_name'] ?></td>
                            <td><?= AppHelper::convertToThai($item['movement_date']); ?></td>
                            <td><?= $item['code'] ?></td>
                            <td class="text-center"><?= $item['transaction_type'] == 'IN' ? 'รับเข้า' : 'จ่ายออก' ?></td>
                            <td><?= $item['asset_item'] ?></td>
                            <td><?= $item['asset_name'] ?></td>
                            <td class="text-center"><?= $item['unit'] ?></td>
                            <td class="text-center fw-bold"><?= $item['item_qty'] ?></td>
                            <td class="text-end fw-bold"><?= number_format($item['unit_price'] ?? 0, 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format(($item['end_price']) ?? 0, 2) ?></td>
                        </tr>
                        <?php $totalPrice += ($item['end_price']); ?>
                    <?php endforeach; ?>
                </tbody>
                <!-- ✅ ย้ายรวมราคามาใส่ใน <tfoot> เพื่อ fix footer -->
                <tfoot class="table-warning" style="position: sticky; bottom: 0; background-color: #ffeb3b; z-index: 9;">
                    <tr class="fw-bold">
                        <td colspan="13" class="fw-bold text-end">รวมราคาทั้งหมด</td>
                        <td class="text-end fw-bold"><?= number_format($totalPrice, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


<?php
$url = Url::to(array_merge(
    ['/inventory/report/list-by-order', 'export' => 1],
    Yii::$app->request->queryParams
));
$js = <<< JS
        $("body").on("click", "#download-button", function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'ยืนยันการดาวน์โหลด?',
                text: 'คุณต้องการดาวน์โหลดรายงานวัสดุรับ-จ่าย ใช่หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ดาวน์โหลด',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // แสดง loading ระหว่างรอดาวน์โหลด
                    Swal.fire({
                        title: 'กำลังดาวน์โหลด...',
                        text: 'กรุณารอสักครู่ ระบบกำลังประมวลผลไฟล์',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '$url', // URL ไปยัง action export Excel
                        method: 'GET',
                        xhrFields: {
                            responseType: 'blob' // สำหรับรับ binary data
                        },
                        success: function (data) {
                            const monthName = $('#stockeventsearch-receive_month').find(':selected').text();
                            const filename = 'รายงานวัสดุรับ-จ่าย' + '.xlsx';
                            const blob = new Blob([data], {
                                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            });

                            const link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = filename;
                            link.click();

                            // ปิด loading + แสดง success
                            Swal.fire({
                                icon: 'success',
                                title: 'ดาวน์โหลดสำเร็จ',
                                text: 'ไฟล์ถูกดาวน์โหลดเรียบร้อยแล้ว',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: 'ไม่สามารถดาวน์โหลดไฟล์ได้',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    });
                }
            });
        });

    JS;
$this->registerJS($js, View::POS_END);
?>