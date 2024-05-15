<?php

use app\modules\helpdesk\models\Helpdesk;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\RepairSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Repairs';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'helpdesk-container','timeout' => 5000 ]); ?>
<div class="" style="background-color:eee;">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-body">

            <table
                class="table table-primary"
            >
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th scope="col">Column 2</th>
                        <th scope="col">Column 3</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr>
                        <td>
                            <?=Html::a($model->data_json['title'],['/helpdesk/repair/view','id' => $model->id])?>
                        </td>
                        <td>R1C2</td>
                        <td>R1C3</td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        
<?php // $this->render('req_item',['model' => $model])?>

    <?php GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            // [
            //     'header' => 'ผู้แจ้ง',
            //     'format' => 'raw',
            //     'width' => '350px',
            //     'value' => function($model){
            //         return $model->getUserReq();
            //     }
            // ],
            [
                'header' => 'อาการ',
                'format' => 'raw',
                'value' => function($model){
                    return $this->render('title',['model' => $model]);
                }
            ],
            [
                'header' => 'สถานะ',
                'format' => 'raw',
                'width' => '100px',
                'value' => function($model){
                    return $this->render('status',['model' => $model]);
                }
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Helpdesk $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>
            
            </div>
    </div>
    
    <?php Pjax::end(); ?>