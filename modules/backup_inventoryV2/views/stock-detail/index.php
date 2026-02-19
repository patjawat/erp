<?php

use app\modules\inventoryV2\models\StockDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-detail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Stock Detail', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'stock_order_id',
            'item_id',
            'qty',
            'unit_price',
            //'lot_number',
            //'expiry_date',
            //'ref',
            //'data_json',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, StockDetail $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
