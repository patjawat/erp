<?php

use app\modules\am\components\AssetHelper;
use app\modules\am\models\Fsn;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
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

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<div class="fsn-index">


    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <p class="mb-0">หมวดครุภัณฑ์</p>
            <div class="action">
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/fsn/create'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>      
                <?=Html::a('<i class="fa-solid fa-gear"></i> การตั้งค่า', ['/am/fsn/group-setting'], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']])?>
            </div>
        </div>
    </div>


    <div class="container-fluid bg-trasparent my-4 p-3" style="position: relative">
        <div class="row row-cols-1 row-cols-xs-2 row-cols-sm-2 row-cols-lg-5 g-3">
            <?php foreach ($dataProvider->getModels() as $fsnGroup): ?>
            <div class="col hp">
                <div class="card h-100 shadow-sm">
                    <?= Html::a(Html::img($searchModel->showLogoImg($fsnGroup->ref),['class' => 'card-img-top p-3']), ['view', 'id' => $fsnGroup->id]) ?>


                    <div class="card-body">
                        <div class="clearfix mb-3">
                            <span class="float-start badge rounded-pill bg-success">หมวด <?=($fsnGroup->code)?></span>

                            <span class="float-end"><a href="#" class="small text-muted text-uppercase aff-link">10
                                    รายการ</a></span>
                        </div>
                        <h5 class="card-title">
                            <?=Html::a($fsnGroup->title,['/am/fsn'])?>
                        </h5>

                        <div class="d-grid gap-2 my-4">
                            
                        <div class="btn-group">
                            <?=Html::a('กำหนดหมายเลข FSN',['view', 'id' => $fsnGroup->id],['class' => 'btn btn-warning'])?>
                            <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?=Html::a('แก้ไขครุภัณฑ์',['/am/fsn/update', 'id' => $fsnGroup->id], ['class' => 'btn btn-primary open-modal dropdown-item', 'data' => ['size' => 'modal-lg']])?></li>
                            </ul>
                        </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>



    <?php Pjax::end();?>
    <?php
$js = <<< JS


JS;
$this->registerJS($js)

?>           
</div>