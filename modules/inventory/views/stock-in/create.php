<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockIn $model */

$this->title = 'Create Stock In';
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-in-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
