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
หมวดครุภัณฑ์
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>


<div class="card">
                <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
                    <div class="d-flex justify-content-start">
                        <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้าง'.$title,
                    'url' =>['/am/fsn/create'],
                    'modal' => true,
                    'size' => 'lg'])?>
                    </div>

                    <div class="d-flex gap-2">
                        <?=Html::a('<i class="bi bi-list-ul"></i>',['index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
                        <?=Html::a('<i class="bi bi-grid"></i>',['index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
                        <?=Html::a('<i class="fa-solid fa-gear"></i>', ['/am/fsn/group-setting'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
                    </div>

                </div>
            </div>


<div class="row">
    <div class="col-8">
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
        </div>
        <div class="col-4">
<div class="card mb-0">
    <div class="card-body">
    <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างหมวดหมู่ครุภัณฑ์',
                    'url' =>['/am/fsn/create'],
                    'modal' => true,
                    'size' => 'lg'])?>
    </div>
</div>

            <div class="list-group mt-0">
            <!-- <a href="<?=Url::to(['/am/fsn','code' => ''])?>"
                    class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                    <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-circle-plus"></i></div>
                    <div class="d-flex gap-2 w-100 justify-content-between">
                        <div>
                            <h6 class="mb-0 text-primary">สร้างประเภทครุภัณฑ์</h6>
                            <p class="mb-0 opacity-75 fw-light">ปป</p>
                        </div>
                        <small class="opacity-50 text-nowrap">xx</small>
                    </div>
                </a> -->
                <?php foreach($dataProviderType->getModels() as $fsnType):?>
                <a href="<?=Url::to(['/am/fsn','code' => $fsnType->code,'title' => $fsnType->title])?>"
                    class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                    <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light">icon</div>
                    <div class="d-flex gap-2 w-100 justify-content-between">
                        <div>
                            <h6 class="mb-0 text-primary"><?=$fsnType->title?></h6>
                            <p class="mb-0 opacity-75 fw-light"><?=$fsnType->title?></p>
                        </div>

                        
                        <!-- <small class="opacity-50 text-nowrap"> -->
                            <a href="#" class="opacity-50 text-nowrap">แก้ไข</a>
                            <?php //$fsnType->code?>
                            <!-- แก้ไข -->
                            <?php //  Html::a('Update', ['update', 'id' => $fsnType->id], ['class' => '']) ?>
                        <!-- </small> -->
                    </div>
                </a>
                <?php endforeach;?>
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