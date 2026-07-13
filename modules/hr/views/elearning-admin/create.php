<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningCourse $model */

$this->title = 'เพิ่มหลักสูตรใหม่';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = ['label' => 'จัดการ E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="elearning-course-create">
    <?php $this->beginBlock('page-title'); ?>
    <?= Html::encode($this->title) ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
