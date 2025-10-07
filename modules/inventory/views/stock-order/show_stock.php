<?php

use yii\web\View;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;
use app\components\ThaiDateHelper;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;

$warehouse = Yii::$app->session->get('warehouse');

//ตรวจสอบดู lot ในใบ order
$order = StockEvent::find()
    ->where(['category_id' => $category_id])
    ->all();
// แปลง Lot number เป็น Array
$lots = ArrayHelper::getColumn($order, 'lot_number');


$stockEvents = Stock::find()
    ->andWhere([
        'asset_item' => $asset_item,
        'warehouse_id' => $warehouse->id,
    ])
    ->andWhere(['not in', 'lot_number', $lots])   // ❌ ไม่เอา lot ที่อยู่ใน $lots
    ->andWhere(['>', 'qty', 0]);

$stockEvents = $stockEvents->all();

$balance = 0;
$balanceQty = 0;

?>

<?php if (count($stockEvents) > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th class="fw-semibold" scope="col" style="width:130px">วันที่รับเข้า</th>
                <th class="fw-semibold" scope="col" style="width:130px">หมายเลขล็อต</th>
                <th class="fw-semibold text-center">คงเหลือ</th>
                <th class="fw-semibold text-center">ราคาต่อหน่วย</th>
                <th class="fw-semibold text-center">จัดการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
        <tbody class="align-middle table-group-divider">
            <?php foreach ($stockEvents as $key => $item2): ?>
                <!-- ถ้า lot_number ตรงกัน -->
                <?php if ($item2->lot_number == $lot_number): ?>
                    <!-- จำนวนที่มีน้อยกว่าจำนวนที่เบิกไม่สารมรถเบิกได้ -->
                    <?php if ($item2->qty < $qty): ?>
                        <tr>
                            <td></td>
                            <td><?= $item2->lot_number ?></td>
                            <td class="fw-semibold text-center"><?= $item2->qty ?></td>
                            <td class="text-center"></td>
                        </tr>
                    <?php endif ?>
                <?php else: ?>
                      <!-- จำนวนที่มีน้อยกว่าจำนวนที่เบิกให้เพิ่ม Lot number ใหม่ได้ -->
                    <tr>
 
                        <td><?= ThaiDateHelper::formatThaiDate($item2->getLotDate()['movement_date'] ?? '') ?></td>
                        <td><?= $item2->lot_number ?></td>
                        <td class="fw-semibold text-center"><?= $item2->qty ?></td>
                        <td class="fw-semibold text-center"><?= $item2->unit_price ?></td>
                        <td class="text-center">

                            <?= $key == 0 && $new_lotnumber == 'Y' ? Html::a(
                                '<i class="fa-solid fa-circle-plus"></i> เพิ่มล๊อตจ่าย',
                                [
                                    '/inventory/stock-order/add-new-lot',
                                    'category_id' => $category_id,
                                    'lot_number' => $item2->lot_number,
                                    'asset_item' => $item2->asset_item,
                                    'unit_price' => $item2->unit_price,
                                ],
                                [
                                    'class' => 'btn btn-sm btn-primary add-new-lot'
                                ]
                            ) : '' ?>

                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach; ?>
        </tbody>

        </tbody>
    </table>
<?php else: ?>
    <h3 class="text-center">หมด</h3>
<?php endif; ?>
<?php
$js = <<< JS

// $('body').on('click', '.add-item-lot', function(e) {
$('body').on('click', '.add-new-lot', function(e) {
    e.preventDefault();
    let id = $(this).attr('data-id');
    let category_id = $(this).attr('data-category-id');
    let url = $(this).attr('href');
    Swal.fire({
        title: 'ยืนยันการเพิ่มสินค้า?',
        text: "คุณต้องการเพิ่มสินค้านี้ลงในตะกร้าหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เพิ่มเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
                       // 🔹 แสดง loading ก่อนยิง ajax
            Swal.fire({
                title: 'กำลังเพิ่มสินค้า...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                type: "get",
                url: url,
                dataType: "json",
                success: function(res) {
                    if (res.status == 'success') {
                        // 1. ปิด modal
                        $('#main-modal').hide();

                        // 2. แสดง Swal 1 วินาที
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'สินค้าได้ถูกเพิ่มลงในตะกร้าแล้ว.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1000
                        }).then(() => {
                            // 3. reload หน้า
                            window.location.reload(true);
                        });
                    }
                },
                error: function(data) {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเพิ่มสินค้าได้.',
                        'error'
                    );
                    console.log(data);
                }
            });
        }
    });
});

JS;
$this->registerJS($js, View::POS_END);

?>