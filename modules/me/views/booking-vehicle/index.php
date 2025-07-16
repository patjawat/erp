<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use app\models\Categorise;
use yii\grid\ActionColumn;
use app\modules\booking\models\Vehicle;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบขอใช้ยานพาหนะ/ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-1 white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียนขอใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me//menu', ['active' => 'vehicle']) ?>
<?php $this->endBlock(); ?>

<?php //  $this->render('@app/modules/booking/views/vehicle/summary', ['model' => $searchModel]) ?>

<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center">
        <?= Html::a(
            '<i class="bi bi-plus-circle me-1"></i>สร้างคำขอใหม่',
            ['/me/booking-vehicle/create', 'title' => 'แบบขอใช้รถยนต์'],
            [
                'class' => 'btn btn-primary open-modal rounded-pill shadow',
                'data' => ['size' => 'modal-lg']
            ]
        ) ?>
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?> <span
                        class="badge rounded-pill text-bg-primary">
                        <?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            </div>

        </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th style="width: 180px;">เลขที่/ขอใช้รถ</th>
                        <th class="text-center">ความเร่งด่วน</th>
                        <th>จุดหมาย/วันที่ขอใช้</th>
                        <th>วัตถุประสงค์/ความเร่งด่วน</th>
                        <th>ผู้ขอ</th>
                        <th>สถานะ</th>
                        <th class="text-center" style="width:150px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?= (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td>
                            <p class="mb-0 fw-semibold"><?= $item->code ?></p>
                            <p class="fs-13 mb-0">
                                <?= $item->viewCarType() ?>
                            </p>
                        </td>
                        <td class="text-center">
     <p class="mb-0 fs-13">
                                    <?= $item->viewUrgent() ?>
                                </p>
</td>
                        <td>
                            <div class="avatar-detail">
                                <h6 class="mb-0 fs-13">
                                    <?= $item->viewGoType() ?> : <?= $item->locationOrg?->title ?? '-' ?>
                                </h6>
                                <p class="text-muted mb-0 fs-13">
                                    <?= $item->showDateRange() ?> เวลา <?= $item->viewTime() ?>
                                </p>
                            </div>
                        </td>

                        <td>
                            <div class="avatar-detail">
                                <h6 class="mb-0 fs-13"><?= $item->reason; ?></h6>
                               
                            </div>
                        </td>
                        <td>
                            <?= $item->userRequest()['avatar'] ?>
                        </td>
                        <td>
                            <?= $item->viewStatus()['view'] ?? '-' ?>
                        </td>
                        <td class="fw-light text-center">
                            <div class="btn-group">
                                <?= Html::a(
                                        '<i class="fa-solid fa-pen-to-square"></i>',
                                        ['/me/booking-vehicle/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขข้มูลขอใช้รถ'],
                                        [
                                            'class' => 'btn btn-light w-100 open-modal',
                                            'data' => ['size' => 'modal-lg']
                                        ]
                                    ) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <?= Html::a(
                                                '<i class="fa-solid fa-eye me-1"></i> แสดงข้อมูล',
                                                ['/me/booking-vehicle/view', 'id' => $item->id, 'title' => 'แสดงข้มูลขอใช้รถ'],
                                                [
                                                    'class' => 'dropdown-item open-modal',
                                                    'data' => ['size' => 'modal-lg']
                                                ]
                                            ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                                '<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอรถยนต์',
                                                ['/booking/vehicle/print', 'id' => $item->id, 'title' => 'แสดงข้มูลขอใช้รถ'],
                                                [
                                                    'class' => 'dropdown-item open-modal',
                                                    'data' => ['size' => 'modal-lg']
                                                ]
                                            ) ?>
                                    </li>
                                    <li>
                                        <?= Html::a(
                                                '<i class="fa-regular fa-trash-can me-1"></i> ลบข้อมูล',
                                                ['/me/booking-vehicle/delete', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> ลบ'],
                                                [
                                                    'class' => 'dropdown-item delete-item',
                                                    'data' => ['size' => 'modal-lg']
                                                ]
                                            ) ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <!--
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-3">
                                    <?= Html::a(
                                        '<i class="fa-solid fa-eye fa-2x"></i>',
                                        ['/me/booking-vehicle/view', 'id' => $item->id, 'title' => 'แสดงข้มูลขอใช้รถ'],
                                        ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]
                                    ) ?>
                                    <?= Html::a(
                                        '<i class="fa-solid fa-pen-to-square fa-2x text-warning"></i>',
                                        ['/me/booking-vehicle/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขข้มูลขอใช้รถ'],
                                        ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]
                                    ) ?>
                                    <?= Html::a(
                                        '<i class="fa-regular fa-trash-can fa-2x text-danger"></i>',
                                        ['/me/booking-vehicle/delete', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> ลบ'],
                                        ['class' => 'delete-item', 'data' => ['size' => 'modal-lg']]
                                    ) ?>
                                </div>
                            </td>
                            -->
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>
<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>