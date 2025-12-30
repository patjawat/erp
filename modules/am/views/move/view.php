<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="row">
    <!-- รายละเอียดครุภัณฑ์ -->
    <div class="col-md-12 border-end">
        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-info-circle me-1"></i> ข้อมูลครุภัณฑ์</h6>
        <div class="asset-preview-box mb-4">
            <div class="row align-items-center">
                <div class="col-auto text-primary">
                    <?= Html::img($model->asset->showImg()['image'], ['class' => 'w-100 h-100 object-fit-cover', 'style' => 'max-width: 76px;']) ?>
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1"><?= $model->asset->asset_name ?? '-' ?></h5>
                    <p class="text-muted mb-0">หมายเลขครุภัณฑ์: <?= $model->asset->code ?? '' ?> | ยี่ห้อ: <?= $model->asset->data_json['brand'] ?? '' ?></p>
                    <p class="text-muted mb-0">สถานะปัจจุบัน: <?= $model->asset->viewstatus() ?></p>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <label class="text-muted d-block">สถานที่ต้นทาง</label>
                <span class="fw-bold"><?= $model->asset->data_json['location'] ?? '-' ?></span>
            </div>
            <div class="col-6 text-primary">
                <label class="text-muted d-block">สถานที่ปลายทาง</label>
                <span class="fw-bold"><i class="bi bi-geo-alt-fill me-1"></i><?= $model->data_json['location'] ?? '-' ?></span>
            </div>
            <div class="col-6 text-muted">
                <label class="text-muted d-block">ผู้แจ้งเคลื่อนย้าย</label>
                <span class="fw-bold"><?= $model->createdBy->employees->fullname ?? '-' ?></span>
            </div>
            <div class="col-6">
                <label class="text-muted d-block">วันที่ต้องการย้าย</label>
                <span class="fw-bold"><?= Yii::$app->thaiDate->toThaiDate($model->date_start, false, false); ?></span>
            </div>
            <div class="col-12">
                <label class="text-muted d-block">เหตุผลการเคลื่อนย้าย</label>
                <div class="bg-light p-3 rounded mt-1">
                    <?= $model->getReasonLabel() ?>
                    <p class="mb-0"><?= $model->data_json['remask'] ?></p>
                </div>
            </div>

            <div class="col-6 text-muted">
                <label class="text-muted d-block">สถานะการอนุมัติของหัวหน้า</label>
                <span class="fw-bold"><?= $model->getLeaderStatusBadge()?></span>
            </div>
            <div class="col-6">
                <label class="text-muted d-block">ความเห็นขอของหัวหน้า</label>
                <span class="fw-bold"><?=$model->data_json['leader_remask'] ?? '-' ?></span>
            </div>



            
        </div>
    </div>
</div>
