<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanGroup $model */

$this->title = 'Create Plan Group';
$this->params['breadcrumbs'][] = ['label' => 'Plan Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
