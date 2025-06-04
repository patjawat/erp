<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\bootstrap5\LinkPager;
use app\components\SiteHelper;
use app\modules\am\models\Fsn;
use app\components\CategoriseHelper;
use app\modules\am\components\AssetHelper;

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

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/sm/views/default/menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>


<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
         <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่ ', ['/sm/asset-item/create', 'title' => Yii::$app->request->get('group') == 3 ? "<i class='fa-solid fa-circle-plus'></i> สร้างครุภัณฑ์" : "<i class='fa-solid fa-circle-plus'></i> สร้างครุภัณฑ์",], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <div class="d-flex gap-2">
            <?=Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่ากลุ่มทรัพย์สิน',['/sm/asset-type'],['class' => 'btn btn-light'])?>
        </div>
    </div>
</div>

<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => false, 'timeout' => 5000]);?>


<div class="card">
    <div class="card-body">
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
            
        ],
    ]); ?>



    </div>
</div>

<?php Pjax::end();?>
