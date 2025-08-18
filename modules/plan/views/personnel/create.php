<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */

$this->title = 'แผนคำขอบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'Plan Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-user-plus me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>


        <?= $this->render('_form',  ['model' => $model, 'items' => $items]) ?>
