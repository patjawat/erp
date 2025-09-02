<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanItem $model */

$this->title = 'Create Plan Item';
$this->params['breadcrumbs'][] = ['label' => 'Plan Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
