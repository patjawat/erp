<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Store $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="store-view">
    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], [
            'class' => 'btn btn-warning open-modal',
            'data' => ['size' => 'modal-md']
        ]) ?>
        <?= Html::a('ลบทิ้ง', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger delete-item',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'active',
        ],
    ]) ?>

</div>
