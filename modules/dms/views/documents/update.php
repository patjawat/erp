<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = 'แก้ไขหนังสือ: ' . $model->document_type;
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->document_type, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<?= $this->render('_form_'.$model->document_group, [
        'model' => $model
    ]) ?>

