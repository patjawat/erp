<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanCategory $model */

$this->title = 'Update Plan Category: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Plan Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="plan-category-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
