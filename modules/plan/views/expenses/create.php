<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */

$this->title = 'Create Plan Order';
$this->params['breadcrumbs'][] = ['label' => 'Plan Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
