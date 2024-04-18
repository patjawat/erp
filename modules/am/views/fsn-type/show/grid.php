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
<?=$this->render('../../default/menu')?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>


    <div class="container-fluid bg-trasparent my-4 p-3" style="position: relative">
        <div class="row row-cols-1 row-cols-xs-2 row-cols-sm-2 row-cols-lg-5 g-3">
            <?php foreach ($dataProvider->getModels() as $fsnGroup): ?>
            <div class="col hp">
                <div class="card h-100 shadow-sm">
                    <?= Html::a(Html::img($searchModel->showImg(),['class' => 'card-img-top p-3']), ['view', 'id' => $fsnGroup->id]) ?>


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


    <?php
$js = <<< JS


JS;
$this->registerJS($js)

?>           
</div>