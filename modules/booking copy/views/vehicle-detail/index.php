<?php

use app\modules\booking\models\VehicleDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Vehicle Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vehicle-detail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Vehicle Detail', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'vehicle_id',
            'ref',
            'mileage_start',
            'mileage_end',
            //'distance_km',
            //'oil_price',
            //'oil_liter',
            //'license_plate',
            //'status',
            //'date_start',
            //'time_start',
            //'date_end',
            //'time_end',
            //'driver_id',
            //'data_json',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            //'deleted_at',
            //'deleted_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, VehicleDetail $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
