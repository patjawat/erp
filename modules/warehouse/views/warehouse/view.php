<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Warehouse $model */

$this->title = $model->warehouse_id;
$this->params['breadcrumbs'][] = ['label' => 'Warehouses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="warehouse-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'warehouse_id' => $model->warehouse_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'warehouse_id' => $model->warehouse_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'warehouse_id',
            'warehouse_name',
            'warehouse_code',
            'is_main',
        ],
    ]) ?>

</div>
