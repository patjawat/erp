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

<?php Pjax::begin(['id' => $name.'-container'])?>


<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout'=> '{summary}{items}',
    'columns' => [
        // ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'title',
            'header' => 'ชื่อตำแหน่ง',
            'format' => 'raw',
            'width' => '100%',
            'value' => function($model){
               return $model->title;
            }
        ],
        [
            'attribute' => 'position_group',
            'format' => 'raw',
            'width' => '38px',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                return ' <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                class="bi bi-clipboard-check me-1"></i>'.$model->positionGroup->title.'</label>';
            }
        ],
        [
            'attribute' => 'position_type',
            'format' => 'raw',
            'width' => '38px',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                return ' <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                class="bi bi-clipboard-check me-1"></i>'.$model->positionGroup->positionType->title.'</label>';
            }
        ],
        [
            'header' => 'ดำเนินการ',
            'format' => 'raw',
            'width' => '180px',
            // 'headerOptions' => ['style' => 'width:100px'],
            'value' => function($model){
                return $this->render('../action',['model' => $model]);
            }
        ],
       
    
        
       
    ],
]); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start gap-2">
            <?=app\components\AppHelper::Btn([
                'url' => ['create'],
                'modal' => true,
                'size' => 'lg',
                ])?>
        </div>

        <div class="d-flex gap-2">

            <?php //  $this->render('_search', ['model' => $searchModel]); ?>

            <?=Html::a('<i class="bi bi-list-ul"></i>',['/hr/employees/index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['/hr/employees/index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>',['/hr/categorise','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>

        </div>

    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col" >ชื่อตำแหน่ง</th>
                        <th style="width:100px">กลุ่มงาน</th>
                        <th style="width:100px">ประเภทบุคลากร</th>
                        <th style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr class="">
                        <td class="text-truncate"><?=$model->title?></td>
                        <td class="align-middle">
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                            class="bi bi-clipboard-check"></i> <?= $model->positionGroup->title?></label>
                               
                        </td>
                        <td class="align-middle">
                        <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                            class="bi bi-clipboard-check"></i> <?= $model->positionGroup->positionType->title?></label>

                        </td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <??>
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                    <?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $model->id], [
                                        'class' => 'dropdown-item delete-item',
                                        ])?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>




    </div>
</div>
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

