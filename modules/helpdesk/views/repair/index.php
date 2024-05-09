<?php

use app\modules\helpdesk\models\Repair;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\RepairSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Repairs';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'repair-container','timeout' => 5000 ]); ?>
<div class="" style="background-color:eee;">
    <div class="card mb-0">
        <div class="card-body">
            <?=app\components\AppHelper::Btn([
    'title' => "<i class='fa-solid fa-circle-plus'></i> สร้างใหม่",
    'url' => ['/helpdesk/repair/create', 'name' => 'repair','title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> บันทึกแจ้งซ่อม'],
    'modal' => true, 'size' => 'lg'])?>
        </div>
    </div>


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ref',
            'code',
            'date_start',
            'date_end',
            //'name',
            //'user_id',
            //'emp_id',
            //'data_json',
            //'updated_at',
            //'created_at',
            //'created_by',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Repair $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>