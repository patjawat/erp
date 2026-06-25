<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListVendor $model */

$this->title = 'เพิ่มผู้ขาย/ผู้จำหน่าย/ผู้บริจาค';
$this->params['breadcrumbs'][] = ['label' => 'ผู้ขาย/ผู้จำหน่าย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-vendor-create">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
