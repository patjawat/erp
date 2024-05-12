<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'เพิ่มทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'จัดการทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนทรัพย์สิน', 'url' => ['/am/asset']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

    <?= $this->render('_form', [
        'model' => $model,
        
    ]) ?>

