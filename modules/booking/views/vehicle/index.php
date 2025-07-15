<?php

use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนการขอใช้รถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
    <i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
    ทะเบียนใช้รถยนต์ทั่วไป
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
    <?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('menu', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body d-flex justify-content-center align-items-center">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card shadow-sm">
        <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks me-1"></i> <?= $this->title ?>
                <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ
            </h6>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th>รหัสการจอง</th>
                    <th class="text-center">ความเร่งด่วน</th>
                    <th>รถที่ต้องการ</th>
                    <th>วันที่ใช้</th>
                    <th>จุดหมาย</th>
                    <th>ผู้จอง</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?= (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td><?= $item->code ?></td>
                        <td class="text-center"><?= $item->viewUrgent() ?></td>
                        <td><?=$this->render('caritem',['item' => $item])?></td>
                        <td>
                            <p class="mb-0">
                                <?= $item->showDateRange() ?>
                            </p>
                            <p class="mb-0">
                                <?= $item->viewTime() ?>
                            </p>
                    </td>
                        <td><?= $item->locationOrg?->title ?? '-' ?></td>
                        <td><?= $item->userRequest()['avatar'] ?></td>
                        <td><?= $item->viewStatus()['view'] ?? '-' ?></td>
                        <td class="fw-light text-end">
                            <div class="btn-group">
                                <?= Html::a(
                                    '<i class="fa-solid fa-pen-to-square"></i>',
                                    ['/booking/vehicle/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขข้มูลขอใช้รถ'],
                                    ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-lg']]
                                ) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-user-tag me-1"></i> จัดสรร',
                                            ['/booking/vehicle/approve', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขข้มูลขอใช้รถ'],
                                            ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]
                                        ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอรถยนต์',
                                            ['/booking/vehicle/print', 'id' => $item->id, 'title' => 'ใบขอรถยนต์'],
                                            ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]
                                        ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                            '<i class="fa-regular fa-circle-xmark me-1"></i> ยกเลิก',
                                            ['/booking/vehicle/cancel', 'id' => $item->id],
                                            ['class' => 'dropdown-item cancel-order', 'data' => ['size' => 'modal-lg']]
                                        ) ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="body-footer">
            <div class="d-flex justify-content-center">
                <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'class' => 'pagination pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>


<?php
$js = <<<JS
$(document).on('click', '.cancel-order', function(e) {
    e.preventDefault();
    let url = $(this).attr('href');
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการยกเลิกคำขอนี้หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ยกเลิก!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'POST',
                success: function(response) {
                    Swal.fire(
                        'ยกเลิกสำเร็จ!',
                        'คำขอของคุณถูกยกเลิกแล้ว.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                },
                error: function() {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถยกเลิกคำขอได้.',
                        'error'
                    );
                }
            });
        }
    });
});
JS;
$this->registerJS($js, \yii\web\View::POS_END);
?>