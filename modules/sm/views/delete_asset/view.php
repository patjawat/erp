<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="asset-view">

<div class="card">
    <div class="card-body d-flex ">
        <h1 class="card-title"><?= Html::encode($this->title) ?></h1>

    </div>
</div>

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
            'name',
            'qty',
            'fsn',
            'fsn_number',
            'receive_date',
            'price',
            'life',
            'dep_id',
            'depre_type',
            'budget_year',
            'data_json["name"]',
            'data_json["detail"]',
            'updated_at',
            'created_at',
            'created_by',
            'updated_by',
        ],
    ]) ?>

</div>
