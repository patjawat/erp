<?php

use app\modules\am\models\AssetDetail;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::$app->request->get('title');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex justify-content-between">

    <h3><?= Html::encode($this->title) ?></h3>

    <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สอบเทียบ',
                    'url' =>['/am/asset-detail/create','name' => 'calibration_items','title' => '<i class="fa-solid fa-clock-rotate-left"></i> การเก็บประวัติ'],
                    'modal' =>true,
                    'size' => 'lg',
            ])?>

</div>

<?php Pjax::begin(); ?>
<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ref',
            'code',
            'user_id',
            'emp_id',
            //'name',
            //'data_json',
            //'updated_at',
            //'created_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AssetDetail $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

<?php Pjax::end(); ?>