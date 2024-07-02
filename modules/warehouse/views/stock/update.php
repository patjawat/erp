<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Stock $model */

$this->title = 'Update Stock: ' . $model->stock_id;
$this->params['breadcrumbs'][] = ['label' => 'Stocks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->stock_id, 'url' => ['view', 'stock_id' => $model->stock_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
