<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockIn $model */

$this->title = 'Update Stock In: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-in-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
