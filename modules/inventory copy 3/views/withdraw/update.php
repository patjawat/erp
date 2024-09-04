<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */

$this->title = 'Update Stock Movement: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Movements', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="stock-movement-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
