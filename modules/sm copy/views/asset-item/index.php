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
use app\models\Categorise;
use kartik\select2\Select2;

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



<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
    <h4>การตั้งค่าครุภัณฑ์</h4> 
    <div class="d-flex gap-2">  
                <?=Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่ากลุ่มทรัพย์สิน',['/sm/asset-type'],['class' => 'btn btn-light'])?>
        </div>
    </div>
    </div>
</div>

<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => false, 'timeout' => 5000]);?>


<div class="row justify-content-center">

    <div class="col">

    <div class="card mb-0">
            <div class="card-body">
            <?=app\components\AppHelper::Btn([
                    'title' => Yii::$app->request->get('group') == 3 ? "<i class='fa-solid fa-circle-plus'></i> สร้างครุภัณฑ์" : "<i class='fa-solid fa-circle-plus'></i> สร้างครุภัณฑ์",
                    'url' =>['/sm/asset-item/create'],
                    'modal' => true,'size'=>'lg'])?>
            </div>
        </div>



    <?= GridView::widget([
        'dataProvider' => $dataProviderGroup,
        'filterModel' => $searchModel,
        'hover' => true,
        'layout' => '{items}',
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'contentOptions' => ['class' => 'kartik-sheet-style'],
                'width' => '36px',
                'pageSummary' => 'Total',
                'pageSummaryOptions' => ['colspan' => 6],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'header' => 'รายการครุภัณฑ์',
                'value' => function($model){
                        return $this->render('type_item',['model' => $model]);
                }
            ],
            [
                'attribute' => 'category_id',
                'format' => 'raw',
                'header' => 'ทรัพย์สิน',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                    // return Categorise::findOne(['name' => 'asset_type','code'=>$model->category_id])->title;
                }
            ],
            [
                'attribute' => 'data_json',
                'format' => 'raw',
                'header' => 'ประเภท',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'data_json',
                    'data' => ['3' => 'ครุภัณฑ์', '4' => 'วัสดุ'],
                    'options' => ['placeholder' => 'เลือกประเภท...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'value' => function($model){
                    // return $model->data_json["asset_type"]["category_id"] == 3 ? 'ครุภัณฑ์' : "วัสดุ";
                }
            ],
/*             [
                'format' => 'raw',
                'header' => 'ดำเนินการ',
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
            ], */
           
            
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