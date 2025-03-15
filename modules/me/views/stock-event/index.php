<?php

use app\modules\inventory\models\StockEvent;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Events';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-event-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Stock Event', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'code',
            'transaction_type',
            'asset_item',
            //'warehouse_id',
            //'vendor_id',
            //'po_number',
            //'from_warehouse_id',
            //'qty',
            //'total_price',
            //'unit_price',
            //'receive_type',
            //'movement_date',
            //'lot_number',
            //'category_id',
            //'order_status',
            //'checker',
            //'ref',
            //'data_json',
            //'thai_year',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            //'deleted_at',
            //'deleted_by',
            //'helpdesk_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, StockEvent $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
