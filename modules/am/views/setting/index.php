<?php

use app\modules\am\models\AssetType;
use app\modules\am\models\AssetItem;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Asset Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .grid-expanded-row-details{
        background-color: white;
    }
</style>

<div class="" style="background-color:eee;">

<?php  Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]); ?>

        <div class="card mb-0">
            <div class="card-body">
            <?=app\components\AppHelper::Btn([
                    'title' =>"<i class='fa-solid fa-circle-plus'></i> สร้างกลุ่มครุภัณฑ์หรือวัสดุ",
                    'url' =>['/am/setting/create','name' => 'asset_type'],
                    'modal' => true, 'size' => 'lg' ])?>
            </div>
        </div>
    <?= GridView::widget([
        'id' => 'demo-container',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover' => true,
        'pjax'=>false,
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
                'value' => function($model){
                        return $this->render('type_item',['model' => $model]);
                }
            ],
            [
                'attribute' => 'data_json[service_life]',
                'format' => 'raw',
                'header' => 'อายุการใช้งาน (ปี)',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'value' => function($model){
                        return  isset($model->data_json["service_life"]) ? $model->data_json["service_life"] : '';
                }
            ],
            [
                'attribute' => 'data_json[depreciation]',
                'format' => 'raw',
                'header' => 'อัตราค่าเสื่อม',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'value' => function($model){
                        return isset($model->data_json["depreciation"]) ? $model->data_json["depreciation"] : '';
                }
            ],
            [
                'attribute' => 'category_id',
                'format' => 'raw',
                'header' => 'ประเภท',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                // 'filter' => Select2::widget([
                //     'model' => $searchModel,
                //     'attribute' => 'category_id',
                //     'data' => ['3' => 'ครุภัณฑ์', '4' => 'วัสดุ'],
                //     'options' => ['placeholder' => 'เลือกประเภท...'],
                //     'pluginOptions' => [
                //         'allowClear' => true,
                //     ],
                // ]),
                'value' => function($model){
                        return $model->id;
                },
                'filter' => ArrayHelper::map(Categorise::find()->where(['name' => 'asset_group'])->asArray()->all(), 'code', 'title'),
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => ''],
                    'pluginOptions' => ['allowClear' => true],
                ],
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

    <?php Pjax::end(); ?>

</div>
