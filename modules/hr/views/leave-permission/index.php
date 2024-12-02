<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\LeavePermission;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePermissionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Leave Permissions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-permission-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Leave Permission', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cal leave', ['/hr/leave-permission/view-cal-leave'], ['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-md']]) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'emp_id',
            'service_time:datetime',
            'leave_over',
            'leave_over_before',
            //'year_of_service',
            //'position_type_id',
            //'leave_type_id',
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
                'urlCreator' => function ($action, LeavePermission $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>