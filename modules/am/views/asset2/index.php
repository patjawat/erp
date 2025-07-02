<?php

use app\modules\am\models\Asset2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset2Search $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Assets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Asset', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ref',
            'asset_group',
            'asset_name',
            'asset_item',
            //'code',
            //'fsn_number',
            //'qty',
            //'receive_date',
            //'price',
            //'purchase',
            //'department',
            //'owner',
            //'life',
            //'on_year',
            //'dep_id',
            //'depre_type',
            //'budget_year',
            //'asset_status',
            //'data_json',
            //'device_items',
            //'updated_at',
            //'created_at',
            //'created_by',
            //'updated_by',
            //'deleted_at',
            //'deleted_by',
            //'license_plate',
            //'car_type',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Asset2 $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
