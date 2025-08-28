<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */

$this->title = 'แผนคำขอค่าใช้สอย';
$this->params['breadcrumbs'][] = ['label' => 'Plan Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-file-invoice me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'expenses']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="fa-solid fa-eye"></i> แสดงรายละเอียดแผนคำขอบุคลากร</h6>
             <?=$this->render('action',['model' => $model])?>
        </div>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                  [
                    'attribute' => 'status',
                    'label' => 'สถานะ',
                    'value' => function ($model) {
                        return $model->viewStatus()['title'];
                    }
                ],
                [
                    'attribute' => 'thai_year',
                    'label' => 'ปีงบประมาณ',
                    'value' => function ($model) {
                        return $model->thai_year;
                    }
                ],
                [
                    'attribute' => 'plan_budget_type',
                    'label' => 'ค่าใช้จ่าย',
                    'value' => function ($model) {
                        return $model->planType->title;
                    }
                ],
                [
                    'attribute' => 'plan_type_item_id',
                    'label' => 'ค่า',
                    'value' => function ($model) {
                        return $model->planTypeItem?->title ?? '-';
                    }
                ],
                [
                    'attribute' => 'department_id',
                    'label' => 'ของกลุ่มงาน',
                    'value' => function ($model) {
                        return $model->departmentName() ??  '-';
                    }
                ],

                [
                    'attribute' => 'description',
                    'label' => 'วัตถุประสงค์',
                ],
                [
                    'attribute' => 'order_price',
                    'label' => 'ยอดเงินทั้งสิ้น',
                ],

            ],
        ]) ?>

    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th class="fw-semibold" scope="col" style="text-align: center;width:100px">ลำดับ</th>
            <th>ชื่อรายการ</th>
            <th width="150">จำนวน</th>
            <th width="200" class="text-end">ราคาต่อหน่วย</th>
            <th width="200" class="text-end">รวม</th>
        </tr>
    </thead>
    <tbody class="table-group-divider align-middle">
        <?php $num = 1;
        foreach ($model->getPlanItems()->all() as $item): ?>
            <tr>
                <td><?= $num++ ?></td>
                <td><?= $item->item_name ?></td>
                <td><?= $item->qty ?></td>
                <td class="text-end fw-semibold"><?= $item->unit_price ?></td>
                <td class="text-end fw-semibold"><?= number_format($item->qty * $item->unit_price, 2) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<div class="card">
    <div class="card-body">
        <h6>แผนการใช้จ่าย</h6>
        <table class="table table-bordered table-hover table-modal">
            <thead>
                <tr>
                    <th colspan="3" class="text-center font-14">ไตรมาส 1</th>
                    <th colspan="3" class="text-center font-14">ไตรมาส 2</th>
                    <th colspan="3" class="text-center font-14">ไตรมาส 3</th>
                    <th colspan="3" class="text-center font-14">ไตรมาส 4</th>
                </tr>
                <tr>
                    <th class="text-center font-14">ต.ค.</th>
                    <th class="text-center font-14">พ.ย.</th>
                    <th class="text-center font-14">ธ.ค.</th>
                    <th class="text-center font-14">ม.ค.</th>
                    <th class="text-center font-14">ก.พ.</th>
                    <th class="text-center font-14">มี.ค.</th>
                    <th class="text-center font-14">เม.ย.</th>
                    <th class="text-center font-14">พ.ค.</th>
                    <th class="text-center font-14">มิ.ย.</th>
                    <th class="text-center font-14">ก.ค.</th>
                    <th class="text-center font-14">ส.ค.</th>
                    <th class="text-center font-14">ก.ย.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_10, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_11, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_12, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_1, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_2, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_3, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_4, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_5, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_6, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_7, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_8, 2) ?></td>
                    <td class="text-end fw-semibold"><?= number_format((float)$model->month_9, 2) ?></td>

                </tr>
            </tbody>
        </table>
    </div>
</div>


<?php
$js = <<< JS

$('.update-status').click(function (e) { 
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยัน?',
        text: "คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะนี้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เปลี่ยนเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url:$(this).attr('href'),
                data: {
                    id:$(this).data('id'),
                    status:$(this).data('status'),
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'อัปเดตสถานะเรียบร้อยแล้ว',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload(); // โหลดใหม่ถ้าต้องการ
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด!',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                    });
                }
            });
        }
    });
});



$('.renew').click(function (e) { 
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยันการปรับแผน?',
        text: "คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะนี้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เปลี่ยนเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url:$(this).attr('href'),
                data: {
                    id:$(this).data('id'),
                    status:$(this).data('status'),
                },
                dataType: "json",
                success: function (response) {
                   if (response.url) {
                        window.location.href = response.url;
                    } else {
                        location.reload();
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด!',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                    });
                }
            });
        }
    });
});



JS;
$this->registerJS($js, View::POS_END);
?>