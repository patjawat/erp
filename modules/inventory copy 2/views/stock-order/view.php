<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockOrder $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="stock-order-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'stock_order_id' => $model->stock_order_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'stock_order_id' => $model->stock_order_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'stock_order_id',
            'name',
            'po_number',
            'rc_number',
            'product_id',
            'from_warehouse_id',
            'to_warehouse_id',
            'qty',
            'movement_type',
            'movement_date',
            'lot_number',
            'expiry_date',
        ],
    ]) ?>

</div>
