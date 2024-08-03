<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\PlanDetail $model */

$this->title = 'Create Plan Detail';
$this->params['breadcrumbs'][] = ['label' => 'Plan Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
