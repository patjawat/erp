<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningCourse $model */

$this->title = 'แก้ไขหลักสูตร: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = ['label' => 'จัดการ E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="elearning-course-update">
    <?php $this->beginBlock('page-title'); ?>
    แก้ไขหลักสูตร
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
