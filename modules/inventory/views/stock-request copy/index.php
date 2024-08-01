<?php

use app\modules\inventory\models\Stock;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'เบิกสินค้า';
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
<div class="stock-movement-index">
<div class="card">
    <div class="card-body">        
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เบิกวัสดุ', ['/inventory/stock-request/create', 'receive_type' => 'normal', 'title' => '<i class="fa-solid fa-cubes-stacked"></i> ขอเบิกวัสดุ'], ['id' => 'btn-add1', 'class' => 'btn btn-success open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    </div>
</div>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'rq_number',
            'rc_number',
            'po_number',
            //'product_id',
            //'from_warehouse_id',
            //'to_warehouse_id',
            //'qty',
            //'total_price',
            //'unit_price',
            //'movement_type',
            //'receive_type',
            //'movement_date',
            //'lot_number',
            //'expiry_date',
            //'category_id',
            //'order_status',
            //'item_status',
            //'ref',
            //'data_json',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Stock $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
