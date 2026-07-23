<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\DepreciationProfile $model */

$this->title = 'แก้ไขเกณฑ์: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = ['label' => 'เกณฑ์ค่าเสื่อม', 'url' => ['/am/depreciation-profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="pencil"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">
    <div class="alert alert-info small">
        <i data-lucide="info"></i> การแก้เกณฑ์นี้จะไม่เปลี่ยน snapshot ของทรัพย์สินที่ขึ้นทะเบียนไปแล้ว หากต้องการเกณฑ์ใหม่ที่ต่างจากเดิมอย่างมีนัยสำคัญ แนะนำให้สร้างเกณฑ์ใหม่แทน
    </div>
    <div class="card"><div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div></div>
</div>
