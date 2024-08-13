<?php
use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
use app\components\CategoriseHelper;
$this->title = 'ตั้งค่ากำหนดตำแหน่ง';
?>
<?php Pjax::begin(['id' => 'hr-container'])?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../employees/menu')?>
<?php $this->endBlock(); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start gap-2">
            <?php if(Yii::$app->request->get('title')):?>
                <h5><i class="fa-regular fa-folder-open text-primary"></i> <?=Yii::$app->request->get('title')?></h5>
                <?php else:?>
        <h5>การกำหนดตำแหน่ง</h5>
        <?php endif;?>
        </div>

        <div class="d-flex gap-2">

            <?php //  $this->render('_search', ['model' => $searchModel]); ?>

            <?=Html::a('<i class="bi bi-list-ul"></i>',['/hr/employees/index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['/hr/employees/index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>',['/hr/categorise','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>

        </div>

    </div>
</div>

<div class="row">
    <div class="col-9">

<?php if(Yii::$app->request->get('code')):?>
    <div class="card mb-0">
            <div class="card-body">
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างกลุ่มงาน',
                    'url' =>['/hr/position/create','name'=> 'position_group','category_id' => Yii::$app->request->get('code'),'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างกลุ่มงาน', ],
                    'modal' => true,
                    'size' => 'md'])?>
            </div>
        </div>

        <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
                'header' => 'รายการ',
                'format' => 'raw',
                'value' => function($model){
                        return Html::a($model->title,['view','id' => $model->id,'code' => $model->code]);
                }
            ],
            [
                'attribute' => 'code',
                'header' => 'code',
                'value' => function($model){
                        return $model->code;
                }
            ],
            [
                'attribute' => 'category_id',
                'header' => 'ประเภท',
                'value' => function($model){
                        return $model->positionType->title;
                }
            ],
            [
                'header' => 'ดำเนินการ',
                'width' => '100px',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function($model){
                        return '<div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                            data-bs-toggle="dropdown" aria-expanded="false"><i
                                class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                        <div class="dropdown-menu" style="">
                            <??>
                            '.Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/position/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขกลุ่ม'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]).'

                            '.Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/position/delete', 'id' => $model->id], [
                        'class' => 'dropdown-item delete-item',
                        ]).'
                        </div>
                    </div>';
                }
            ]
           
           
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
<?php else:?>
    <div class="alert alert-success" role="alert">
  <h4 class="alert-heading">การตั้งค่าตำแหน่ง!</h4>
  <p><i class="fa-solid fa-circle-exclamation"></i> ข้อมูลรายการตำแหน่งจะแสดงเมื่อท่านเลือกประเภทของบุคลากร.</p>
  <hr>
  <p class="mb-0">กรุณาเลือกประเภทของตำแหน่งเพื่อทำการตั้งค่าต่อไป.</p>
</div>
    <?php endif;?>

    </div>
    <div class="col-3">

        <div class="card mb-0">
            <div class="card-body">
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างประเภทบุคลากร',
                    'url' =>['/hr/position/create','name' => 'position_type','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างประเภท'],
                    'modal' => true,
                    'size' => 'md'])?>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                    <?php foreach(CategoriseHelper::Categorise('position_type') as $positionType):?>
                    <tr class="">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php if($positionType->code == Yii::$app->request->get('code')):?>
                                        <i class="fa-regular fa-folder-open fs-2 text-primary"></i>
                                        <?php else:?>
                                        <i class="bi bi-folder-check fs-2"></i>
                                   <?php endif;?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <?=Html::a($positionType->title,['/hr/position','code' => $positionType->code,'title' => $positionType->title])?><br>
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                        <?=$positionType->code?>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td style="width: 90px;">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $positionType->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขประเภท'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-sm']]) ?>
                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $positionType->id], [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this item?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>


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