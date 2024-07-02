<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Warehouse $model */

$this->title = 'Update Warehouse: ' . $model->warehouse_id;
$this->params['breadcrumbs'][] = ['label' => 'Warehouses', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->warehouse_id, 'url' => ['view', 'warehouse_id' => $model->warehouse_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="warehouse-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
