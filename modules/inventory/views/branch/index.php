<?php

use app\modules\inventory\models\Store;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StoreSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'คลัง';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>



<div class="store-index">

<div class="card">
    <div class="card-body">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/inventory/branch/create', 'title' => '<i class="fa-solid fa-cubes-stacked"></i> สร้างคลัง'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        

    <?php Pjax::begin(['id' => 'warehouse-container']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'title',
                'header' => 'ชื่อคลัง',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->title, ['/inventory/branch/view', 'id' => $model->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-md']]);
                }
            ]
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    </div>
</div>

</div>
