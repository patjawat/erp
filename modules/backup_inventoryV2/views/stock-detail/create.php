<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockDetail $model */

$this->title = 'Create Stock Detail';
$this->params['breadcrumbs'][] = ['label' => 'Stock Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-detail-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
