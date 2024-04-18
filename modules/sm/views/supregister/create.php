<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Supregister $model */


$this->title = 'เพิ่มรายการ';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<div class="asset-index">
<div class="supregister-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
