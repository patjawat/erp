<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
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
    ['name' => 'position_type','title' => 'ประเภทตำแหน่ง'],
    ['name' => 'position_group','title' => 'กลุ่มงาน'],
    ['name' => 'position_name','title' => 'กำหนดตำแหน่ง'],
    ['name' => 'position_level','title' => 'ระดับตำแหน่ง'],
    // ['name' => 'position_manage','title' => 'ตำแหน่งบริหาร'],
    ['name' => 'expertise','title' => 'ความเชี่ยวชาญ'],
];
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php if(!$name):?>

<div class="d-flex">
    <form class="subnav-search d-flex flex-nowrap ms-auto">
        <label for="search" class="visually-hidden">Search for icons</label>
        <input class="form-control search mb-0" id="search" type="search" placeholder="ค้นหาหมูวดหมู่..."
            autocomplete="off">
    </form>
</div>



<div class="card">
    <div class="card-body">
        <!-- <h4 class="card-title">Title</h4> -->

        <div class="table-responsive">
            <table id="icons-list" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <!-- <th scope="col" style="width:30px;">จำนวน</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items  as $key => $item):?>
                    <tr class="">
                        <td><i class="fa-regular fa-circle-check"></i> <?=Html::a($item['title'],['/hr/categorise','name' => $item['name'],'title' => $item['title']],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php else:?>



<?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<?php Pjax::begin(['id' => 'hr-container'])?>
<?=$this->render('@app/modules/hr/views/categorise/menu',['name' => $name,'title' => $title])?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout'=> '{summary}{items}',
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'code',
            'width' => '20px',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                return $model->code;
            }
        ],
        [
            'attribute' => 'title',
            'format' => 'raw',
            'width' => '90%',
            'value' => function($model){
                return $this->render('./title',['model' => $model]);
            }
        ],
        // [
        //     'class' => 'kartik\grid\ActionColumn',
        //     'width' => '50px',
        //     'template' => '{view} | {update}  | {delete}',  // the default buttons + your custom button
        //     'buttons' => [
        //         'view' => function($url, $model, $key) {     // render your custom button
        //             return Html::a('<i class="fa-regular fa-eye"></i>',['/hr/categorise/view','id' => $model->id,'title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-sm btn-outline-primary open-modal']);
        //         },
        //         'update' => function($url, $model, $key) {     // render your custom button
        //             return Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/hr/categorise/update','id' => $model->id,'title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-sm btn-outline-warning open-modal']);
        //         },
        //         'delete' => function($url, $model, $key) {     // render your custom button
        //             return Html::a('<i class="fa-solid fa-trash-can"></i>',['/hr/categorise/delete','id' => $model->id],['class' => 'btn btn-sm btn-outline-danger delete-item']);
        //         }
        //     ]
        // ]
        [
            'header' => 'ดำเนินการ',
            'format' => 'raw',
            'width' => '150px',
            'vAlign' => 'middle',
            'hAlign' => 'center',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                return $this->render('@app/modules/hr/views/categorise/action',['model' => $model]);
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
<?php endif;?>
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

<div class="categorise-index">



</div>