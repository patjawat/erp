<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\AppHelper;

$this->title = 'ทะเบียนที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-map fs-3"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">


    <div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-info-circle"></i> ข้อมูลทั่วไป
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i> จัดการ
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li> <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-triangle-exclamation me-2"></i> แจ้งซ่อม', ['/me/repair-v2/create', 'asset_number' => $model->code, 'send_type' => 'asset', 'container' => 'ma-container', 'title' => '<i class="fa-solid fa-circle-info fs-3"></i>  ส่งซ่อม'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-qrcode me-2"></i> QR-Code', ['qrcode', 'id' => $model->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-chart-line me-2"></i> ค่าเสื่อม', ['depreciation', 'id' => $model->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                    <li><?= Html::a('<i class="fa-solid fa-trash me-2"></i> ลบ', ['delete', 'id' => $model->id], ['class' => 'dropdown-item delete-asset']) ?></li>
                </ul>
            </div>

        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">ชื่อครุภัณฑ์:</div>
            <div class="col-md-8"><?= $model->assetItem?->title ?? '-'; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">รหัสครุภัณฑ์:</div>
            <div class="col-md-8"><?= $model->code ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">หมวดหมู่:</div>
            <div class="col-md-8">ครุภัณฑ์คอมพิวเตอร์</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">ประเภท:</div>
            <div class="col-md-8"><?= $model->AssetTypeName() ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">ยี่ห้อ/รุ่น:</div>
            <div class="col-md-8">
                <?= $model->data_json['band'] ?? 'ไม่ระบุ' ?>/<?= $model->data_json['model'] ?? 'ไม่ระบุ' ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">หน่วยงานที่รับผิดชอบ:</div>
            <div class="col-md-8">ฝ่ายเทคโนโลยีสารสนเทศ</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">สถานที่ตั้ง:</div>
            <div class="col-md-8"><?= isset($model->data_json['location']) ? $model->data_json['location'] : '-' ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-bold">สถานะ:</div>
            <div class="col-md-8"><?= $model->statusName() ?></div>
        </div>
    </div>
</div>

    </div>
</div>

