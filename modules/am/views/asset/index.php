<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\am\models\Asset;



/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
$this->title = 'ทะเบียน'.$title;
$this->params['breadcrumbs'][] = ['label' => 'จัดการทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Pjax::begin(['id' => 'title-container','timeout' => 50000 ]); ?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ทั้งหมด <span id="showTotalCount"><?=$dataProvider->getTotalCount()?></span> รายการ
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>
<?php Pjax::end(); ?>
<?php Pjax::begin(['id' => 'am-container','timeout' => 50000 ]); ?>

<div class="asset-index">

    <div class="card">
        <div
            class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
            <div class="d-flex justify-content-start gap-3">
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> ลงทะเบียน'.$title,
                    'url' =>['select-type','group' => $group,'title' => $title],
                    'modal' =>true,
                    'size' => 'sm',
            ])?>
                <?php if($group):?>
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> ลงทะเบียน'.$title,
                    'url' =>['create','group' => $group,'title' => $title],
                    'model' =>true,
                    'size' => 'lg',
            ])?>
            <?php else:?>
    <!-- <a class="btn btn-outline-warning text-primary" href="#" data-pjax="0" onclick="return alert('กรุณาเลือกประเภททรัพย์สินก่อนสร้างรายการใหม่')"><i class="fa-solid fa-circle-exclamation text-danger"></i> เลือกประเภททรัพย์สินเพื่อสร้างรายการ</a> -->
                <?php endif;?>

                <?=app\components\AppHelper::Btn([
    'title' => '<i class="fa-solid fa-circle-exclamation"></i> รายการไม่สมบูรณ์',
    'url' => ['/am/asset/omit'],
    'modal' => true, 
    'size' => 'lg',
    'class' => 'btn btn-danger'
    ]
    )?>
            </div>

            <div class="d-flex gap-2">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
            <span class="filter-asset btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-custom-class="custom-tooltip" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
        <i class="fa-solid fa-filter"></i>
    </span>
    <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-primary setview']) ?>
    <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-primary setview']) ?>
                <?=Html::a('<i class="fa-solid fa-file-import me-1"></i>',['/am/asset/import-csv'],['class' => 'btn btn-outline-primary','title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
            'data' => [
                'bs-placement' => 'top',
                'bs-toggle' => 'tooltip',
                ]])?>
            </div>

        </div>
    </div>

    <?php if(count(($dataProvider->getModels())) == 0):?>

        <div class="row d-flex justify-content-center">
        <div class="col-6">
        <div class="f-flex justify-content-center align-items-center mt-5 bg-primary bg-opacity-10  p-3 rounded-2">
            <h4 class="text-center"> <i class="fa-solid fa-circle-exclamation text-primary"></i> ไม่มีทรัพย์สินที่ได้รับผิดชอบ</h4>
            <p class="text-center">หากต้องการสืบค้นสามารถใช้ตัวกรองเพื่อค้นหาข้อมูลได้</p>
</div>
        </div>
        </div>


        <div class="">
        <div class="row d-flex flex-column">
<div class="col-lg-6 col-md-8 col-sm-12">

</div>
        </div>
        </div>
    <?php endif;?>


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php if(SiteHelper::getDisplay() == 'list'):?>

<?=$this->render('show/list', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);?>

<?php else:?>
<?=$this->render('show/grid', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);?>

<?php endif?>


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


</div>
<span id="totalCount" class="d-none"><?=$dataProvider->getTotalCount();?></span>

<?php Pjax::end(); ?>

<?php
$js = <<< JS

$('#am-container').on('pjax:success', function() {
    // Your code goes here ...
    console.log('success',$('#totalCount').text());
    $('#showTotalCount').text($('#totalCount').text())
    $.pjax.reload({ container:'#title-container', history:false,replace: false});         
});

JS;
$this->registerJS($js)

?>