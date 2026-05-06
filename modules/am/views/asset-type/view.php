<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ประเภททรัพย์สิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$dataJson = is_array($model->data_json)
    ? $model->data_json
    : (is_string($model->data_json) ? (json_decode($model->data_json, true) ?: []) : []);
$description = $dataJson['description'] ?? ($model->description ?? '-');
$titleEn = $dataJson['title_en'] ?? '-';
$statusText = !empty($model->active) ? 'ใช้งาน' : 'ไม่ใช้งาน';
$statusClass = !empty($model->active) ? 'success' : 'danger';
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4 fw-bold">รหัส:</div>
            <div class="col-md-8"><?= Html::encode($model->code ?? '-') ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4 fw-bold">ชื่อรายการ:</div>
            <div class="col-md-8"><?= Html::encode($model->title ?? '-') ?></div>
        </div>

        <div class="row">
            <div class="col-md-4 fw-bold">สถานะ:</div>
            <div class="col-md-8">
                <span class="badge text-bg-<?= $statusClass ?>"><?= Html::encode($statusText) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center gap-2">
    <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขประเภททรัพย์สิน'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    <?= Html::a('<i class="fa-solid fa-trash"></i> ลบทิ้ง', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => 'Are you sure you want to delete this item?',
            'method' => 'post',
        ],
    ]) ?>
</div>
