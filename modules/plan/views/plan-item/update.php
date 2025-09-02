<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanItem $model */

$this->title = 'Update Plan Item: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Plan Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="plan-item-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
