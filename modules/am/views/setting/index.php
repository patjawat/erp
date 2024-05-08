<?php

use app\models\Categorise;
use app\modules\am\models\AssetType;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตั้งค่าประเภททรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'title-container','timeout' => 500 ]); ?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <span class="page-title"><?=$this->title?></span>
<?php $this->endBlock();?>
<?php $this->beginBlock('sub-title');?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock()?>
<?php Pjax::end(); ?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>

<span style="display:none" id="page-title">
<span class="fs-5">
    <i class="bi bi-folder-check"></i> <?=$this->title?></span>
</span>

<style>
    .grid-expanded-row-details{
        background-color: white;
    }
</style>

<div class="" style="background-color:eee;">


        <div class="card mb-0">
            <div class="card-body">
            <?=app\components\AppHelper::Btn([
    'title' => "<i class='fa-solid fa-circle-plus'></i> สร้างประเภททรัพย์สิน",
    'url' => ['/am/setting/create', 'name' => 'asset_type'],
    'modal' => true, 'size' => 'lg'])?>
            </div>
        </div>
    <?=GridView::widget([
    'id' => 'demo-container',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'hover' => true,
    'pjax' => false,
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
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function ($model, $key, $index, $column) {
                // $assetItem = AssetItem::find()->where(['category_id' => $model->code])->count('id');
                // if($assetItem > 0){
                //     return GridView::ROW_EXPANDED;
                // }else{
                // }
                return GridView::ROW_COLLAPSED;

            },
            'detailUrl' => Url::to(['/am/setting/items-detail']),
            'headerOptions' => ['class' => 'kartik-sheet-style'],
            'expandOneOnly' => true,
            'expandIcon' => '<i class="fa-solid fa-folder-plus"></i>',
            'collapseIcon' => '<i class="fa-regular fa-folder-open"></i>',
            'detailRowCssClass' => 'grid-expanded-row-details',
        ],
        [
            'attribute' => 'title',
            'format' => 'raw',
            'header' => 'ชื่อ',
            'value' => function ($model) {
                return $this->render('type_item', ['model' => $model]);
            },
        ],
        [
            'attribute' => 'data_json[service_life]',
            'format' => 'raw',
            'header' => 'อายุการใช้งาน (ปี)',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'width' => '150px',
            'value' => function ($model) {
                return isset($model->data_json["service_life"]) ? $model->data_json["service_life"] : '';
            },
        ],
        [
            'attribute' => 'data_json[depreciation]',
            'format' => 'raw',
            'header' => 'อัตราค่าเสื่อม',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'width' => '150px',
            'value' => function ($model) {
                return isset($model->data_json["depreciation"]) ? $model->data_json["depreciation"] : '';
            },
        ],

    ],
]);?>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>
    <?php Pjax::end();?>

</div>
