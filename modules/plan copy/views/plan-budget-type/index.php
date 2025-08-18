<?php

use app\modules\plan\models\PlanBudgetType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanBudgetTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ประเภทงบ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-budget-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Plan Budget Type', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'code',
            'title',
            'category_id',
            //'code',
            //'emp_id',
            //'name',
            //'title:ntext',
            //'qty',
            //'description',
            //'data_json',
            //'ma_items',
            //'active',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, PlanBudgetType $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
