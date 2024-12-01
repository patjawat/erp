<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\LeaveRole;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveRoleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Leave Roles';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leave-role-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Leave Role', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'data_json',
            'emp_id',
            'thai_year',
            'work_year',
            'point',
            'point_use',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, LeaveRole $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

