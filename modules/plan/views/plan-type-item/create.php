<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanTypeItem $model */

$this->title = 'Create Plan Type Item';
$this->params['breadcrumbs'][] = ['label' => 'Plan Type Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-type-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
