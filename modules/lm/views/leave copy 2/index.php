<?php

use app\modules\lm\models\Leave;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบการลา';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="card text-start">
    <div class="card-body">

    <p>
        <?= Html::a('สร้างใหม่', ['type-select'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'leave_type_id',
            'leave_time_type',
            //'start_end',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            //'deleted_at',
            //'deleted_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Leave $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>



    </div>
</div>
