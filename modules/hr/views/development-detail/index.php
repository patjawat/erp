<?php

use app\modules\hr\models\DevelopmentDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Development Details';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="development-detail-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Development Detail', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'development_id',
            'category_id',
            'name',
            'emp_id',
            //'qty',
            //'price',
            //'data_json',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',
            //'deleted_at',
            //'deleted_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, DevelopmentDetail $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
