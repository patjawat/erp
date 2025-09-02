<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanType $model */

$this->title = 'Create Plan Type';
$this->params['breadcrumbs'][] = ['label' => 'Plan Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
