<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockOrder $model */

$this->title = 'Create Stock Order';
$this->params['breadcrumbs'][] = ['label' => 'Stock Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-order-create">
    <?= $this->render('_form', [
        'model' => $model,
        'listWarehouse' => $listWarehouse,
        'listItemType' => $listItemType ?? [],
        'items' => $items ?? [],
    ]) ?>

</div>
