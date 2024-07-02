<?php

use app\modules\warehouse\models\Warehouse;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\WarehouseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Warehouses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warehouse-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Warehouse', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'warehouse_id',
            'warehouse_name',
            'warehouse_code',
            'is_main',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Warehouse $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'warehouse_id' => $model->warehouse_id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
