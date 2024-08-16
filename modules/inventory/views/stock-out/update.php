<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockOut $model */

$this->title = 'Update Stock Out: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Outs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-out-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
