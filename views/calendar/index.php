<?php

use app\models\Calendar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\CalendarSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Calendars';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="calendar-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Calendar', ['create'], ['class' => 'btn btn-success']) ?>
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
            'date_start',
            'date_end',
            'status',
            //'data_json',
            //'thai_year',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            //'deleted_at',
            //'deleted_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Calendar $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
