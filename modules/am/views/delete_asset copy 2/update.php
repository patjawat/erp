<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */



$this->title = 'แก้ไขทะเบียนครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'บริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php // Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-pen-to-square"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>


    <?= $this->render('_form', [
        'model' => $model
    ]) ?>
<?php // Pjax::end();?>
