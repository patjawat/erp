<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\SupVendor $model */

$this->title = 'เพิ่มรายการ';
$this->params['breadcrumbs'][] = ['label' => 'ผู้แทนจำหน่าย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<div class="asset-index">
<div class="sup-vendor-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
