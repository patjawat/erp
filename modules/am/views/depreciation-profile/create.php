<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\DepreciationProfile $model */

$this->title = 'เพิ่มเกณฑ์ค่าเสื่อม';
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = ['label' => 'เกณฑ์ค่าเสื่อม', 'url' => ['/am/depreciation-profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="plus"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">
    <div class="card"><div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div></div>
</div>
