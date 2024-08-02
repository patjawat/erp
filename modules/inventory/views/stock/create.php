<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
$this->title = 'Create Stock Movement';
$this->params['breadcrumbs'][] = ['label' => 'Stock Movements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-movement-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
