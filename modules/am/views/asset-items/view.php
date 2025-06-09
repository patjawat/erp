<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>



<div class="text-center mb-3">
    <h4 id="viewAssetName">โต๊ะทำงานเหล็ก ขนาด 5 ฟุต</h4>
  <!-- <span class="badge text-bg-success">ใช้งาน</span> -->
</div>
<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-4 fw-semibold">รหัส FSN:</div>
            <div class="col-md-8 fw-semibold" id="viewAssetFSN"><?=$model->code?></div>
        </div>
        <div class="row">
            <div class="col-md-4 fw-bold">ประเภททรัพย์สิน:</div>
            <div class="col-md-8"><?=$model->assetType?->title ?? '-'?></div>
        </div>
    </div>
</div>


<div class="d-flex justify-content-center">
    <p>
        <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขรหัสทรัพย์สิน'], ['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>
                      
        <?= Html::a('<i class="fa-solid fa-trash"></i> ลบทิ้ง', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

</div>
