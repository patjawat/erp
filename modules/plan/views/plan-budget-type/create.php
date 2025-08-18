<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanBudgetType $model */

$this->title = 'Create Plan Budget Type';
$this->params['breadcrumbs'][] = ['label' => 'Plan Budget Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-budget-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
