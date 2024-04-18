<?php

use app\modules\am\components\AssetHelper;
use app\modules\am\models\Fsn;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use app\components\SiteHelper;
use app\components\CategoriseHelper;
/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'การตั้งค่าทะเบีบนทรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
$name = Yii::$app->request->get('name');
if($name == 'asset_type'){
    $title = 'ประเภท'.Yii::$app->request->get('title');
}else{
    $title = Yii::$app->request->get('title');
}

?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gear"></i> <?=$this->title;?>
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
            <div class="btn-group">
                <?=Html::a('<i class="fa-solid fa-box-open me-1"></i> หมวดหมู่ทรัพย์สิน',['/am/asset-item/setting'],['class' => 'btn btn-light'])?>
                <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach(CategoriseHelper::Categorise('asset_group') as $assetGroup):?>
                    <li><?=Html::a('<i class="bi bi-check2-circle me-1 text-primary"></i>'.$assetGroup->title,['/am/asset-item','code' => $assetGroup->code,'title' => 'ประเภท'.$assetGroup->title], ['class' => 'dropdown-item'])?>
                    </li>
                    <?php endforeach;?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    </li>
                    <li><?=Html::a('<i class="fa-solid fa-gear me-2"></i> ตั้งค่าทรัพย์สิน',['/am/asset-item/setting','name' => 'asset_group'],['class' => ' dropdown-item'])?>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<?php if($code):?>


<div class="row">
    <div class="col-9">
        <div class="card  mb-0">
            <div class="card-body">
            
                <?php if($code):?>
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้าง'.$title,
                    'url' =>['/am/asset-item/create','category_id' => $code,'title' => $title,'name' => 'asset_name'],
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
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้าง'.$title,
                    'url' =>['/am/asset-item/create','name' => 'asset_type','code'  => $code ],
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
                                <?php if($name == "asset_group"):?>
                                <?=Html::a($fsnType->title,['/am/asset-item/','code' => $fsnType->code,'name' => $fsnType->name,'title' => $fsnType->title])?>
                                <?php else:?>

                                <div class="flex-grow-1 ms-3">

                                    <?=Html::a($fsnType->title,['/am/asset-item/view-type','id' => $fsnType->id,'name' => $fsnType->name,'title' => $fsnType->title])?><br>
                                    อายุการใช้งาน
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                        <?=$fsnType->code?>
                                    </label>
                                    ปี | ค่่าสื่อม
                                    <label class="badge rounded-pill text-primary-emphasis bg-danger-subtle me-1">
                                        <?=$fsnType->code?>
                                    </label>
                                </div>
                                <?php endif;?>
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
<?php else:?>

    <div class="row justify-content-center">
<div class="col-xl-6 col-lg -6 col-md-8 col-sm-12">
    <div class="alert alert-success" role="alert">
        <h4 class="alert-heading">เลือกหมวดทรัพย์สิน!</h4>
        <p>Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.</p>
        <hr>
        <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
    </div>
    

    
    </div>
        </div>

    <?php endif;?>
<?php Pjax::end();?>
<?php
$js = <<< JS


JS;
$this->registerJS($js)

?>
</div>