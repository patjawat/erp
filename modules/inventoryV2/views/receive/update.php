<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockOrder $model */

$this->title = 'Update Stock Order: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Stock Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-order-update">
    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'listWarehouse' => $listWarehouse,
        'listItemType' => $listItemType ?? [],
        'listVendors' => $listVendors ?? [],
    ]) ?>

</div>
