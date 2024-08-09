<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockOrder $model */

$this->title = 'Create Stock Order';
$this->params['breadcrumbs'][] = ['label' => 'Stock Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
