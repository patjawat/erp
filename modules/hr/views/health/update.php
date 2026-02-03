<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */

$this->title = 'Update Employee Detail: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Employee Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="employee-detail-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
