<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whcheck $model */


$this->title = 'เพิ่มรายการ';
$this->params['breadcrumbs'][] = ['label' => 'ตรวจรับ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<div class="whcheck-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
