<?php

use app\modules\am\models\Asset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\SiteHelper;



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
<i class="bi bi-folder-check"></i> <?=$this->title;?>
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
            <div class="d-flex justify-content-start">
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
            </div>

            <div class="d-flex gap-2">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
            <span class="filter-asset btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-custom-class="custom-tooltip" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
        <i class="fa-solid fa-filter"></i>
    </span>
                <?=Html::a('<i class="bi bi-list-ul"></i>',['/am/asset/','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
                <?=Html::a('<i class="bi bi-grid"></i>',['/am/asset/','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
                <?=Html::a('<i class="fa-solid fa-file-import me-1"></i>',['/am/asset/import-csv'],['class' => 'btn btn-outline-primary','title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
            'data' => [
                'bs-placement' => 'top',
                'bs-toggle' => 'tooltip',
                ]])?>
            </div>

        </div>
    </div>


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