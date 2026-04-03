<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="asset-item-view">

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
            'asset_group_id',
            'asset_type_id',
            'asset_category_id',
            'title:ntext',
            'fsn',
            'description:ntext',
            'price',
            'depreciation',
            'service_life',
            'status',
            'data_json',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'deleted_at',
            'deleted_by',
        ],
    ]) ?>

</div>
