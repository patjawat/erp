<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListVendor $model */

$this->title = 'แก้ไขผู้ขาย/ผู้จำหน่าย: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ผู้ขาย/ผู้จำหน่าย', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="list-vendor-update">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
