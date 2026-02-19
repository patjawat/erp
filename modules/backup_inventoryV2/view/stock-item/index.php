<?php

use app\modules\inventoryV2\models\StockItem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Items';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-item-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Stock Item', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'item_code',
            'item_name',
            'category_id',
            'min_qty',
            //'max_qty',
            //'is_asset',
            //'is_innovation',
            //'is_active',
            //'ref',
            //'data_json',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, StockItem $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
