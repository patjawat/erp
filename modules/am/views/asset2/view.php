<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="asset-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'ref',
            'asset_group',
            'asset_name',
            'asset_item',
            'code',
            'fsn_number',
            'qty',
            'receive_date',
            'price',
            'purchase',
            'department',
            'owner',
            'life',
            'on_year',
            'dep_id',
            'depre_type',
            'budget_year',
            'asset_status',
            'data_json',
            'device_items',
            'updated_at',
            'created_at',
            'created_by',
            'updated_by',
            'deleted_at',
            'deleted_by',
            'license_plate',
            'car_type',
        ],
    ]) ?>

</div>
