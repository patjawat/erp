<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockOut $model */

$this->title = 'Create Stock Out';
$this->params['breadcrumbs'][] = ['label' => 'Stock Outs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-out-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
