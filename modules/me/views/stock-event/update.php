<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'Update Stock Event: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-event-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
