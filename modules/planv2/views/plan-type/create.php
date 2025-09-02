<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanType $model */

$this->title = 'Create Plan Type';
$this->params['breadcrumbs'][] = ['label' => 'Plan Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
