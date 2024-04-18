<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
use yii\helpers\ArrayHelper;
$name = Yii::$app->request->get('name');
$title = Yii::$app->request->get('title');

/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตั้งค่าบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = $this->title;

$items = [
    ['name' => 'prefix','title' => 'คำนำหน้า'],
    ['name' => 'Nationality','title' => 'สัญชาติ/เชื้อชาติ'],
    ['name' => 'marry','title' => 'สถานภาพสมรส'],
    ['name' => 'blood','title' => 'หมู่โลหิต'],
    ['name' => 'religion','title' => 'ศาสนา'],
    ['name' => 'position_name','title' => 'ตั้งค่าชื่อตำแหน่ง'],
    ['name' => 'position_type','title' => 'ตั้งค่าตำแหน่ง'],
    ['name' => 'position_level','title' => 'ระดับตำแหน่ง'],
    // ['name' => 'position_group','title' => 'ประเภท/กลุ่มงาน'],
    ['name' => 'position_manage','title' => 'ตำแหน่งบริหาร'],
    ['name' => 'expertise','title' => 'ความเชี่ยวชาญ'],
];
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'hr-container'])?>

<?php // $this->render('@app/modules/hr/views/categorise/menu',['name' => $name,'title' => $title])?>


<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout'=> '{summary}{items}',
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        // [
        //     'attribute' => 'code',
        //     'header' => 'รหัส',
        //     'format' => 'raw',
        //     'width' => '100px',
        //     'value' => function($model){
        //        return $model->code;
        //     }
        // ],
        [
            'attribute' => 'title',
            'header' => 'ชื่อตำแหน่ง',
            'format' => 'raw',
            'width' => '80%',
            'value' => function($model){
               return $model->title;
            }
        ],
        [
            'attribute' => 'position_group',
            // 'width' => '38px',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => ArrayHelper::map(Categorise::find()->where(['name' => 'position_group'])->all(), 'code',function($model){
                return $model->title;
        }),  
            'filterWidgetOptions' => [
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'เลือกกลุ่มงาน...', 'multiple' => false], // allows multiple authors to be chosen
            'format' => 'raw',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                if(isset($model->positionGroup->title)){

                    return ' <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i class="fa-solid fa-layer-group text-success me-1"></i>'.$model->positionGroup->title.'</label>';
                }else{
                    return '<i class="fa-solid fa-circle-exclamation text-warning"></i> ไม่ได้ระบุ';
                }
               
            }
        ],
        [
            'attribute' => 'position_type',
            // 'width' => '38px',
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => ArrayHelper::map(Categorise::find()->where(['name' => 'position_type'])->all(), 'code',function($model){
                    return $model->title;
            }), 
            'filterWidgetOptions' => [
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => 'เลือกประเภท...', 'multiple' => false], // allows multiple authors to be chosen
            'format' => 'raw',
            'value' => function($model){
                if(isset($model->positionGroup->positionType->title)){

                    return ' <label class="badge rounded-pill text-primary-emphasis bg-primary-subtle me-1"><i
                    class="fa-solid fa-tags ext-success text-primary me-1"></i>'.$model->positionGroup->positionType->title.'</label>';
                }else{
                    return '<i class="fa-solid fa-circle-exclamation text-warning"></i> ไม่ได้ระบุ';
                }
            }
        ],
        [
            'header' => 'ดำเนินการ',
            'format' => 'raw',
            'width' => '20%',
            'vAlign' => 'middle',
            'hAlign' => 'center',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                return Html::a('<i class="fa-solid fa-eye me-1"></i>', ['/hr/categorise/view', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'open-modal', 'data' => ['size' => 'modal-md']]);
            }
        ],
       
    ],
]); ?>

<div class="d-flex justify-content-center">

    <div class="text-muted">
        <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
    </div>
</div>

<?php Pjax::end()?>

<?php
$js = <<< JS

$(document).ready(function(){
  $("#search").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#icons-list tbody tr td").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

JS;
$this->registerJS($js);
?>

