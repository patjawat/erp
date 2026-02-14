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

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
