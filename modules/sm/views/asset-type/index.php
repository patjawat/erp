<?php

use app\modules\sm\models\AssetType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Asset Types';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="asset-type-index">
<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
    <h4>ตั้งค่ากลุ่ม </h4> 
    <div class="d-flex gap-2">  
                <?=Html::a('<i class="fa-solid fa-chevron-left me-1"></i> ย้อนกลับ',['/sm/asset-item'],['class' => 'btn btn-light'])?>
        </div>
    </div>
    </div>
</div>
<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => true, 'timeout' => 5000]); ?>

        <div class="card mb-0">
            <div class="card-body">
            <?=app\components\AppHelper::Btn([
                    'title' =>"<i class='fa-solid fa-circle-plus'></i> สร้างกลุ่มครุภัณฑ์หรือวัสดุ",
                    'url' =>['/sm/asset-type/create','name' => 'asset_type'],
                    'modal' => true, 'size' => 'lg' ])?>
            </div>
        </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

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
                'header' => 'ชื่อ',
                'value' => function($model){
                        return $this->render('type_item',['model' => $model]);
                }
            ],
            [
                'attribute' => 'code',
                'format' => 'raw',
                'header' => 'รหัส',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                        return $model->code;
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
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'category_id',
                    'data' => ['3' => 'ครุภัณฑ์', '4' => 'วัสดุ'],
                    'options' => ['placeholder' => 'เลือกประเภท...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'value' => function($model){
                        return $model->category_id == 3 ? "ครุภัณฑ์" : "วัสดุ";
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

    <?php Pjax::end(); ?>

</div>
