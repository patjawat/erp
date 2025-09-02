<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanType $model */

$this->title = 'Update Plan Type: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Plan Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="plan-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
