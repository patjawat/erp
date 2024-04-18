<?php

use app\modules\am\components\AssetHelper;
use app\modules\am\models\Fsn;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\SiteHelper;
use app\components\CategoriseHelper;
/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'การตั้งค่าครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;

$group = Yii::$app->request->get('group');
$category_id = Yii::$app->request->get('category_id');
$name = Yii::$app->request->get('name');

?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gear"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => true, 'timeout' => 5000]);?>


<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
    <h4><?= Yii::$app->request->get('title') ?></h4>
    <div class="d-flex gap-2">  
                <?=Html::a('<i class="fa-solid fa-chevron-left me-1"></i> ย้อนกลับ',['/sm/asset-item'],['class' => 'btn btn-light'])?>
                <?= Yii::$app->request->get('group') == 3 ? Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่ากลุ่มทรัพย์สิน',['/sm/asset-item/next','group' => 4,'title' => 'ตั้งค่ากลุ่มทรัพย์สิน'],['class' => 'btn btn-light']) : Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่ากลุ่มครุภัณฑ์',['/sm/asset-item/next','group' => 3,'title' => 'ตั้งค่ากลุ่มครุภัณฑ์'],['class' => 'btn btn-light']) ?>
        </div>
    </div>
    </div>
</div>


<div class="row justify-content-center">

<div class="col">

        <div class="card mb-0">
            <div class="card-body">
            <?=app\components\AppHelper::Btn([
                    'title' => Yii::$app->request->get('group') == 3 ? "<i class='fa-solid fa-circle-plus'></i> สร้างกลุ่มครุภัณฑ์" : "<i class='fa-solid fa-circle-plus'></i> สร้างกลุ่มทรัพย์สิน",
                    'url' =>['/sm/asset-item/create','name' => 'asset_type','code'  => $model->code,'group' => $model->category_id,'title' => Yii::$app->request->get('group') == 3 ?  'สร้างประครุภัณฑ์' : 'สร้างประทรัพย์สิน' ],
                    'modal' => true,])?>
            </div>
        </div>



    <?= GridView::widget([
        'dataProvider' => $dataProviderGroup,
        'filterModel' => $searchModel,
        'hover' => true,
        'layout' => '{items}',
        'columns' => [
            [
                'class'=>'kartik\grid\SerialColumn',
                'contentOptions'=>['class'=>'kartik-sheet-style'],
                'width'=>'36px',
                'pageSummary'=>'Total',
                'pageSummaryOptions' => ['colspan' => 6],
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'header' => 'ชื่ออาคาร',
                'value' => function($model){
                        return $this->render('type_item',['model' => $model]);
                }
            ],
            [
                'format' => 'raw',
                'header' => 'กำเนินการ',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '90px',
                'value' => function($model){
                    return '<div clas="d-flex gap-3">'.Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary open-modal me-2', 'data' => ['size' => 'modal-lg']])
                    .Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-sm btn-danger',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],
                    ]).'</div>';
                }
            ],
           
            
        ],
    ]); ?>


        
    </div>
    </div>

<?php Pjax::end();?>
<?php
$js = <<< JS


JS;
$this->registerJS($js)

?>
</div>