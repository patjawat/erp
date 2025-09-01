<?php

use yii\helpers\Html;
use yii\web\View;

use yii\widgets\DetailView;

/** @var yii\web\View$(this) */
/** @var app\modules\plan\models\PlanOrder $model */

$this->title = 'แผนคำขอพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'Plan Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-dolly me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'parcel']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between">
            <h6><i class="fa-solid fa-eye"></i> แสดงรายละเอียดคำขอ</h6>
            <p>
                <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= ($model->status == 'draft' || $model->status == 'renew')
                    ? Html::a(
                        '<i class="fa-solid fa-paper-plane"></i> ส่งคำขอ',
                        ['/plan/plan-order/update-status'],
                        [
                            'class' => 'btn btn-warning update-status',
                            'data' => ['id' => $model->id]
                        ]
                    )
                    : ''
                ?>

                <?= $model->status == 'submit' ?  Html::a('<i class="fa-solid fa-circle-check"></i> อนุมัติแผน', ['/plan/plan-order/approve', 'id' => $model->id], ['class' => 'btn btn-success open-modal', 'data' => ['size' => 'modal-m']]) : '' ?>
                <?= $model->status == 'approve' ?  Html::a('<i class="fa-solid fa-arrow-rotate-left"></i> ปรับแผน', ['/plan/plan-order/renew'], ['class' => 'btn btn-warning renew', 'data' => ['id' => $model->id]]) : '' ?>
                <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
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
                    'label' => 'ประเภทงบประมาณ',
                ],
                [
                    'attribute' => 'asset_group_id',
                    'label' => 'หมวด',
                    'value' => function ($model) {
                        return $model->planType?->title ?? '-';
                    }
                ],
                [
                    'attribute' => 'asset_type_id',
                    'label' => 'ประเภท',
                    'value' => function ($model) {
                        return $model->assetType?->title ?? '-';
                    }
                ],
                [
                    'attribute' => 'asset_category_id',
                    'label' => 'หมวดพัสดุ',
                    'value' => function ($model) {
                        return $model->assetCategory?->title ?? '-';
                    }
                ],
                [
                    'attribute' => 'department_id',
                    'label' => 'ของกลุ่มงาน',
                    'value' => function ($model) {
                        return $model->departmentName();
                    }
                ],
                [
                    'attribute' => 'budget_id',
                    'label' => 'แหล่งของเงิน',
                ],
                [
                    'attribute' => 'order_price',
                    'label' => 'ยอดเงินทั้งสิ้น',
                ],

            ],
        ]) ?>

    </div>
</div>


<div class="card">
    <div class="card-body">
        <h6>รายการครุภัณฑ์</h6>
        <table class="table table-bordered table-hover table-modal">
            <thead>
                <tr>
                    <th class="text-center">ลำดับ</th>
                    <th class="text-center">ชื่อ</th>
                    <th class="text-center">ราคา/ต่อหน่วย</th>
                    <th class="text-center">จำนวน</th>
                    <th class="text-center">หน่วยนับ</th>
                    <th class="text-center">เป็นเงิน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->getPlanItems()->all() as $item): ?>
                    <tr>
                        <td rowspan="2" class="text-center">1</td>
                        <td><?= $item->item_name ?></td>
                        <td class="text-end"><?= $item->unit_price ?></td>
                        <td class="text-end"><?= $item->qty ?></td>
                        <td>เครื่อง</td>
                        <td class="text-end"><?= ($item->qty * $item->unit_price) ?></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>


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

$('.renew').click(function (e) { 
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยันการเปลี่ยนสถานะ?',
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