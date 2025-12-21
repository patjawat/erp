<?php

/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $type string */

$this->title = 'แก้ไขข้อมูล: ' . $model->name;
?>

<?= $this->render('_form', [
    'model' => $model,
    'type' => $type,
]) ?>