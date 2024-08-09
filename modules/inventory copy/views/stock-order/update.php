<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockOrder $model */

$this->title = 'Update Stock Order: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'stock_order_id' => $model->stock_order_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-order-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
