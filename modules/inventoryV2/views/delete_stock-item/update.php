<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockItem $model */

$this->title = 'Update Stock Item: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Stock Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-item-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
