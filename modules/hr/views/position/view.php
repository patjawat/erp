<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\models\Categorise;
use app\components\CategoriseHelper;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Categorises', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php Pjax::begin(['id' => 'hr-container'])?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>


<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start gap-2">
            <h5 class="mb-0">
                <i class="bi bi-folder-check fs-2"></i> <?=$model->title?> <span
                    class="badge bg-danger badge-pill bg-danger rounded-pill text-white"><?=$dataProviderPositionName->getTotalCount()?>
                </span> ตำแหน่ง
            </h5>
        </div>

        <div class="d-flex gap-2">
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
                    'title' => '<i class="fa-solid fa-circle-plus"></i>  สร้างตำแหน่ง',
                    'url' =>['/hr/position/create','name' => 'position_name','category_id' => $model->code,'title' => 'สร้างตำแหน่ง | กลุ่ม'.$model->title],
                    'modal' => true,
                    'size' => 'lg'])?>
            </div>
        </div>

        <?= GridView::widget([
        'dataProvider' => $dataProviderPositionName,
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
                'value' => function($model){
                        return $model->title;
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
                'header' => 'กลุ่ม',
                'width' => '150px',
                'value' => function($model){
                        return $model->positionGroup->title;
                }
            ],
            [
                'header' => 'ดำเนินการ',
                'width' => '10px',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'format' => 'raw',
                'value' => function($model){
                        return '<div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                            data-bs-toggle="dropdown" aria-expanded="false"><i
                                class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                        <div class="dropdown-menu">
                            <??>
                            '.Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/position/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-user-tag"></i> แก้ไขตำแหน่ง','name' => 'position_name'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]).'

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
                    'pagination' => $dataProviderPositionName->pagination,
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
  <h4 class="alert-heading">เลือกกลุ่มของตำแหน่ง!</h4>
  <p><i class="fa-solid fa-circle-exclamation"></i> ข้อมูลรายการตำแหน่งจะแสดงเมื่อท่านเลือกกลุ่มของบุคลากร.</p>
  <hr>
  <p class="mb-0">กรุณาเลือกกลุ่มของตำแหน่งเพื่อทำการตั้งค่าต่อไป.</p>
</div>

<?php endif;?>


    </div>
    <div class="col-3">
        <div class="card mb-0">
            <div class="card-body d-flex justify-content-between">
                <?=Html::a('<i class="fa-solid fa-angle-left"></i> ย้อนกลับ',['/hr/position'],['class' => 'btn btn-outline-primary'])?>

            </div>
        </div>

        <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'code',
            [
                'format'=>'raw',
                'label' => 'ประเภท',
                'value' =>  $model->positionType->title
            ],
        ],
    ]) ?>



        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                    <?php foreach($dataProviderPositionGroup->getModels() as $key => $positionGroup):?>
                    <tr class="">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php if($positionGroup->code == Yii::$app->request->get('code')):?>
                                    <i class="fa-regular fa-folder-open fs-2 text-primary"></i>
                                    <?php else:?>
                                    <i class="bi bi-folder-check fs-2"></i>
                                    <?php endif;?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <?=Html::a($positionGroup->title,['/hr/position/view','id' => $positionGroup->id,'code' => $positionGroup->code,'title' => $positionGroup->title])?><br>
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                        <?=$positionGroup->code?>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td style="width: 90px;">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $positionGroup->id,'title' => 'แก้ไข'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $positionGroup->id], [
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