<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanCategory $model */

$this->title = 'Create Plan Category';
$this->params['breadcrumbs'][] = ['label' => 'Plan Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-category-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
