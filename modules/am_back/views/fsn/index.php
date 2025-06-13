<?php

use app\modules\am\components\AssetHelper;
use app\modules\am\models\Fsn;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use app\components\SiteHelper;
/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'การให้หมายเลข FSN';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ประเภทครุภัณฑ์
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">
            <?php if($code):?>
                <h5><i class="fa-regular fa-folder-open text-primary fs-3"></i> <?=$title?></h5>
           
            <?php else:?>
            <h5>รายการพัสดุ</h5>
            <?php endif;?>
        </div>

        <div class="d-flex gap-2">
            <?=Html::a('<i class="bi bi-list-ul"></i>',['index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>', ['/am/fsn/group-setting'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-9">
        <div class="card  mb-0">
            <div class="card-body">
            <?php if($code):?>
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้าง'.$title,
                    'url' =>['/am/fsn/create','category_id' => $code,'title' => $title,'name' => 'asset_name'],
                    'modal' => true,
                    'size' => 'lg'])?>
            <?php else:?>
            <h5>รายการพัสดุ</h5>
            <?php endif;?>
            </div>
        </div>
        
        <!-- แสดงมุมมอง -->
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
        <!-- จบแสดงมุมมอง -->
    </div>
    <div class="col-3">

        <div class="card mb-0">
            <div class="card-body">
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างกลุ่มครุภัณฑ์',
                    'url' =>['/am/fsn/create','name' => 'asset_group'],
                    'modal' => true,
                    'size' => 'lg'])?>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                    <?php foreach($dataProviderGroup->getModels() as $fsnType):?>
                    <tr class="">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php if($fsnType->code == Yii::$app->request->get('code')):?>
                                    <i class="fa-regular fa-folder-open fs-2 text-primary"></i>
                                    <?php else:?>
                                    <i class="bi bi-folder-check fs-2"></i>
                                    <?php endif;?>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    
                                    <?=Html::a($fsnType->title,['/am/fsn/index','code' => $fsnType->code,'name' => $fsnType->name,'title' => $fsnType->title])?><br>
                                    อายุการใช้งาน
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                         <?=$fsnType->code?>
                                    </label>
                                    ปี | ค่่าสื่อม 
                                    <label class="badge rounded-pill text-primary-emphasis bg-danger-subtle me-1">
                                         <?=$fsnType->code?>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td style="width: 90px;">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $fsnType->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $fsnType->id], [
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

<?php Pjax::end();?>
<?php
$js = <<< JS


JS;
$this->registerJS($js)

?>
</div>